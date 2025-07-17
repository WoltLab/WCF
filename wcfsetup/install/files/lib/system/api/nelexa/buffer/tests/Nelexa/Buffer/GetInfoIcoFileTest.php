<?php

namespace Nelexa\Buffer\Nelexa\Buffer;

use Nelexa\Buffer\Buffer;
use Nelexa\Buffer\FileBuffer;

class GetInfoIcoFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @see https://en.wikipedia.org/wiki/ICO_(file_format)
     * @throws \Nelexa\Buffer\BufferException
     */
    public function testIcoInfo()
    {
        $binaryFile = __DIR__ . '/test.ico';

        $buffer = new FileBuffer($binaryFile);
        $buffer->setReadOnly(true);
        $buffer->setOrder(Buffer::LITTLE_ENDIAN);

        // ico header
        $this->assertEquals($buffer->getShort(), 0); // reserved
        $type = $buffer->getShort();
        $this->assertTrue($type === 1 || $type === 2); // type icon
        $count = $buffer->getShort();
        $this->assertTrue($count > 0); // count images

        // image directory
        for ($i = 0; $i < $count; $i++) {
            $width = $buffer->getByte();
            $height = $buffer->getByte();
            $colors = $buffer->getByte();
            $this->assertEquals($buffer->getByte(), 0); // reserved
            $planes = $buffer->getShort();
            $bpp = $buffer->getShort();
            $size = $buffer->getInt();
            $offset = $buffer->getInt();

            $buffer->setPosition($offset + $size);
            $this->assertFalse($buffer->hasRemaining());

            $this->assertEquals($width, 16);
            $this->assertEquals($height, 16);
            $this->assertEquals($colors, 0);
            $this->assertEquals($planes, 1);
            $this->assertEquals($bpp, 32);
        }
        $buffer->close();
    }
}
