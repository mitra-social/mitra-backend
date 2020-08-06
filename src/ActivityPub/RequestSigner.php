<?php

declare(strict_types=1);

namespace Mitra\ActivityPub;

use HttpSignatures\Algorithm;
use HttpSignatures\HeaderList;
use HttpSignatures\Key;
use HttpSignatures\Signer;
use Mitra\Entity\User\InternalUser;
use Mitra\Slim\UriGenerator;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

final class RequestSigner implements RequestSignerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Key
     */
    private $instanceKey;

    /**
     * @var UriGenerator
     */
    private $uriGenerator;

    public function __construct(UriGenerator $uriGenerator, string $instancePrivateKey, LoggerInterface $logger)
    {
        $this->uriGenerator = $uriGenerator;
        $this->instanceKey = new Key(
            $uriGenerator->fullUrlFor('instance-user-read'),
            $instancePrivateKey
        );
        $this->logger = $logger;
    }

    public function signRequest(RequestInterface $request, ?InternalUser $user): RequestInterface
    {
        $key = null !== $user ? new Key(
            $user->getPrivateKey(),
            $this->uriGenerator->fullUrlFor('user-read', [
                'username' => $user->getUsername(),
            ]) . '#main-key',
        ) : $this->instanceKey;

        if (!$request->hasHeader('Host')) {
            $request = $request->withHeader('Host', $request->getUri()->getHost());
        }

        if (!$request->hasHeader('Date')) {
            $request = $request->withHeader('Date', (new \DateTimeImmutable())->format(\DateTime::RFC7231));
        }

        $request = (new Signer(
            $key,
            Algorithm::create('rsa-sha256'),
            new HeaderList(['(request-target)', 'Host', 'Date', 'Accept'])
        ))->sign($request);

        $this->logger->info('Sign request: ' . $request->getHeaderLine('Signature'));

        return $request;
    }
}
