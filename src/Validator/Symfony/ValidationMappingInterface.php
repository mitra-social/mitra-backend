<?php

declare(strict_types=1);

namespace Mitra\Validator\Symfony;

use Symfony\Component\Validator\Mapping\ClassMetadata;

interface ValidationMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     */
    public function configureMapping(ClassMetadata $metadata);
}
