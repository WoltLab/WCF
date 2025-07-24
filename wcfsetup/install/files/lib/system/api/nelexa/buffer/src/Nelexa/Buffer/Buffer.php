<?php

namespace Nelexa\Buffer;

/**
 * Read And Write Binary Data
 *
 * This is class defines methods for reading and writing values of all primitive types. Primitive values are translated
 * to (or from) sequences of bytes according to the buffer's current byte order, which may be retrieved and modified
 * via the order methods. The initial order of a byte buffer is always Buffer::BIG_ENDIAN.
 *
 * @author Ne-Lexa alexey@nelexa.ru
 * @license MIT
 */
abstract class Buffer
{
    const BIG_ENDIAN = 'BIG_ENDIAN';
    const LITTLE_ENDIAN = 'LITTLE_ENDIAN';

    /**
     * @var int
     */
    protected $position = 0;
    /**
     * @var int
     */
    private $limit = 0;
    /**
     * @var bool Is little endian order
     */
    private $orderLittleEndian = false;
    /**
     * @var boolean
     */
    private $isReadOnly = false;

    protected static function checkPhpSupport()
    {
        if (PHP_VERSION_ID < 70015 || PHP_VERSION_ID === 70100) {
            throw new \RuntimeException('Operation not supported for PHP versions less than 7.0.15 and 7.1.1. Current version ' . PHP_VERSION);
        }
    }

    /**
     * Get buffer position
     *
     * @return int
     */
    final public function position()
    {
        return $this->position;
    }

    /**
     * Rewinds this buffer. The position is set to zero.
     *
     * Invoke this method before a sequence of channel-write or get
     * operations, assuming that the limit has already been set
     * appropriately.
     *
     * For example:
     *
     * $buf->writeString("Hello");  // Write remaining data
     * $buf->rewind();              // Rewind buffer
     * $buf->get(5);                // get 5 bytes (Hello)
     *
     * @return Buffer
     * @throws BufferException
     */
    final public function rewind()
    {
        return $this->setPosition(0);
    }

    /**
     * Set buffer position.
     *
     * @param int $position
     * @return Buffer
     * @throws BufferException
     */
    public function setPosition($position)
    {
        $position = (int)$position;
        if ($position > $this->limit) {
            throw new BufferException('Set position ' . $position . ' invalid. Exceeded limit ' . $this->limit);
        }
        $this->position = $position;
        return $this;
    }

    /**
     * Flips this buffer. The limit is set to the current position and then
     * the position is set to zero.
     *
     * After a sequence of channel-read or put operations, invoke
     * this method to prepare for a sequence of channel-write or relative
     * get operations.
     *
     * @return Buffer
     */
    abstract public function flip();

    /**
     * Returns the number of elements between the current position and the
     * limit.
     *
     * @return int The number of elements remaining in this buffer
     */
    public function remaining()
    {
        return $this->limit - $this->position;
    }

    /**
     * Tells whether there are any elements between the current position and
     * the limit.
     *
     * @return boolean true if, and only if, there is at least one element remaining in this buffer
     */
    public function hasRemaining()
    {
        return $this->position < $this->limit;
    }

    /**
     * Modifies this buffer's byte order.
     *
     * @param string $order The new byte order, either Buffer::BIG_ENDIAN or Buffer::LITTLE_ENDIAN
     * @return Buffer
     * @see Buffer::BIG_ENDIAN
     * @see Buffer::LITTLE_ENDIAN
     *
     */
    final public function setOrder($order)
    {
        $this->orderLittleEndian = $order === self::LITTLE_ENDIAN;
        return $this;
    }

    /**
     * Set read only buffer.
     *
     * @param boolean $isReadOnly
     * @return Buffer
     */
    public function setReadOnly($isReadOnly)
    {
        $this->isReadOnly = $isReadOnly;
        return $this;
    }

    /**
     * Skip 1 byte
     *
     * @return Buffer
     * @throws BufferException
     */
    public function skipByte()
    {
        return $this->skip(1);
    }

    /**
     * Skip number bytes.
     *
     * @param int $n The number of bytes to be skipped. The value may be negative.
     * @return Buffer
     * @throws BufferException
     */
    public function skip($n)
    {
        return $this->setPosition($this->position + $n);
    }

