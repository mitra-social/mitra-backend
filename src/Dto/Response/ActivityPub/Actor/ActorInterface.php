<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityPub\Actor;

interface ActorInterface
{
    public function getId(): string;

    public function getInbox(): string;

    public function getOutbox(): string;

    public function getPreferredUsername(): ?string;

    public function getName(): ?string;

    public function getType(): string;
}
