<?php

declare(strict_types=1);

namespace Mitra\Validator\Symfony;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\LoaderInterface;

final class ClassMapLoader implements LoaderInterface
{

    /**
     * @var array
     */
    private $classMap = [];

    /**
     * @param array              $classMap
     */
    public function __construct(array $classMap)
    {
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

        $mapping = $this->classMap[$className];

        $mapping->configureMapping($metadata);

        return true;
    }
}