    /**
     * Skip short (2 bytes)
     *
     * @return Buffer
     * @throws BufferException
     */
    public function skipShort()
    {
        return $this->skip(2);
    }

    /**
     * Skip int (4 bytes)
     *
     * @return Buffer
     * @throws BufferException
     */
    public function skipInt()
    {
        return $this->skip(4);
    }

    /**
     * Skip long (8 bytes)
     *
     * @return Buffer
     * @throws BufferException
     */
    public function skipLong()
    {
        return $this->skip(8);
    }

    /**
     * Skip float (4 bytes)
     *
     * @return $this
     * @throws BufferException
     */
    public function skipFloat()
    {
        return $this->skip(4);
    }

    /**
     * Skip double (8 bytes)
     *
     * @return $this
     * @throws BufferException
     */
    public function skipDouble()
    {
        return $this->skip(8);
    }

    /**
     * Reads one input byte and returns true if that byte is nonzero,
     * false if that byte is zero.
     *
     * @return bool the boolean value read.
     * @throws BufferException
     */
    public function getBoolean()
    {
        return (bool)$this->getUnsignedByte();
    }

    /**
     * Reads one input byte, zero-extends
     * it to type int, and returns
     * the result, which is therefore in the range
     * 0 through 255.
     *
     * @return int the unsigned 8-bit value read.
     * @throws BufferException
     */
    public function getUnsignedByte()
    {
        return unpack('C', $this->get(1))[1];
    }

    /**
     * Relative get method.
     * Reads the string at this buffer's current position, and then increments the position.
     *
     * @param int $length
     * @return string The strings at the buffer's current position
     * @throws BufferException
     */
    abstract protected function get($length);

    /**
     * Reads and returns one input byte.
     * The byte is treated as a signed value in
     * the range -128 through 127, inclusive.
     *
     * @return int the 8-bit value read.
     * @throws BufferException
     */
    public function getByte()
    {
        return Cast::toByte($this->getUnsignedByte());
    }

    /**
     * Reads two input bytes and returns
     * a short value in the range -32768 through 32767.
     *
     * @return int the 16-bit value read.
     * @throws BufferException
     */
    public function getShort()
    {
        return Cast::toShort($this->getUnsignedShort());
    }

    /**
     * Reads two input bytes and returns
     * an int value in the range 0 through 65535.
     *
     * @return int the unsigned 16-bit value read.
     * @throws BufferException
     */
    public function getUnsignedShort()
    {
        return unpack($this->orderLittleEndian ? 'v' : 'n', $this->get(2))[1];
    }

    /**
     * Reads four input bytes and returns an unsigned short value
     * in the range -2147483648 through 2147483647.
     *
     * @return int the int value read.
     * @throws BufferException
     */
    public function getInt()
    {
        return Cast::toInt($this->getUnsignedInt());
    }

    /**
     * Reads four input bytes and returns an unsigned int value
     * in the range 0 through 4294967296.
     *
     * @return int the unsigned int value read.
     * @throws BufferException
     */
    public function getUnsignedInt()
    {
        return unpack($this->orderLittleEndian ? 'V' : 'N', $this->get(4))[1];
    }

    /**
     * Reads eight input bytes and returns a long value
     * in the range -9223372036854775808 through 9223372036854775807.
     *
     * @return string|int the long value read.
     * @throws BufferException
     */
    public function getLong()
    {
        $data = $this->get(8);
        if (PHP_VERSION_ID >= 50603) {
            return unpack($this->orderLittleEndian ? 'P' : 'J', $data)[1];
        }

        if ($this->orderLittleEndian) {
            $unpack = unpack('Va/Vb', $data);
            return $unpack['a'] + ($unpack['b'] << 32);
        }

        $unpack = unpack('Na/Nb', $data);
        return ($unpack['a'] << 32) | $unpack['b'];
    }

    /**
     * Reads four input bytes and returns a float value
     *
     * @return float the float value read.
     * @throws BufferException
     */
    public function getFloat()
    {
        self::checkPhpSupport();
        return unpack($this->orderLittleEndian ? 'g' : 'G', $this->get(4))[1];
    }

