<?php

namespace Behat\Transliterator;

use RollingCurl\Request;
use RollingCurl\RollingCurl;
use Yaoi\Command;
use Yaoi\Command\Option;
use Yaoi\Http\Client;
use Yaoi\String\Lexer\Parsed;
use Yaoi\String\Lexer\Parser;
use Yaoi\String\Lexer\Renderer;
use Yaoi\String\Lexer\Token;
use Yaoi\String\StringValue;
use Yaoi\String\Parser as StringParser;

/**
 * Tool for converting char tables for Behat/Transliterator from Perl to PHP
 * @internal
 */
class SyncTool extends Command
{
    const LIB_VERSION = '1.27';

    private $tokenizer;
    private $renderer;
    private $phpTable;
    private $itemIndex;
    private $nonQuestionBoxFound;
    private $block;

    public function __construct()
    {
        $escape = array(
            '\\}' => '}',
            '\\\\' => '\\',
            '\\{' => '{',
            '\\@' => '@',
            '\\$' => '$',
        );

        $this->tokenizer = new Parser();
        $this->tokenizer->addLineStopper('#');
        $this->tokenizer->addQuote('qq{', '}', $escape);
        $this->tokenizer->addQuote('q{', '}', $escape);
        $this->tokenizer->addQuote('"', '"');
        $this->tokenizer->addQuote("'", "'");
        $this->tokenizer->addBracket('[', ']');
        $this->tokenizer->addDelimiter(';');

        $this->renderer = new Renderer();
        $this->renderer
            ->setBindKey('-~z', 'z~-')
            ->strip('#')
            ->keepBoundaries('[');
    }

    public static function setUpDefinition(\Yaoi\Command\Definition $definition, $options)
    {
        $definition->name = 'update-data';
        $definition->description = 'Tool for converting char tables for Behat/Transliterator from Perl to PHP';
    }

    public function performAction()
    {
        $rollingCurl = new RollingCurl();

        foreach ($this->getPerlTablesUrlList() as $url) {
            $rollingCurl->get($url);
        }

        $rollingCurl->setCallback(function (Request $request, RollingCurl $rollingCurl) {
            $this->response->addContent($request->getUrl());
            $content = $request->getResponseText();
            $this->parsePerlTable($content);
        })
            ->execute();
    }

    private function removePhpCharTable($phpFilePath, $reason)
    {
        $this->response->addContent($reason);
        if (file_exists($phpFilePath)) {
            if (unlink($phpFilePath)) {
                $this->response->success('Deleted');
            } else {
                $this->response->error('Failed to delete');
            }
        } else {
            $this->response->success('No PHP file, skipped');
        }
    }

    private function pushItem($item)
    {
        if ($this->itemIndex >= 16) {
            $this->phpTable = trim($this->phpTable);
            $this->phpTable .= "\n";
            $this->itemIndex = 0;
        }
        ++$this->itemIndex;

        $item = new StringValue($item);
        if ($item->starts('\x') || $item->starts('\n')) {
            $this->phpTable .= '"' . $item . '", ';
            $this->nonQuestionBoxFound = true;
        } else {
            // TODO check if this hack should be removed for chinese letters
            if ($item->value === '[?] ') {
                $item->value = '[?]';
            }
            //

            if ($item->value !== '[?]') {
                $this->nonQuestionBoxFound = true;
            }

            $this->phpTable .= "'" . str_replace(array('\\', '\''), array('\\\\', '\\\''), $item) . "', ";
        }
    }

    private function tokenizePerlTable($content)
    {
        $tokens = $this->tokenizer->tokenize($content);

        $expression = $this->renderer->getExpression($tokens);
        $statement = $expression->getStatement();
        /** @var Parsed[] $binds */
        $binds = $expression->getBinds();

        $parser = new StringParser($statement);
        $block = (string)$parser->inner('$Text::Unidecode::Char[', ']');
        if (!$block) {
            throw new \Exception('Block not found');
        }
        $this->block = $this->renderer->getExpression($binds[$block])->getStatement();

        $itemsBind = (string)$parser->inner('[', ']');

        if (!$itemsBind) {
            $items = array();
        }
        else {
            $items = $binds[$itemsBind];
        }

        return $items;
    }

    private function parsePerlTable($content)
    {
        $items = $this->tokenizePerlTable($content);

        $phpFilePath = __DIR__ . '/data/' . substr($this->block, 1) . '.php';
        if (!$items) {
            $this->removePhpCharTable($phpFilePath, 'Empty char table for block ' . $this->block);
            return;
        }

        $this->phpTable = <<<PHP
<?php
\$UTF8_TO_ASCII[$this->block] = array(

PHP;

        $itemsExpression = $this->renderer->getExpression($items);
        $itemsStatement = $itemsExpression->getStatement();
        $itemsBinds = $itemsExpression->getBinds();

        $itemsStatement = explode(',', $itemsStatement);
        $this->itemIndex = 0;
        $this->nonQuestionBoxFound = false;
        foreach ($itemsStatement as $item) {
            $item = trim($item);
            if (!$item) {
                break;
            }

            if (isset($itemsBinds[$item])) {
                /** @var Token $token */
                $token = $itemsBinds[$item];
                $item = $token->unEscapedContent;
            }

            $this->pushItem($item);
        }

        if ($this->nonQuestionBoxFound) {
            $this->phpTable = trim($this->phpTable) . "\n" . ');' . "\n";
            if (file_put_contents($phpFilePath, $this->phpTable)) {
                $this->response->success('Block ' . $this->block . ' converted to ' . $phpFilePath);
            } else {
                $this->response->error('Failed to save ' . $phpFilePath);
            }
        } else {
            $this->removePhpCharTable($phpFilePath, 'Block ' . $this->block . ' contains only [?]');
        }

    }

    private function getPerlTablesUrlList()
    {
        $client = new Client();
        $list = array();
        $page = $client->fetch('http://cpansearch.perl.org/src/SBURKE/Text-Unidecode-' . self::LIB_VERSION . '/lib/Text/Unidecode/');
        foreach (StringParser::create($page)->innerAll('.pm">', '</a>') as $xXXpm) {
            $list[] = 'http://cpansearch.perl.org/src/SBURKE/Text-Unidecode-' . self::LIB_VERSION . '/lib/Text/Unidecode/'
                . $xXXpm;
        }
        return $list;
    }
}

