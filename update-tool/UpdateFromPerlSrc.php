<?php

// http://cpansearch.perl.org/src/SBURKE/Text-Unidecode-1.23/lib/Text/Unidecode/

use \Yaoi\String\StringValue;

class UpdateFromPerlSrc extends Yaoi\Command
{
    public $libVer = '1.27';

    static function setUpDefinition(\Yaoi\Command\Definition $definition, $options)
    {
        $options->libVer = \Yaoi\Command\Option::create()->setIsUnnamed()
            ->setDescription('Version of Perl library');
        $definition->name = 'update';
        $definition->description = 'Tool for converting char tables for Behat/Transliterator from Perl to PHP';
    }

    public function performAction()
    {
        $rollingCurl = new \RollingCurl\RollingCurl();

        foreach ($this->getXxxPmUrlList() as $url) {
            $rollingCurl->get($url);
        }

        $rollingCurl->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) {
            $this->response->addContent($request->getUrl());
            $content = $request->getResponseText();
            $this->readXxxPm($content);
        })
            ->execute();
    }


    private $tokenizer;
    public function __construct()
    {
        $escape = array(
            '\\}' => '}',
            '\\\\' => '\\',
            '\\{' => '{',
            '\\@' => '@',
            '\\$' => '$',
            );

        $this->tokenizer = new Yaoi\String\Lexer\Parser();
        $this->tokenizer->addLineStopper('#');
        $this->tokenizer->addQuote('qq{','}', $escape);
        $this->tokenizer->addQuote('q{', '}', $escape);
        $this->tokenizer->addQuote('"', '"');
        $this->tokenizer->addQuote("'", "'");
        $this->tokenizer->addBracket('[', ']');
        $this->tokenizer->addDelimiter(';');
    }

    private function readXxxPm($content) {
        $tokens = $this->tokenizer->tokenize($content);
        $renderer = new \Yaoi\String\Lexer\Renderer();
        $renderer->
            setBindKey('-~z', 'z~-')
            ->strip('#')
            ->keepBoundaries('[');

        $expression = $renderer->getExpression($tokens);
        $statement = $expression->getStatement();
        /** @var \Yaoi\String\Lexer\Parsed[] $binds */
        $binds = $expression->getBinds();


        $parser = new \Yaoi\String\Parser($statement);
        $block = (string)$parser->inner('$Text::Unidecode::Char[', ']');
        if (!$block) {
            throw new \Exception('Block not found');
        }
        $block = $renderer->getExpression($binds[$block])->getStatement();

        $php = <<<PHP
<?php
\$UTF8_TO_ASCII[$block] = array(

PHP;

        $itemsBind = (string)$parser->inner('[', ']');
        if (!$itemsBind) {
            //echo $statement, PHP_EOL;
            return;
        }
        $items = $binds[$itemsBind];
        $itemsExpression = $renderer->getExpression($items);
        $itemsStatement = $itemsExpression->getStatement();
        $itemsBinds = $itemsExpression->getBinds();

        $itemsStatement = explode(',', $itemsStatement);
        $index = 0;
        foreach ($itemsStatement as $item) {
            $item = trim($item);
            if (!$item) {
                break;
            }

            if ($index >= 16) {
                $php = trim($php);
                $php .= PHP_EOL;
                $index = 0;
            }
            ++$index;

            if (isset($itemsBinds[$item])) {
                /** @var \Yaoi\String\Lexer\Token $token */
                $token = $itemsBinds[$item];
                $value = new StringValue($token->unEscapedContent);
                if ($value->starts('\x')) {
                    $php .= '"' . $value . '", ';
                }
                else {
                    $php .= "'" . str_replace(array('\\', '\''), array('\\\\', '\\\''), $value) . "', ";
                }
            }
            else {
                $php .= "'" . str_replace(array('\\', '\''), array('\\\\', '\\\''), $item) . "', ";
            }
        }

        $php = trim($php) . PHP_EOL . ');' . PHP_EOL;

        file_put_contents(__DIR__ . '/../src/Behat/Transliterator/data/' . substr($block, 1) . '.php', $php);
    }


    private function getXxxPmUrlList() {
        $client = new \Yaoi\Http\Client();
        $list = array();
        $page = $client->fetch('http://cpansearch.perl.org/src/SBURKE/Text-Unidecode-'.$this->libVer.'/lib/Text/Unidecode/');
        foreach (\Yaoi\String\Parser::create($page)->innerAll('.pm">', '</a>') as $xXXpm) {
            $list []= 'http://cpansearch.perl.org/src/SBURKE/Text-Unidecode-' . $this->libVer . '/lib/Text/Unidecode/'
                . $xXXpm;
        }
        return $list;
    }

}