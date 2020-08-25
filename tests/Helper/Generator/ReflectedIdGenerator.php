<?php

declare(strict_types=1);

namespace Mitra\Tests\Helper\Generator;

use Mitra\Slim\IdGeneratorInterface;

final class ReflectedIdGenerator implements IdGeneratorInterface
{
    /**
     * @var array<string>
     */
    private $ids = [];

    /**
     * @var int
     */
    private $idCount = 0;

    /**
     * @var int
     */
    private $currentPosition = 0;

    public function getId(): string
    {
        ++$this->currentPosition;

        if ([] === $this->ids) {
            throw new \OutOfBoundsException(sprintf(
                'Requested %d ids but only %d ids were available.',
                $this->currentPosition,
                $this->idCount
            ));
        }

        return array_shift($this->ids);
    }

    /**
     * @param array<string> $ids
     */
    public function setIds(array $ids): void
    {
        $this->ids = $ids;
        $this->idCount = count($ids);
        $this->currentPosition = 0;
    }
}
