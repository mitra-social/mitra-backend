<?php

declare(strict_types=1);

namespace Mitra\Validator\Symfony;

use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\LoaderInterface;

final class ClassMapLoader implements LoaderInterface
{

    /**
     * @var array<string,ValidationMappingInterface>
     */
    private $classMap = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     * @param array<string,ValidationMappingInterface> $classMap
     */
    public function __construct(ContainerInterface $container, array $classMap)
    {
        $this->container = $container;
        $this->classMap = $classMap;
    }

    /**
     * @param ClassMetadata $metadata The metadata to load
     * @return boolean Whether the loader succeeded
     * @throws \RuntimeException
     */
    public function loadClassMetadata(ClassMetadata $metadata)
    {
        $className = $metadata->getClassName();

        if (false === isset($this->classMap[$className])) {
            return false;
        }

        $identifier = $this->classMap[$className];

        if ($this->container->has($identifier)) {
            $mapping = $this->container->get($identifier);
        } elseif (class_exists($identifier)) {
            $mapping = new $identifier();
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Could not load validation mapping for class `%s`',
                $className
            ));
        }

        $mapping->configureMapping($metadata);

        return true;
    }
}
