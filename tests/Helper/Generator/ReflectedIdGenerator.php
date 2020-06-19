<?php

declare(strict_types=1);

namespace Mitra\Tests\Helper\Generator;

use Mitra\Slim\IdGeneratorInterface;

final class ReflectedIdGenerator implements IdGeneratorInterface
{
    /**
     * @var string
     */
    private $id;

    public function getId(): string
    {
        if (null === $this->id) {
            throw new \RuntimeException('No id set, set one by calling `setId()`');
        }

        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