    /**
     * Reads four input bytes and returns a double value
     *
     * @return double the double value read.
     * @throws BufferException
     */
    public function getDouble()
    {
        self::checkPhpSupport();
        return unpack($this->orderLittleEndian ? 'e' : 'E', $this->get(8))[1];
    }

    /**
     * Reads $length bytes from an input stream.
     *
     * @param $length int
     * @return int[]
     * @throws BufferException
     */
    public function getArrayBytes($length)
    {
        if ($length > 0) {
            return array_values(
                unpack('c*', $this->get($length))
            );
        }
        return [];
    }

    /**
     * Reads in a string that has been encoded using
     * a modified UTF-8 format.
     *
     * First, two bytes are read and used to
     * construct an unsigned 16-bit integer in
     * exactly the manner of the Buffer::readUnsignedShort()
     * method. This integer value is called the UTF length
     * and specifies the number of additional bytes to be read.
     *
     * Analog java @see java.io.DataOutputStream#readUTF()
     *
     * @return string
     * @throws BufferException
     */
    public function getUTF()
    {
        $size = $this->getUnsignedShort();
        if ($size > 0) {
            return $this->getString($size);
        }
        return '';
    }

    /**
     * Reads $length input bytes and returns a string value.
     *
     * @param $length int
     * @return string
     * @throws BufferException
     */
    public function getString($length)
    {
        if ($length > 0) {
            return $this->get($length);
        }
        return '';
    }

    /**
     * Reads $length * 2 input bytes and returns a string value.
     *
     * @param $length int
     * @return string
     * @throws BufferException
     * @deprecated
     */
    public function getUTF16($length)
    {
        if ($length > 0) {
            return implode('', array_map('chr', array_values(unpack('S*', $this->get($length << 1)))));
        }
        return '';
    }

    /**
     * Insert boolean value
     *
     * @param $bool
     * @return Buffer
     * @throws BufferException
     */
    public function insertBoolean($bool)
    {
        return $this->insert($this->writeBoolean($bool));
    }

    /**
     * Insert Buffer or string.
     *
     * @param Buffer|string $buffer
     * @return Buffer
     * @throws BufferException
     */
    abstract public function insert($buffer);

    /**
     * @param bool $bool
     * @return string
     * @throws BufferException
     */
    protected function writeBoolean($bool)
    {
        if ($bool === null) {
            throw new BufferException('null boolean');
        }
        return pack('c', $bool ? 1 : 0);
    }

    /**
     * Insert byte (-128 >= byte <= 127)
     *
     * @param int|string $byte
     * @return Buffer
     * @throws BufferException
     */
    public function insertByte($byte)
    {
        return $this->insert($this->writeByte($byte));
    }

    /**
     * @param int|string $byte
     * @return string
     * @throws BufferException
     */
    protected function writeByte($byte)
    {
        if ($byte === null) {
            throw new BufferException('null byte');
        }
        return pack('c', $byte);
    }

    /**
     * Insert short value (-32768 >= short <= 32767)
     *
     * @param int|string $v
     * @return Buffer
     * @throws BufferException
     */
    public function insertShort($v)
    {
        return $this->insert($this->writeShort($v));
    }

    /**
     * @param int|string $v
     * @return string
     * @throws BufferException
     */
    protected function writeShort($v)
    {
        if ($v === null) {
            throw new BufferException('null short');
        }
        return pack($this->orderLittleEndian ? 'v' : 'n', $v);
    }

    /**
     * Insert integer value (-2147483648 >= int <= 2147483647)
     *
     * @param int|string $v
     * @return Buffer
     * @throws BufferException
     */
    public function insertInt($v)
    {
        return $this->insert($this->writeInt($v));
    }

    /**
     * @param int|string $v
     * @return string
     * @throws BufferException
     */
    protected function writeInt($v)
    {
        if ($v === null) {
            throw new BufferException('null int');
        }
        return pack($this->orderLittleEndian ? 'V' : 'N', $v);
    }

    /**
     * Insert long value (-9223372036854775808 >= long <= 9223372036854775807)
     *
     * @param int|string $v
     * @return Buffer
     * @throws BufferException
     */
    public function insertLong($v)
    {
        return $this->insert($this->writeLong($v));
    }

