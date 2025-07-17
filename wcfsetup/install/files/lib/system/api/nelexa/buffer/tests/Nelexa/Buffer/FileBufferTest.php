<?php

namespace Nelexa\Buffer;

class FileBufferTest extends BufferTestCase
{

    /**
     * @var string
     */
    private $outputFilename;

    /**
     * Before test
     */
    protected function setUp()
    {
        $this->outputFilename = tempnam(sys_get_temp_dir(), 'temp');
        parent::setUp();
    }

    /**
     * After test
     */
    protected function tearDown()
    {
        parent::tearDown();

        if ($this->outputFilename !== null && file_exists($this->outputFilename)) {
            unlink($this->outputFilename);
        }
    }

    /**
     * @return Buffer
     * @throws BufferException
     */
    protected function createBuffer()
    {
        return new FileBuffer($this->outputFilename);
    }
}
