<?php

namespace Nelexa\Buffer;

class TempBufferTest extends BufferTestCase
{

    /**
     * @return Buffer
     * @throws BufferException
     */
    protected function createBuffer()
    {
        return new TempBuffer();
    }
}
