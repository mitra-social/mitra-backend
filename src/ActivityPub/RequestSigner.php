<?php

declare(strict_types=1);

namespace Mitra\ActivityPub;

use HttpSignatures\Algorithm;
use HttpSignatures\HeaderList;
use HttpSignatures\Key;
use HttpSignatures\KeyException;
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

    /**
     * @var array<string>
     */
    private $headersToSign;

    /**
     * RequestSigner constructor.
     * @param UriGenerator $uriGenerator
     * @param string $instancePrivateKey
     * @param LoggerInterface $logger
     * @param array<string> $headersToSign
     */
    public function __construct(
        UriGenerator $uriGenerator,
        string $instancePrivateKey,
        LoggerInterface $logger,
        array $headersToSign
    ) {
        $this->uriGenerator = $uriGenerator;
        $this->logger = $logger;
        $this->headersToSign = array_merge(['(request-target)'], $headersToSign);

        try {
            $this->instanceKey = new Key(
                $uriGenerator->fullUrlFor('instance-user-read'),
                $instancePrivateKey
            );
        } catch (KeyException $e) {
            throw new \RuntimeException('Could not create instance key', 0, $e);
        }
    }

    public function signRequest(RequestInterface $request, ?InternalUser $user): RequestInterface
    {
        try {
            $key = null !== $user ? new Key(
                $this->uriGenerator->fullUrlFor('user-read', [
                    'username' => $user->getUsername(),
                ]) . '#main-key',
                $user->getPrivateKey()
            ) : $this->instanceKey;
        } catch (KeyException $e) {
            throw new \RuntimeException('Could not create user key', 0, $e);
        }

        if (in_array('Host', $this->headersToSign, true) && !$request->hasHeader('Host')) {
            $request = $request->withHeader('Host', $request->getUri()->getHost());
        }

        if (in_array('Date', $this->headersToSign, true) && !$request->hasHeader('Date')) {
            $request = $request->withHeader('Date', (new \DateTimeImmutable())->format(\DateTime::RFC7231));
        }

        $request = (new Signer(
            $key,
            Algorithm::create('rsa-sha256'),
            new HeaderList($this->headersToSign)
        ))->sign($request);

        $this->logger->info('Sign request: ' . $request->getHeaderLine('Signature'));

        return $request;
    }
}
