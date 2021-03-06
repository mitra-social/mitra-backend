<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Command\ActivityPub;

use Mitra\ActivityPub\Client\ActivityPubClientException;
use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\ActivityPub\RequestSignerInterface;
use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\ActivityPub\Resolver\RemoteObjectResolverException;
use Mitra\MessageBus\Command\ActivityPub\SendObjectToRecipientsCommand;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Slim\UriGeneratorInterface;
use Psr\Log\LoggerInterface;

final class SendObjectToRecipientsCommandHandler
{
    /**
     * @var ActivityPubClientInterface
     */
    private $activityPubClient;

    /**
     * @var RequestSignerInterface
     */
    private $requestSigner;

    /**
     * @var ExternalUserResolver
     */
    private $externalUserResolver;

    /**
     * @var UriGeneratorInterface
     */
    private $uriGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ActivityPubClientInterface $activityPubClient,
        RequestSignerInterface $requestSigner,
        ExternalUserResolver $externalUserResolver,
        UriGeneratorInterface $uriGenerator,
        LoggerInterface $logger
    ) {
        $this->activityPubClient = $activityPubClient;
        $this->requestSigner = $requestSigner;
        $this->externalUserResolver = $externalUserResolver;
        $this->uriGenerator = $uriGenerator;
        $this->logger = $logger;
    }

    public function __invoke(SendObjectToRecipientsCommand $command): void
    {
        $object = $command->getObject();
        $sender = $command->getSender();

        $inboxUrls = $this->getInboxUrls($object);

        foreach ($inboxUrls as $inboxUrl) {
            try {
                $request = $this->requestSigner->signRequest(
                    $this->activityPubClient->createRequest('POST', $inboxUrl, $object),
                    $sender
                );

                $response = $this->activityPubClient->sendRequest($request);
                $responseBody = (string) $response->getHttpResponse()->getBody();

                $this->logger->info(sprintf(
                    'Received response from recipient: %d (body: %s)',
                    $response->getHttpResponse()->getStatusCode(),
                    '' !== $responseBody ? $responseBody : '<empty>'
                ));
            } catch (ActivityPubClientException $e) {
                $context = [];

                if (null !== $response = $e->getResponse()) {
                    $context['responseBody'] = (string) $response->getBody();
                }

                $this->logger->error(
                    sprintf('Could not send to recipient\'s inbox (url: %s): %s', $inboxUrl, $e->getMessage()),
                    $context
                );

                throw $e;
            }
        }
    }

    /**
     * TODO also check bto, cc, bcc properties of the object
     * @param ObjectDto $object
     * @return array<string>
     * @throws \Mitra\ActivityPub\Resolver\RemoteObjectResolverException
     */
    private function getInboxUrls(ObjectDto $object): array
    {
        $toInboxUrls = [];

        if (is_string($object->to)) {
            $toInboxUrls[] = $this->resolveRemoteObjectInboxUrl($object->to);
        } elseif (is_array($object->to)) {
            foreach ($object->to as $to) {
                $toInboxUrls[] = $this->resolveRemoteObjectInboxUrl($to);
            }
        }

        return array_filter($toInboxUrls);
    }

    /**
     * @param mixed $recipient
     * @return string|null
     */
    private function resolveRemoteObjectInboxUrl($recipient): ?string
    {
        try {
            if (null === $object = $this->externalUserResolver->resolve($recipient)) {
                return null;
            }

            return $object->getInbox();
        } catch (RemoteObjectResolverException $e) {
            $this->logger->notice(sprintf('Could not resolve recipient: %s', $e->getMessage()));
        }

        return null;
    }
}
