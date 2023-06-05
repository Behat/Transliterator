<?php

namespace Behat\Tests\Transliterator;

use Behat\Transliterator\Transliterator;
use PHPUnit\Framework\TestCase;

class TransliteratorTest extends TestCase
{
    /**
     * @dataProvider provideUtf8ConversionCases
     */
    public function testUtf8Conversion($input, $expected)
    {
        $this->assertSame($expected, Transliterator::utf8ToAscii($input));
    }

    public static function provideUtf8ConversionCases()
    {
        return array(
            array('', ''),
            array('BonJour', 'BonJour'),
            array('Déjà', 'Deja'),
            array('trąnslįteration tėst ųsąge ūž', 'transliteration test usage uz'),
            array('това е тестово заглавие', 'tova e testovo zaglavie'),
            array('це є тестовий заголовок з ґ, є, ї, і', 'tse ie testovii zagolovok z g\', ie, yi, i'),
            array('это тестовый заголовок', 'eto testovyi zagolovok'),
            array('führen Aktivitäten Haglöfs', 'fuhren Aktivitaten Haglofs'),
        );
    }

    /**
     * @dataProvider provideTransliterationCases
     */
    public function testTransliteration($input, $expected)
    {
        $this->assertSame($expected, Transliterator::transliterate($input));
    }

    public static function provideTransliterationCases()
    {
        return array(
            array('', ''),
            array('BonJour', 'bonjour'),
            array('BonJour & au revoir', 'bonjour-au-revoir'),
            array('Déjà', 'deja'),
            array('trąnslįteration tėst ųsąge ūž', 'transliteration-test-usage-uz'),
            array('това е тестово заглавие', 'tova-e-testovo-zaglavie'),
            array('це є тестовий заголовок з ґ, є, ї, і', 'tse-ie-testovii-zagolovok-z-g-ie-yi-i'),
            array('это тестовый заголовок', 'eto-testovyi-zagolovok'),
            array('führen Aktivitäten Haglöfs', 'fuhren-aktivitaten-haglofs'),
            array("that it's 'eleven' 'o'clock'", "that-its-eleven-oclock"),
        );
    }

    /**
     * @dataProvider provideUnaccentCases
     */
    public function testUnaccent($input, $expected)
    {
        $this->assertSame($expected, Transliterator::unaccent($input));
    }

    public static function provideUnaccentCases()
    {
        return array(
            array('', ''),
            array('BonJour', 'BonJour'),
            array('Déjà', 'Deja'),
            array('това е тестово заглавие', 'това е тестово заглавие'),
            array('це є тестовий заголовок з ґ, є, ї, і', 'це є тестовий заголовок з ґ, є, ї, і'),
        );
    }

    /**
     * @dataProvider provideUrlizationCases
     */
    public function testUrlization($input, $expected)
    {
        $this->assertSame($expected, Transliterator::urlize($input));
    }

    public static function provideUrlizationCases()
    {
        return array(
            array('', ''),
            array('BonJour', 'bonjour'),
            array('BonJour & au revoir', 'bonjour-au-revoir'),
            array('Déjà', 'deja'),
            array('това е тестово заглавие', ''),
            array('це є тестовий заголовок з ґ, є, ї, і', ''),
        );
    }
}