    /**
     * @param int|string $v
     * @return string
     * @throws BufferException
     */
    protected function writeLong($v)
    {
        if ($v === null) {
            throw new BufferException('null long');
        }
        if (PHP_VERSION_ID >= 50603) {
            return pack($this->orderLittleEndian ? 'P' : 'J', $v);
        }

        $left = 0xffffffff00000000;
        $right = 0x00000000ffffffff;
        if ($this->orderLittleEndian) {
            $r = ($v & $left) >> 32;
            $l = $v & $right;
            return pack('VV', $l, $r);
        }

        $l = ($v & $left) >> 32;
        $r = $v & $right;
        return pack('NN', $l, $r);
    }

    /**
     * Insert float value
     *
     * @param float $v
     * @return Buffer
     * @throws BufferException
     */
    public function insertFloat($v)
    {
        return $this->insert($this->writeFloat($v));
    }

    /**
     * @param float $v
     * @return string
     * @throws BufferException
     */
    protected function writeFloat($v)
    {
        self::checkPhpSupport();
        if ($v === null) {
            throw new BufferException('null float');
        }
        return pack($this->orderLittleEndian ? 'g' : 'G', $v);
    }

    /**
     * Insert double value
     *
     * @param double $v
     * @return Buffer
     * @throws BufferException
     */
    public function insertDouble($v)
    {
        return $this->insert($this->writeDouble($v));
    }

    /**
     * @param double $v
     * @return string
     * @throws BufferException
     */
    protected function writeDouble($v)
    {
        self::checkPhpSupport();
        if ($v === null) {
            throw new BufferException('null double');
        }
        return pack($this->orderLittleEndian ? 'e' : 'E', $v);
    }

    /**
     * Insert string
     *
     * @param string $string
     * @return Buffer
     * @throws BufferException
     */
    public function insertString($string)
    {
        return $this->insert($this->writeString($string));
    }

    /**
     * @param string $string
     * @return string
     */
    protected function writeString($string)
    {
        return $string;
    }

    /**
     * Insert array bytes
     *
     * @param array $bytes
     * @return Buffer
     * @throws BufferException
     */
    public function insertArrayBytes(array $bytes)
    {
        return $this->insert($this->writeArrayBytes($bytes));
    }

    /**
     * @param array $bytes
     * @return string
     */
    protected function writeArrayBytes(array $bytes)
    {
        return call_user_func_array('pack', array_merge(['c*'], $bytes));
    }

    /**
     * Writes a string to the underlying output stream using
     * modified UTF-8 encoding in a machine-independent manner.
     *
     * @param string $string
     * @return Buffer
     * @throws BufferException
     * @see Buffer::writeUTF()
     *
     */
    public function insertUTF($string)
    {
        return $this->insert($this->writeUTF($string));
    }

    /**
     * Writes a string to the underlying output stream using
     * modified UTF-8 encoding in a machine-independent manner.
     *
     * First, two bytes are written to the output stream as if by the
     * Buffer::writeShort() method giving the number of bytes to
     * follow. This value is the number of bytes actually written out,
     * not the length of the string.
     *
     * Analog java @see java.io.DataOutputStream#writeUTF()
     *
     * @param string $str
     * @return string
     * @throws BufferException
     */
    protected function writeUTF($str)
    {
        if ($str === null) {
            throw new BufferException('$str is null');
        }
        $bytes = unpack('c*', $str);
        $length = count($bytes);
        if ($length > 65535) {
            throw new BufferException('Encoded string too long: ' . $length . ' bytes');
        }
        array_unshift($bytes, 'c*');
        return $this->writeShort($length) . call_user_func_array('pack', $bytes);
    }

    /**
     * Insert UTF16 string
     *
     * @param string $string
     * @return Buffer
     * @throws BufferException
     * @deprecated
     */
    public function insertUTF16($string)
    {
        return $this->insert($this->writeUTF16($string));
    }

    /**
     * @param string $string
     * @return string
     * @throws BufferException
     * @deprecated
     */
    protected function writeUTF16($string)
    {
        if ($string === null) {
            throw new BufferException('$string is null');
        }
        $args = array_map('ord', str_split($string));
        array_unshift($args, 'S*');
        return call_user_func_array('pack', $args);
    }

