<?php

namespace Nelexa\Buffer;

/**
 * Read And Write Binary Data From Memory.
 *
 * This is class defines methods for reading and writing values of all primitive types. Primitive values are translated
 * to (or from) sequences of bytes according to the buffer's current byte order, which may be retrieved and modified
 * via the order methods. The initial order of a byte buffer is always Buffer::BIG_ENDIAN.
 *
 * @author Ne-Lexa alexey@nelexa.ru
 * @license MIT
 */
class MemoryResourceBuffer extends ResourceBuffer
{
    /**
     * Wraps a string into a buffer.
     *
     * @param string $bytes
     * @throws BufferException
     */
    public function __construct($bytes = '')
    {
        if (($fp = fopen('php://memory', 'w+b')) === false) {
            throw new BufferException('Can not open memory');
        }
        if (!empty($bytes)) {
            fwrite($fp, (string)$bytes);
            rewind($fp);
        }
        parent::__construct($fp);
    }
}
