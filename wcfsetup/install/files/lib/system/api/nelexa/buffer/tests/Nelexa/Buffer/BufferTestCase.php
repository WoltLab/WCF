<?php

namespace Nelexa\Buffer;

use Nelexa\Buffer\BinaryFormat\BinaryFileItem;
use Nelexa\Buffer\BinaryFormat\BinaryFileTestFormat;

/**
 * Base class for tests all type buffers.
 *
 * Class BufferTestCase
 *
 * @package Nelexa\Buffer
 */
abstract class BufferTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Buffer
     */
    protected $buffer;

    /**
     * Set up
     *
     * @throws \AssertionError
     */
    protected function setUp()
    {
        parent::setUp();
        $this->buffer = $this->createBuffer();
        if (!($this->buffer instanceof Buffer)) {
            throw new \AssertionError('$buffer can\'t implements Buffer');
        }
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->buffer->close();
    }

    /**
     * @return Buffer
     */
    abstract protected function createBuffer();

    /**
     * @throws BufferException
     */
    public function testBaseFunctional()
    {
        $this->buffer->insertString('Telephone');
        $this->buffer->rewind();
        $this->buffer->putString('My I');
        $this->assertEquals($this->buffer->toString(), 'My Iphone');

        $this->buffer->rewind();
        $this->buffer->replaceString('P', 5);
        $this->assertEquals($this->buffer->toString(), 'Phone');

        $this->buffer->rewind();
        $this->buffer->insertString('Tele');
        $this->assertEquals($this->buffer->toString(), 'TelePhone');

        $this->buffer->skip(2);
        $this->buffer->flip();
        $this->assertEquals($this->buffer->position(), 0);
        $this->assertEquals($this->buffer->toString(), 'TelePh');

        $this->buffer->truncate();
        $this->assertEquals($this->buffer->position(), 0);
        $this->assertEquals($this->buffer->size(), 0);
    }

    /**
     * @throws BufferException
     */
    public function testFluent()
    {
        $this->buffer->insertByte(1)
            ->insertBoolean(true)
            ->insertShort(5551)
            ->skip(-2)
            ->insertUTF('Hello, World')
            ->truncate()
            ->insertString(str_rot13('Hello World'))
            ->setPosition(7)
            ->flip();
        $this->assertEquals($this->buffer->size(), 7);
        $this->assertEquals($this->buffer->position(), 0);
        $this->assertEquals($this->buffer->toString(), str_rot13('Hello W'));
    }

    /**
     * @throws BufferException
     */
    public function testInsertFunctional()
    {
        $orders = [Buffer::BIG_ENDIAN, Buffer::LITTLE_ENDIAN];

        foreach ($orders as $order) {
            $this->buffer->truncate();
            $this->buffer->setOrder($order);

            $byte1 = 34;
            $byte2 = 3432424;
            $byte3 = -100;

            $this->buffer->insertByte($byte1);
            $this->buffer->insertByte($byte2);
            $this->buffer->insertByte($byte3);

            $short1 = 31111;
            $short2 = -12444;
            $short3 = 243253233;

            $this->buffer->insertShort($short1);
            $this->buffer->insertShort($short2);
            $this->buffer->insertShort($short3);

            $int1 = Cast::INTEGER_MIN_VALUE;
            $int2 = Cast::INTEGER_MIN_VALUE - 1;
            $int3 = Cast::INTEGER_MAX_VALUE;
            $int4 = Cast::INTEGER_MAX_VALUE + 1;
            $int5 = 24234333;

            $this->buffer->insertInt($int1);
            $this->buffer->insertInt($int2);
            $this->buffer->insertInt($int3);
            $this->buffer->insertInt($int4);
            $this->buffer->insertInt($int5);

            $long1 = Cast::LONG_MIN_VALUE;
            $long2 = Cast::LONG_MAX_VALUE;
            $long3 = Cast::BYTE_MIN_VALUE;
            $long4 = 0;
            $long5 = 243535423222;

            $this->buffer->insertLong($long1);
            $this->buffer->insertLong($long2);
            $this->buffer->insertLong($long3);
            $this->buffer->insertLong($long4);
            $this->buffer->insertLong($long5);

            $bool1 = true;
            $bool2 = false;

            $this->buffer->insertBoolean($bool1);
            $this->buffer->insertBoolean($bool2);

            $arrayBytes = [0x01, 0x02, 0x03, 0x4, Cast::toByte(Cast::INTEGER_MAX_VALUE)];
            $this->buffer->insertArrayBytes($arrayBytes);

            $string = 'String... Строка... 串...
 😀 😬 😁 😂 😃 😄 😅 😆 😇 😉 😊 😊 🙂 🙃 ☺️ 😋 😌 😍 😘 
 🇦🇫 🇦🇽 🇦🇱 🇩🇿 🇦🇸 🇦🇩 🇦🇴 🇦🇮 🇦🇶 🇦🇬 🇦🇷 🇦🇲 🇦🇼 🇦🇺 🇦🇹
  🇦🇿 🇧🇸 🇧🇭 🇧🇩 🇧🇧 🇧🇾 🇧🇪 🇧🇿 🇧🇯 🇧🇲 🇧🇹 🇧🇴 🇧🇶 🇧🇦 🇧🇼
   🇧🇷 🇮🇴 🇻🇬 🇧🇳 🇧🇬 🇧🇫 🇧🇮 🇨🇻 🇰🇭 🇨🇲 🇨🇦 🇮🇨 🇰🇾 🇨🇫 🇹🇩
    🇨🇱 🇨🇳 🇨🇽 🇨🇨 🇨🇴 🇰🇲 🇨🇬 🇨🇩 🇨🇰 🇨🇷 🇭🇷 🇨🇺 🇨🇼 🇨🇾
     🇨🇿 🇩🇰 🇩🇯 🇩🇲 🇩🇴 🇪🇨 🇪🇬 🇸🇻 🇬🇶 🇪🇷 🇪🇪 🇪🇹 🇪🇺 🇫🇰 
     🇫🇴 🇫🇯 🇫🇮 🇫🇷 🇬🇫 🇵🇫 🇹🇫 🇬🇦 🇬🇲 🇬🇪 🇩🇪 🇬🇭 🇬🇮 🇬🇷 
     🇬🇱 🇬🇩 🇬🇵 🇬🇺 🇬🇹 🇬🇬 🇬🇳 🇬🇼 🇬🇾 🇭🇹 🇭🇳 🇭🇰 🇭🇺 🇮🇸 
     🇮🇳 🇮🇩 🇮🇷 🇮🇶 🇮🇪 🇮🇲 🇮🇱 🇮🇹 🇨🇮 🇯🇲 🇯🇵 🇯🇪 🇯🇴 🇰🇿 
     🇰🇪 🇰🇮 🇽🇰 🇰🇼 🇰🇬 🇱🇦 🇱🇻 🇱🇧 🇱🇸 🇱🇷 🇱🇾 🇱🇮 🇱🇹 🇱🇺 
     🇲🇴 🇲🇰 🇲🇬 🇲🇼 🇲🇾 🇲🇻 🇲🇱 🇲🇹 🇲🇭 🇲🇶 🇲🇷 🇲🇺 🇾🇹 🇲🇽 
     🇫🇲 🇲🇩 🇲🇨 🇲🇳 🇲🇪 🇲🇸 🇲🇦 🇲🇿 🇲🇲 🇳🇦 🇳🇷 🇳🇵 🇳🇱 🇳🇨 
     🇳🇿 🇳🇮 🇳🇪 🇳🇬 🇳🇺 🇳🇫 🇲🇵 🇰🇵 🇳🇴 🇴🇲 🇵🇰 🇵🇼 🇵🇸 🇵🇦 
     🇵🇬 🇵🇾 🇵🇪 🇵🇭 🇵🇳 🇵🇱 🇵🇹 🇵🇷 🇶🇦 🇷🇪 🇷🇴 🇷🇺 🇷🇼 🇧🇱 
     🇸🇭 🇰🇳 🇱🇨 🇵🇲 🇻🇨 🇼🇸 🇸🇲 🇸🇹 🇸🇦 🇸🇳 🇷🇸 🇸🇨 🇸🇱 🇸🇬 
     🇸🇽 🇸🇰 🇸🇮 🇸🇧 🇸🇴 🇿🇦 🇬🇸 🇰🇷 🇸🇸 🇪🇸 🇱🇰 🇸🇩 🇸🇷 🇸🇿 
     🇸🇪 🇨🇭 🇸🇾 🇹🇼 🇹🇯 🇹🇿 🇹🇭 🇹🇱 🇹🇬 🇹🇰 🇹🇴 🇹🇹 🇹🇳 🇹🇷 
     🇹🇲 🇹🇨 🇹🇻 🇺🇬 🇺🇦 🇦🇪 🇬🇧 🇺🇸 🇻🇮 🇺🇾 🇺🇿 🇻🇺 🇻🇦 🇻🇪 
     🇻🇳 🇼🇫 🇪🇭 🇾🇪 🇿🇲 🇿🇼 ';
            $lengthString = strlen($string);

            $this->buffer->insertString($string);
            $this->buffer->insertUTF($string);
            $this->buffer->insertUTF16($string);

            $otherBuffer = new MemoryResourceBuffer(str_rot13($string));
            $this->buffer->insert($otherBuffer);

            $this->buffer->rewind();

            $this->assertEquals($this->buffer->position(), 0);
            $this->assertEquals($this->buffer->getByte(), Cast::toByte($byte1));
            $this->assertEquals($this->buffer->position(), 1);
            $this->assertEquals($this->buffer->getByte(), Cast::toByte($byte2));
            $this->assertEquals($this->buffer->position(), 2);
            $this->assertEquals($this->buffer->getByte(), Cast::toByte($byte3));
            $this->assertEquals($this->buffer->position(), 3);

            $this->buffer->setPosition(0);

            $this->assertEquals($this->buffer->position(), 0);
            $this->assertEquals($this->buffer->getUnsignedByte(), Cast::toUnsignedByte($byte1));
            $this->assertEquals($this->buffer->position(), 1);
            $this->assertEquals($this->buffer->getUnsignedByte(), Cast::toUnsignedByte($byte2));
            $this->assertEquals($this->buffer->position(), 2);
            $this->assertEquals($this->buffer->getUnsignedByte(), Cast::toUnsignedByte($byte3));
            $this->assertEquals($this->buffer->position(), 3);

            $this->assertEquals($this->buffer->getShort(), Cast::toShort($short1));
            $this->assertEquals($this->buffer->position(), 5);
            $this->assertEquals($this->buffer->getShort(), Cast::toShort($short2));
            $this->assertEquals($this->buffer->position(), 7);
            $this->assertEquals($this->buffer->getShort(), Cast::toShort($short3));
            $this->assertEquals($this->buffer->position(), 9);

            $this->buffer->skip(-6);

            $this->assertEquals($this->buffer->position(), 3);
            $this->assertEquals($this->buffer->getUnsignedShort(), Cast::toUnsignedShort($short1));
            $this->assertEquals($this->buffer->position(), 5);
            $this->assertEquals($this->buffer->getUnsignedShort(), Cast::toUnsignedShort($short2));
            $this->assertEquals($this->buffer->position(), 7);
            $this->assertEquals($this->buffer->getUnsignedShort(), Cast::toUnsignedShort($short3));
            $this->assertEquals($this->buffer->position(), 9);

            $this->assertEquals($this->buffer->getInt(), Cast::toInt($int1));
            $this->assertEquals($this->buffer->position(), 13);
            $this->assertEquals($this->buffer->getInt(), Cast::toInt($int2));
            $this->assertEquals($this->buffer->position(), 17);
            $this->assertEquals($this->buffer->getInt(), Cast::toInt($int3));
            $this->assertEquals($this->buffer->position(), 21);
            $this->assertEquals($this->buffer->getInt(), Cast::toInt($int4));
            $this->assertEquals($this->buffer->position(), 25);
            $this->assertEquals($this->buffer->getInt(), Cast::toInt($int5));
            $this->assertEquals($this->buffer->position(), 29);

            $this->buffer->skip(-20);

            $this->assertEquals($this->buffer->getUnsignedInt(), Cast::toUnsignedInt($int1));
            $this->assertEquals($this->buffer->position(), 13);
            $this->assertEquals($this->buffer->getUnsignedInt(), Cast::toUnsignedInt($int2));
            $this->assertEquals($this->buffer->position(), 17);
            $this->assertEquals($this->buffer->getUnsignedInt(), Cast::toUnsignedInt($int3));
            $this->assertEquals($this->buffer->position(), 21);
            $this->assertEquals($this->buffer->getUnsignedInt(), Cast::toUnsignedInt($int4));
            $this->assertEquals($this->buffer->position(), 25);
            $this->assertEquals($this->buffer->getUnsignedInt(), Cast::toUnsignedInt($int5));
            $this->assertEquals($this->buffer->position(), 29);

            $this->assertEquals($this->buffer->getLong(), Cast::toLong($long1));
            $this->assertEquals($this->buffer->position(), 37);
            $this->assertEquals($this->buffer->getLong(), Cast::toLong($long2));
            $this->assertEquals($this->buffer->position(), 45);
            $this->assertEquals($this->buffer->getLong(), Cast::toLong($long3));
            $this->assertEquals($this->buffer->position(), 53);
            $this->assertEquals($this->buffer->getLong(), Cast::toLong($long4));
            $this->assertEquals($this->buffer->position(), 61);
            $this->assertEquals($this->buffer->getLong(), Cast::toLong($long5));
            $this->assertEquals($this->buffer->position(), 69);

            $this->assertEquals($this->buffer->getBoolean(), $bool1);
            $this->assertEquals($this->buffer->position(), 70);
            $this->assertEquals($this->buffer->getBoolean(), $bool2);
            $this->assertEquals($this->buffer->position(), 71);

            $this->assertEquals($this->buffer->getArrayBytes(5), $arrayBytes);
            $this->assertEquals($this->buffer->position(), 76);

            $this->assertEquals($this->buffer->getString($lengthString), $string);
            $this->assertEquals($this->buffer->position(), 76 + $lengthString);

            $this->assertEquals($this->buffer->getUTF(), $string);
            $this->assertEquals($this->buffer->position(), 78 + $lengthString * 2);

            $this->assertEquals($this->buffer->getUTF16($lengthString), $string);
            $this->assertEquals($this->buffer->position(), 78 + $lengthString * 4);

            $this->assertEquals($this->buffer->getString($lengthString), $otherBuffer->toString());
            $this->assertEquals($this->buffer->position(), 78 + $lengthString * 5);
        }
    }

    /**
     * @throws BufferException
     */
    public function testPutFunctional()
    {
        $this->buffer->setOrder(Buffer::BIG_ENDIAN);
        $this->buffer->insertLong(12345);
        $this->buffer->setPosition(4);
        $this->buffer->putInt(98765);
        $this->buffer->rewind();
        $this->assertEquals($this->buffer->getLong(), 98765);

        $this->buffer->rewind();
        $this->buffer->setOrder(Buffer::LITTLE_ENDIAN);
        $this->buffer->putLong(12345);
        $this->buffer->rewind();
        $this->assertEquals($this->buffer->getLong(), 12345);
        $this->buffer->setPosition(0);
        $this->buffer->putInt(98765);
        $this->buffer->rewind();
        $this->assertEquals($this->buffer->getLong(), 98765);
    }

    /**
     * @throws BufferException
     */
    public function testReplaceFunctional()
    {
        $this->buffer->insertString('123456789');
        $this->buffer->setPosition(3);
        $this->buffer->replaceBoolean(true, 3);
        $this->assertEquals('123789', $this->buffer->toString());
        $this->buffer->skip(-1);
        $this->buffer->replaceString('', 1);
        $this->assertEquals('123789', $this->buffer->toString());
        $this->buffer->replaceString('456', 0);
        $this->assertEquals('123456789', $this->buffer->toString());
    }

    /**
     * @throws BufferException
     */
    public function testRemoveFunctional()
    {
        $this->buffer->insertString('123456789');
        $this->buffer->setPosition(3);
        $this->buffer->remove(3);
        $this->assertEquals('123789', $this->buffer->toString());
    }

    /**
     * @expectedException \Nelexa\Buffer\BufferException
     * @expectedExceptionMessage put length > remaining
     */
    public function testPutException()
    {
        $this->assertEquals($this->buffer->size(), 0);
        $this->buffer->putString('Test');
    }

    /**
     * @expectedException \Nelexa\Buffer\BufferException
     * @expectedExceptionMessage put length > remaining
     */
    public function testPutException2()
    {
        $this->buffer
            ->insertString('Test')
            ->rewind()
            ->putString('My Test');
    }

    /**
     * @expectedException \Nelexa\Buffer\BufferException
     * @expectedExceptionMessage replace length > remaining
     */
    public function testReplaceException()
    {
        $this->assertEquals($this->buffer->size(), 0);
        $this->buffer->replaceString('Test', 5);
    }

    /**
     * @expectedException \Nelexa\Buffer\BufferException
     * @expectedExceptionMessage remove length > remaining
     */
    public function testRemoveException()
    {
        $this->assertEquals($this->buffer->size(), 0);
        $this->buffer->remove(1);
    }

    /**
     * @expectedException \Nelexa\Buffer\BufferException
     * @expectedExceptionMessage Read Only
     */
    public function testReadOnly()
    {
        $this->assertEquals($this->buffer->isReadOnly(), false);
        $this->buffer->setReadOnly(true);
        $this->assertEquals($this->buffer->isReadOnly(), true);
        $this->buffer->insertBoolean(true);
    }

    /**
     * @throws BufferException
     */
    public function testOrder()
    {
        $this->assertEquals($this->buffer->order(), Buffer::BIG_ENDIAN);

        $this->buffer->insertByte(50)
            ->insertShort(5000)
            ->insertInt(50000000)
            ->insertLong(5000000000);

        $this->buffer->setOrder(Buffer::LITTLE_ENDIAN)->rewind();
        $this->assertEquals($this->buffer->order(), Buffer::LITTLE_ENDIAN);

        $this->assertEquals($this->buffer->getByte(), 50);
        $this->assertEquals($this->buffer->getShort(), -30701);
        $this->assertEquals($this->buffer->getInt(), -2131691006);
        $this->assertEquals($this->buffer->getLong(), 68122622327521280);

        $this->buffer->setOrder(Buffer::BIG_ENDIAN)->rewind();
        $this->assertEquals($this->buffer->order(), Buffer::BIG_ENDIAN);

        $this->assertEquals($this->buffer->getByte(), 50);
        $this->assertEquals($this->buffer->getShort(), 5000);
        $this->assertEquals($this->buffer->getInt(), 50000000);
        $this->assertEquals($this->buffer->getLong(), 5000000000);
    }

    /**
     * @throws BufferException
     */
    public function testPositions()
    {
        $this->buffer->insertString('Test value');
        $this->assertEquals($this->buffer->size(), 10);
        $this->assertEquals($this->buffer->position(), 10);

        $this->buffer->setPosition(3);
        $this->assertEquals($this->buffer->position(), 3);

        $this->buffer->skip(2);
        $this->assertEquals($this->buffer->position(), 5);

        $this->buffer->skip(-4);
        $this->assertEquals($this->buffer->position(), 1);

        $this->assertEquals($this->buffer->remaining(), 9);
        $this->assertEquals($this->buffer->hasRemaining(), true);

        $this->buffer->setPosition($this->buffer->size());
        $this->assertEquals($this->buffer->position(), 10);
        $this->assertEquals($this->buffer->remaining(), 0);
        $this->assertEquals($this->buffer->hasRemaining(), false);

        $this->buffer->rewind();
        $this->assertEquals($this->buffer->position(), 0);

        $this->buffer->insertString(str_repeat('*', 100));
        $this->assertEquals($this->buffer->position(), 100);
        $this->assertEquals($this->buffer->size(), 110);

        $this->buffer->setPosition(0);
        $this->assertEquals($this->buffer->position(), 0);

        $this->buffer->skipByte();
        $this->assertEquals($this->buffer->position(), 1);

        $this->buffer->skipShort();
        $this->assertEquals($this->buffer->position(), 3);

        $this->buffer->skipInt();
        $this->assertEquals($this->buffer->position(), 7);

        $this->buffer->skipLong();
        $this->assertEquals($this->buffer->position(), 15);

        $this->buffer->toString();
        $this->assertEquals($this->buffer->position(), 15);

        $this->buffer->flip();
        $this->assertEquals($this->buffer->position(), 0);
        $this->assertEquals($this->buffer->size(), 15);

        $this->buffer->setPosition(5)->truncate();
        $this->assertEquals($this->buffer->position(), 0);
        $this->assertEquals($this->buffer->size(), 0);

        $this->buffer->insertBoolean(true);
        $this->assertEquals($this->buffer->position(), 1);
        $this->assertEquals($this->buffer->size(), 1);
        $this->buffer->truncate();

        $this->buffer->insertByte(0);
        $this->assertEquals($this->buffer->position(), 1);
        $this->assertEquals($this->buffer->size(), 1);
        $this->buffer->truncate();

        $this->buffer->insertShort(0);
        $this->assertEquals($this->buffer->position(), 2);
        $this->assertEquals($this->buffer->size(), 2);
        $this->buffer->truncate();

        $this->buffer->insertInt(0);
        $this->assertEquals($this->buffer->position(), 4);
        $this->assertEquals($this->buffer->size(), 4);
        $this->buffer->truncate();

        $this->buffer->insertLong(0);
        $this->assertEquals($this->buffer->position(), 8);
        $this->assertEquals($this->buffer->size(), 8);
        $this->buffer->truncate();

        $this->buffer->insertArrayBytes([5, 5, 6, 5, 7, 8, 9]);
        $this->assertEquals($this->buffer->position(), 7);
        $this->assertEquals($this->buffer->size(), 7);
        $this->buffer->truncate();

        $this->buffer->insertUTF('Test');
        $this->assertEquals($this->buffer->position(), 6);
        $this->assertEquals($this->buffer->size(), 6);
        $this->buffer->truncate();

        $this->buffer->insertUTF16('Test');
        $this->assertEquals($this->buffer->position(), 8);
        $this->assertEquals($this->buffer->size(), 8);
        $this->buffer->truncate();
    }

    /**
     * @throws BufferException
     */
    public function testBinaryFile()
    {
        $name = 'General Name';
        $items = [
            BinaryFileItem::create(time() * 1000, ['Category 1', 'Category 2']),
            BinaryFileItem::create((time() - 3600) * 1000, ['Category 2', 'Category 3']),
            BinaryFileItem::create((time() - 52222) * 1000, ['Category 4', 'Category 2', 'Category 7']),
        ];

        $binaryFileActual = BinaryFileTestFormat::create($name, $items);
        $binaryFileActual->writeObject($this->buffer);
        $output = $this->buffer->toString();

        $buffer = new StringBuffer($output);
        $binaryFileExpected = new BinaryFileTestFormat();
        $binaryFileExpected->readObject($buffer);

        $this->assertEquals($binaryFileExpected, $binaryFileActual);
    }

    /**
     * @throws BufferException
     * @requires PHP 7.0.15
     */
    public function testDouble()
    {
        $double = 12.6664287277627762; // 64 bit

        $buffer = $this->createBuffer();

        $buffer->insertDouble($double);
        $this->assertEquals($buffer->size(), 8);

        $buffer->rewind();
        $this->assertEquals($buffer->getDouble(), $double);
        $this->assertEquals($buffer->position(), 8);

        $buffer->rewind();
        $buffer->skipDouble();
        $this->assertEquals($buffer->position(), 8);

        $buffer->rewind();
        $this->assertEquals($buffer->getArrayBytes(8), [64, 41, 85, 54, 37, 109, -74, 71]);
    }

    /**
     * @throws BufferException
     * @requires PHP 7.0.15
     */
    public function testFloat()
    {
        $float = 12.666428565979; // 32 bit

        $buffer = $this->createBuffer();

        $buffer->insertFloat($float);
        $this->assertEquals($buffer->size(), 4);

        $buffer->rewind();
        $this->assertEquals($buffer->getFloat(), $float);
        $this->assertEquals($buffer->position(), 4);

        $buffer->rewind();
        $buffer->skipFloat();
        $this->assertEquals($buffer->position(), 4);

        $buffer->rewind();
        $this->assertEquals($buffer->getArrayBytes(4), [65, 74, -87, -79]);
    }
}
