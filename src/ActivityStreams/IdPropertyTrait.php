<?php

declare(strict_types=1);

namespace Mitra\ActivityStreams;

trait IdPropertyTrait
{
    /**
     * @var  string|null
     */
    protected $id;

    public function getId(): ?string
    {
        return $this->id;
    }
}
