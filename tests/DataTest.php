<?php

namespace Behat\Tests\Transliterator;

class DataTest extends \PHPUnit_Framework_TestCase
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

        $this->assertInternalType('array', $data, 'The value in $UTF8_TO_ASCII should be an array.');
        // Accept 255 elements because of inconsistencies in the data of the original Perl library
        $this->assertEquals(256, count($data), 'The value in $UTF8_TO_ASCII should have 255 or 256 elements.', 1);
    }

    public function provideDataFiles()
    {
        $files = array();

        $iterator = new \FilesystemIterator(__DIR__.'/../src/Behat/Transliterator/data');

        foreach ($iterator as $file) {
            $files[] = array($file->getFilename());
        }

        return $files;
    }
}
