<?php

namespace Nelexa\Buffer;

class StringBufferTest extends BufferTestCase
{

    /**
     * @return Buffer
     * @throws BufferException
     */
    protected function createBuffer()
    {
        return new StringBuffer();
    }
}
