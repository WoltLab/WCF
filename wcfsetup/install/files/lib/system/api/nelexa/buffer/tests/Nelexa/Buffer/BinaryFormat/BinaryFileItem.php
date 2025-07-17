<?php

namespace Nelexa\Buffer\BinaryFormat;

use Nelexa\Buffer\Buffer;

class BinaryFileItem implements BinaryFileInterface
{
    /**
     * @var int
     */
    private $timeMillis;

    /**
     * @var array
     */
    private $categories;

    /**
     * BinaryFileItem constructor.
     */
    public function __construct()
    {
    }

    public static function create($timeMillis, array $categories)
    {
        $instance = new self();
        $instance->setTimeMillis($timeMillis);
        $instance->setCategories($categories);
        return $instance;
    }

    /**
     * @return int
     */
    public function getTimeMillis()
    {
        return $this->timeMillis;
    }

    /**
     * @param int $timeMillis
     */
    public function setTimeMillis($timeMillis)
    {
        $this->timeMillis = $timeMillis;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @param Buffer $buffer
     * @throws \Nelexa\Buffer\BufferException
     */
    public function readObject(Buffer $buffer)
    {
        $this->timeMillis = $buffer->getLong();
        $length = $buffer->getInt();
        $this->categories = [];
        for ($i = 0; $i < $length; $i++) {
            $this->categories[] = $buffer->getUTF();
        }
    }

    /**
     * @param Buffer $buffer
     * @throws \Nelexa\Buffer\BufferException
     */
    public function writeObject(Buffer $buffer)
    {
        $buffer->insertLong($this->timeMillis);
        $length = count($this->categories);
        $buffer->insertInt($length);
        foreach ($this->categories as $i => $iValue) {
            $buffer->insertUTF($this->categories[$i]);
        }
    }
}