    /**
     * Put boolean value
     *
     * @param $bool
     * @return Buffer
     * @throws BufferException
     */
    public function putBoolean($bool)
    {
        return $this->put($this->writeBoolean($bool));
    }

    /**
     * Relative put method (optional operation).
     *
     * Writes the given string into this buffer at the current
     * position, and then increments the position.
     *
     * @param Buffer|string $buffer
     * @return Buffer
     * @throws BufferException
     */
    abstract public function put($buffer);

    /**
     * Put byte (-128 >= byte <= 127)
     *
     * @param int|string $byte
     * @return Buffer
     * @throws BufferException
     */
    public function putByte($byte)
    {
        return $this->put($this->writeByte($byte));
    }

    /**
     * Put short value (-32768 >= short <= 32767)
     *
     * @param int|string $v
     * @return Buffer
     * @throws BufferException
     */
    public function putShort($v)
    {
        return $this->put($this->writeShort($v));
    }

    /**
     * Put integer value (-2147483648 >= int <= 2147483647)
     *
     * @param int|string $v
     * @return Buffer
     * @throws BufferException
     */
    public function putInt($v)
    {
        return $this->put($this->writeInt($v));
    }

    /**
     * Put long value (-9223372036854775808 >= long <= 9223372036854775807)
     *
     * @param int|string $v
     * @return Buffer
     * @throws BufferException
     */
    public function putLong($v)
    {
        return $this->put($this->writeLong($v));
    }

    /**
     * Put float value
     *
     * @param float $v
     * @return Buffer
     * @throws BufferException
     */
    public function putFloat($v)
    {
        return $this->put($this->writeFloat($v));
    }

    /**
     * Put double value
     *
     * @param double $v
     * @return Buffer
     * @throws BufferException
     */
    public function putDouble($v)
    {
        return $this->put($this->writeDouble($v));
    }

    /**
     * Put string
     *
     * @param string $string
     * @return Buffer
     * @throws BufferException
     */
    public function putString($string)
    {
        return $this->put($this->writeString($string));
    }

    /**
     * Put array bytes
     *
     * @param array $bytes
     * @return Buffer
     * @throws BufferException
     */
    public function putArrayBytes(array $bytes)
    {
        return $this->put($this->writeArrayBytes($bytes));
    }

    /**
     * Put UTF string (Format - java DataOutputStream.writeUTF)
     *
     * @param string $str
     * @return Buffer
     * @throws BufferException
     */
    public function putUTF($str)
    {
        return $this->put($this->writeUTF($str));
    }

    /**
     * Put UTF16 string
     *
     * @param string $str
     * @return Buffer
     * @throws BufferException
     * @deprecated
     */
    public function putUTF16($str)
    {
        return $this->put($this->writeUTF16($str));
    }

    /**
     * Replace by boolean value
     *
     * @param bool $bool
     * @param int $length
     * @return Buffer
     * @throws BufferException
     */
    public function replaceBoolean($bool, $length)
    {
        return $this->replace($this->writeBoolean($bool), $length);
    }

    /**
     * Replace $length bytes in a string or Buffer.
     *
     * @param Buffer|string $buffer
     * @param int $length remove length bytes
     * @return Buffer
     * @throws BufferException
     */
    abstract public function replace($buffer, $length);

    /**
     * Replace by byte (-128 >= byte <= 127)
     *
     * @param int|string $byte
     * @param int $length
     * @return Buffer
     * @throws BufferException
     */
    public function replaceByte($byte, $length)
    {
        return $this->replace($this->writeByte($byte), $length);
    }

    /**
     * Replace short value (-32768 >= short <= 32767)
     *
     * @param int|string $v
     * @param int $length
     * @return Buffer
     * @throws BufferException
     */
    public function replaceShort($v, $length)
    {
        return $this->replace($this->writeShort($v), $length);
    }

    /**
     * Replace integer value (-2147483648 >= int <= 2147483647)
     *
     * @param int|string $v
     * @param int $length
     * @return Buffer
     * @throws BufferException
     */
    public function replaceInt($v, $length)
    {
        return $this->replace($this->writeInt($v), $length);
    }

