<?php

declare(strict_types=1);

namespace Mitra\Tests\Helper\Container;

use Pimple\Container;

final class PimpleTestContainer implements TestContainerInterface
{

    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get($id)
    {
        return $this->container[$id];
    }

    public function has($id)
    {
        return isset($this->container[$id]);
    }

    public function set(string $id, $service)
    {
        $this->container[$id] = $service;
    }
}
