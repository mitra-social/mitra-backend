<?php

declare(strict_types=1);

namespace Mitra\React;

final class Process implements \ArrayAccess
{
    /**
     * @var int
     */
    private $pid;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Process constructor.
     * @param int $pid
     */
    public function __construct(int $pid)
    {
        $this->pid = $pid;
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
