<?php

namespace Behat\Tests\Transliterator;

use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @dataProvider provideDataFiles
     */
    public function testDataFileIntegrity($file)
    {
        $UTF8_TO_ASCII = array();

        require __DIR__.'/../src/Behat/Transliterator/data/'.$file;

        $this->assertCount(1, $UTF8_TO_ASCII, 'Each data file should register a single key in $UTF8_TO_ASCII.');

        $data = current($UTF8_TO_ASCII);

        $this->assertIsArray($data, 'The value in $UTF8_TO_ASCII should be an array.');

        $this->assertEquals(256, count($data), 'The value in $UTF8_TO_ASCII should have 256 elements.');
    }

    public static function provideDataFiles()
    {
        $files = array();

        $iterator = new \FilesystemIterator(__DIR__.'/../src/Behat/Transliterator/data');

        foreach ($iterator as $file) {
            $files[] = array($file->getFilename());
        }

        return $files;
    }
}
