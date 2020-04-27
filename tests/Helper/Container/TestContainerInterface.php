<?php

declare(strict_types=1);

namespace Mitra\Tests\Helper\Container;

use Psr\Container\ContainerInterface;

interface TestContainerInterface extends ContainerInterface
{
    /**
     * @param string $id
     * @param mixed $service
     * @return mixed
     */
    public function set(string $id, $service);
}
