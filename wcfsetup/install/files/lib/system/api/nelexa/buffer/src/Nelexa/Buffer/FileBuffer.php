<?php

namespace Nelexa\Buffer;

/**
 * Read And Write Binary File.
 *
 * This is class defines methods for reading and writing values of all primitive types. Primitive values are translated
 * to (or from) sequences of bytes according to the buffer's current byte order, which may be retrieved and modified
 * via the order methods. The initial order of a byte buffer is always Buffer::BIG_ENDIAN.
 *
 * @author Ne-Lexa alexey@nelexa.ru
 * @license MIT
 */
class FileBuffer extends ResourceBuffer
{
    /**
     * @var bool
     */
    private $writable;

    /**
     * @param string $file
     * @throws BufferException
     */
    public function __construct($file)
    {
        if ($file === null) {
            throw new BufferException('file is null');
        }
        if (!is_readable($file)) {
            throw new BufferException("file '" . $file . "' is not readable.");
        }
        $this->writable = is_writable(dirname($file));
        parent::setReadOnly(!$this->writable);

        $mode = !$this->writable ? 'rb' : (file_exists($file) ? 'r+' : 'w+') . 'b';

        if (($fp = fopen($file, $mode)) === false) {
            throw new BufferException("file '$file' can not open.");
        }
        parent::__construct($fp);
    }

    /**
     * @param bool $isReadOnly
     * @return Buffer
     * @throws BufferException
     */
    public function setReadOnly($isReadOnly)
    {
        if (!$this->writable && !$isReadOnly) {
            throw new BufferException('You can not set the recording flag.' .
                'The directory containing the file is not available for recording.');
        }
        return parent::setReadOnly($isReadOnly);
    }
}
