<?php

namespace Nelexa\Buffer;

/**
 * Read And Write Binary Data From String.
 *
 * This is class defines methods for reading and writing values of all primitive types. Primitive values are translated
 * to (or from) sequences of bytes according to the buffer's current byte order, which may be retrieved and modified
 * via the order methods. The initial order of a byte buffer is always Buffer::BIG_ENDIAN.
 *
 * @author Ne-Lexa alexey@nelexa.ru
 * @license MIT
 */
class StringBuffer extends Buffer
{
    /**
     * @var string
     */
    private $string;

    /**
     * Wraps a string into a buffer.
     *
     * @param string $string
     * @throws BufferException
     */
    public function __construct($string = '')
    {
        if ($string === null) {
            throw new BufferException('null bytes');
        }
        $this->setString($string);
    }

    /**
     * @param string $string
     * @throws BufferException
     */
    final public function setString($string)
    {
        $this->string = $string;
        $this->rewind();
        $this->newLimit(strlen($this->string));
    }

    /**
     * @return string
     */
    final public function toString()
    {
        return $this->string;
    }

    /**
     * Flips this buffer.  The limit is set to the current position and then
     * the position is set to zero.
     *
     * After a sequence of channel-read or put operations, invoke
     * this method to prepare for a sequence of channel-write or relative
     * get operations.
     *
     * @return Buffer
     * @throws BufferException
     */
    final public function flip()
    {
        $this->setString(substr($this->string, 0, $this->position));
        $this->setPosition(0);
        return $this;
    }

    /**
     * @param Buffer|string $buffer
     * @return Buffer
     * @throws BufferException
     */
    public function insert($buffer)
    {
        if ($this->isReadOnly()) {
            throw new BufferException('Read Only');
        }
        if ($buffer === null) {
            throw new BufferException('null buffer');
        }
        if ($buffer instanceof Buffer) {
            $buffer = $buffer->toString();
        }
        $length = strlen($buffer);
        $this->string = substr_replace($this->string, $buffer, $this->position, 0);
        $this->newLimit($this->size() + $length);
        $this->skip($length);
        return $this;
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
    public function put($buffer)
    {
        if ($this->isReadOnly()) {
            throw new BufferException('Read Only');
        }
        if ($buffer === null) {
            throw new BufferException('null buffer');
        }
        if ($buffer instanceof Buffer) {
            $length = $buffer->size();
            $buffer = $buffer->toString();
        } else {
            $length = strlen($buffer);
        }
        if ($length > $this->remaining()) {
            throw new BufferException('put length > remaining');
        }
        $this->string = substr_replace($this->string, $buffer, $this->position, $length);
        $this->skip($length);
        return $this;
    }

    /**
     * @param Buffer|string $buffer
     * @param int $length remove length bytes
     * @return Buffer
     * @throws BufferException
     */
    public function replace($buffer, $length)
    {
        $length = (int)$length;
        if ($this->isReadOnly()) {
            throw new BufferException('Read Only');
        }
        if ($length < 0) {
            throw new BufferException('length < 0');
        }
        if ($length > $this->remaining()) {
            throw new BufferException('replace length > remaining');
        }
        if ($buffer === null) {
            throw new BufferException('null buffer');
        }
        if ($buffer instanceof Buffer) {
            $buffer = $buffer->toString();
        }
        $bufferLength = strlen($buffer);
        $this->string = substr_replace($this->string, $buffer, $this->position, $length);
        $this->newLimit($this->size() + $bufferLength - $length);
        $this->skip($bufferLength);
        return $this;
    }

    /**
     * @param int $length
     * @return Buffer
     * @throws BufferException
     */
    final public function remove($length)
    {
        if ($this->isReadOnly()) {
            throw new BufferException('Read Only');
        }
        if ($length < 0) {
            throw new BufferException('length < 0');
        }
        if ($length > $this->remaining()) {
            throw new BufferException('remove length > remaining');
        }
        $this->string = substr_replace($this->string, '', $this->position, $length);
        $this->newLimit($this->size() - $length);
        return $this;
    }

    /**
     * Truncate buffer
     *
     * @param int $size
     * @return Buffer
     * @throws BufferException
     */
    final public function truncate($size = 0)
    {
        if ($size < $this->size()) {
            $this->setString(substr($this->string, 0, $size));
        }
        return $this;
    }

    /**
     * Destruct object, close file description.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Close buffer. If this buffer resource that closes the stream.
     */
    public function close()
    {
        if ($this->string !== null) {
            $this->string = null;
        }
    }

    /**
     * Relative get method.
     * Reads the string at this buffer's current position, and then increments the position.
     *
     * @param $length
     * @return string The strings at the buffer's current position
     * @throws BufferException
     */
    protected function get($length)
    {
        if ($length > $this->remaining()) {
            throw new BufferException('get length > remaining');
        }
        $str = substr($this->string, $this->position, $length);
        $this->skip($length);
        return $str;
    }
}
