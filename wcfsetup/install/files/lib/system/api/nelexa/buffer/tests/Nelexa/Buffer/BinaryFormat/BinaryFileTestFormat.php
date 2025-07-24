<?php

namespace Nelexa\Buffer\BinaryFormat;

use Nelexa\Buffer\Buffer;

class BinaryFileTestFormat implements BinaryFileInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var BinaryFileItem[]
     */
    private $items;

    /**
     * BinaryFileTestFormat constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $name
     * @param BinaryFileItem[] $items
     * @return BinaryFileTestFormat
     */
    public static function create($name, array $items)
    {
        $instance = new self();
        $instance->setName($name);
        $instance->setItems($items);
        return $instance;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return BinaryFileItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param BinaryFileItem[] $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @param Buffer $buffer
     * @throws \Nelexa\Buffer\BufferException
     */
    public function readObject(Buffer $buffer)
    {
        $this->name = $buffer->getUTF();
        $length = $buffer->getInt();
        $this->items = [];
        for ($i = 0; $i < $length; $i++) {
            $item = new BinaryFileItem();
            $item->readObject($buffer);
            $this->items[] = $item;
        }
    }

    /**
     * @param Buffer $buffer
     * @throws \Nelexa\Buffer\BufferException
     */
    public function writeObject(Buffer $buffer)
    {
        $buffer->insertUTF($this->name);
        $length = count($this->items);
        $buffer->insertInt($length);
        foreach ($this->items as $item) {
            $item->writeObject($buffer);
        }
    }
}
