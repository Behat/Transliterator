<?php

namespace Behat\Tests\Transliterator;

use Behat\Transliterator\Transliterator;

class TransliteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideUtf8ConversionCases
     */
    public function testUtf8Conversion($input, $expected)
    {
        $this->assertSame($expected, Transliterator::utf8ToAscii($input));
    }

    public function provideUtf8ConversionCases()
    {
        return array(
            array('', ''),
            array('BonJour', 'BonJour'),
            array('Déjà', 'Deja'),
            array('trąnslįteration tėst ųsąge ūž', 'transliteration test usage uz'),
            array('това е тестово заглавие', 'tova e testovo zaglavie'),
            array('это тестовый заголовок', 'eto testovyi zagolovok'),
            array('führen Aktivitäten Haglöfs', 'fuhren Aktivitaten Haglofs'),
            array('тест', 'test'),
            array('Розничная торговля', 'Roznichnaya torgovlya'),
            array('Брест', 'Brest'),
            array('резюме', 'rezyume'),
            array('Промышленное производство', 'Promyshlennoe proizvodstvo'),
            array('Оптовая торговля', 'Optovaya torgovlya'),
            array('Я', 'Ya')
        );
    }

    /**
     * @dataProvider provideTransliterationCases
     */
    public function testTransliteration($input, $expected)
    {
        $this->assertSame($expected, Transliterator::transliterate($input));
    }

    public function provideTransliterationCases()
    {
        return array(
            array('', ''),
            array('BonJour', 'bonjour'),
            array('BonJour & au revoir', 'bonjour-au-revoir'),
            array('Déjà', 'deja'),
            array('trąnslįteration tėst ųsąge ūž', 'transliteration-test-usage-uz'),
            array('това е тестово заглавие', 'tova-e-testovo-zaglavie'),
            array('это тестовый заголовок', 'eto-testovyi-zagolovok'),
            array('führen Aktivitäten Haglöfs', 'fuhren-aktivitaten-haglofs'),
            array('тест', 'test'),
            array('Розничная торговля', 'roznichnaya-torgovlya'),
            array('Брест', 'brest'),
            array('резюме', 'rezyume'),
            array('Промышленное производство', 'promyshlennoe-proizvodstvo'),
            array('Оптовая торговля', 'optovaya-torgovlya')
        );
    }

    /**
     * @dataProvider provideUnaccentCases
     */
    public function testUnaccent($input, $expected)
    {
        $this->assertSame($expected, Transliterator::unaccent($input));
    }

    public function provideUnaccentCases()
    {
        return array(
            array('', ''),
            array('BonJour', 'BonJour'),
            array('Déjà', 'Deja'),
            array('това е тестово заглавие', 'това е тестово заглавие'),
            array('тест', 'тест')
        );
    }

    /**
     * @dataProvider provideUrlizationCases
     */
    public function testUrlization($input, $expected)
    {
        $this->assertSame($expected, Transliterator::urlize($input));
    }

    public function provideUrlizationCases()
    {
        return array(
            array('', ''),
            array('BonJour', 'bonjour'),
            array('BonJour & au revoir', 'bonjour-au-revoir'),
            array('Déjà', 'deja'),
            array('това е тестово заглавие', ''),
        );
    }
}
