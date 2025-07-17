<?php

namespace Nelexa\Buffer;

class MemoryResourceBufferTest extends BufferTestCase
{

    /**
     * @return Buffer
     * @throws BufferException
     */
    protected function createBuffer()
    {
        return new MemoryResourceBuffer();
    }
}
