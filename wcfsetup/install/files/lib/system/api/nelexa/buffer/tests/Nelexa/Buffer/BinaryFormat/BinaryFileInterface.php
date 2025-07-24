<?php

namespace Nelexa\Buffer\BinaryFormat;

use Nelexa\Buffer\Buffer;

interface BinaryFileInterface
{
    public function readObject(Buffer $buffer);

    public function writeObject(Buffer $buffer);
}
