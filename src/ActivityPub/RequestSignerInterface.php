<?php

declare(strict_types=1);

namespace Mitra\ActivityPub;

use Mitra\Entity\User\InternalUser;
use Psr\Http\Message\RequestInterface;

interface RequestSignerInterface
{
    public function signRequest(RequestInterface $request, ?InternalUser $user): RequestInterface;
}