    /**
     * Replace long value (-9223372036854775808 >= long <= 9223372036854775807)
     *
     * @param int|string $v
     * @param int $length
     * @return Buffer
     * @throws BufferException
     */
    public function replaceLong($v, $length)
    {
        return $this->replace($this->writeLong($v), $length);
    }

    /**
     * Replace float value
     *
     * @param float $v
     * @param int $length
     * @return Buffer
     * @throws BufferException
     */
    public function replaceFloat($v, $length)
    {
        return $this->replace($this->writeFloat($v), $length);
    }

    /**
     * Replace double value
     *
     * @param double $v
     * @param int $length
     * @return Buffer
     * @throws BufferException
     */
    public function replaceDouble($v, $length)
    {
        return $this->replace($this->writeDouble($v), $length);
    }

    /**
     * Replace string
     *
     * @param string $string
     * @param int $length
     * @return Buffer
     * @throws BufferException
     */
    public function replaceString($string, $length)
    {
        return $this->replace($this->writeString($string), $length);
    }

    /**
     * Insert array bytes
     *
     * @param array $bytes
     * @param int $length
     * @return Buffer
     * @throws BufferException
     */
    public function replaceArrayBytes(array $bytes, $length)
    {
        return $this->replace($this->writeArrayBytes($bytes), $length);
    }

    /**
     * Replace UTF string (Format - java DataOutStream.writeUTF)
     *
     * @param string $str
     * @param int $length
     * @return Buffer
     * @throws BufferException
     */
    public function replaceUTF($str, $length)
    {
        return $this->replace($this->writeUTF($str), $length);
    }

    /**
     * Replace UTF16 string
     *
     * @param string $str
     * @param int $length
     * @return Buffer
     * @throws BufferException
     * @deprecated
     */
    public function replaceUTF16($str, $length)
    {
        return $this->replace($this->writeUTF16($str), $length);
    }

    /**
     * Remove a certain number of bytes.
     *
     * @param int $length
     * @return Buffer
     * @throws BufferException
     */
    abstract public function remove($length);

    /**
     * Truncate data
     *
     * @param int $size
     * @return Buffer
     */
    abstract public function truncate($size = 0);

    /**
     * Close buffer. If this buffer resource that closes the stream.
     */
    abstract public function close();

    /**
     * @return string
     */
    abstract public function toString();

    /**
     * @return string
     */
    public function __toString()
    {
        return get_called_class() . '{' .
            'position=' . $this->position .
            ', limit=' . $this->size() .
            ', order=' . $this->order() .
            ', readOnly=' . ($this->isReadOnly() ? 'true' : 'false') .
            '}';
    }

    /**
     * Returns this buffer's limit.
     *
     * @return int The limit of this buffer
     */
    final public function size()
    {
        return $this->limit;
    }

    /**
     * Retrieves this buffer's byte order.
     *
     * The byte order is used when reading or writing multibyte values, and
     * when creating buffers that are views of this byte buffer. The order of
     * a newly-created byte buffer is always Buffer::BIG_ENDIAN
     *
     * @return string This buffer's byte order
     * @see Buffer::LITTLE_ENDIAN
     *
     * @see Buffer::BIG_ENDIAN
     */
    final public function order()
    {
        return $this->orderLittleEndian ? self::LITTLE_ENDIAN : self::BIG_ENDIAN;
    }

    /**
     * Is read only buffer.
     *
     * @return boolean
     */
    final public function isReadOnly()
    {
        return $this->isReadOnly;
    }

    /**
     * Sets this buffer's limit. If the position is larger than the new limit
     * then it is set to the new limit.
     *
     * @param $newLimit int
     * @return Buffer
     * @throws BufferException
     */
    protected function newLimit($newLimit)
    {
        if ($newLimit < 0) {
            throw new BufferException('New Limit < 0');
        }
        $this->limit = $newLimit;
        if ($this->position > $this->limit) {
            $this->position = $this->limit;
        }
        return $this;
    }

    /**
     * Buffer's byte order is Buffer::LITTLE_ENDIAN
     *
     * @return bool
     * @see Buffer::LITTLE_ENDIAN
     *
     * @see Buffer::BIG_ENDIAN
     */
    final protected function isOrderLE()
    {
        return $this->orderLittleEndian;
    }
}
