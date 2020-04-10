<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\ActivityPub;

use Mitra\ActivityPub\Client\ActivityPubClient;
use Mitra\ActivityPub\Client\ActivityPubClientException;
use Mitra\ActivityPub\RemoteObjectResolver;
use Mitra\ActivityPub\Resolver\RemoteObjectResolverException;
use Mitra\CommandBus\Command\ActivityPub\SendObjectToRecipientsCommand;
use Mitra\Dto\Response\ActivityPub\Actor\ActorInterface;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Slim\UriGenerator;
use Psr\Log\LoggerInterface;

final class SendObjectToRecipientsCommandHandler
{
    /**
     * @var ActivityPubClient
     */
    private $activityPubClient;

    /**
     * @var RemoteObjectResolver
     */
    private $remoteObjectResolver;

    /**
     * @var UriGenerator
     */
    private $uriGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ActivityPubClient $activityPubClient,
        RemoteObjectResolver $remoteObjectResolver,
        UriGenerator $uriGenerator,
        LoggerInterface $logger
    ) {
        $this->activityPubClient = $activityPubClient;
        $this->remoteObjectResolver = $remoteObjectResolver;
        $this->uriGenerator = $uriGenerator;
        $this->logger = $logger;
    }

    public function __invoke(SendObjectToRecipientsCommand $command)
    {
        $object = $command->getObject();
        $sender = $command->getSender();

        $senderPublicKeyUrl = $this->uriGenerator->fullUrlFor('user-read', [
            'preferredUsername' => $sender->getUsername(),
        ]) . '#main-key';
        $inboxUrls = $this->getInboxUrls($object);

        try {
            foreach ($inboxUrls as $inboxUrl) {
                $followRequest = $this->activityPubClient->signRequest(
                    $this->activityPubClient->createRequest('POST', $inboxUrl, $object),
                    $sender->getPrivateKey(),
                    $senderPublicKeyUrl
                );

                $response = $this->activityPubClient->sendRequest($followRequest);
                $responseBody = (string) $response->getResponse()->getBody();

                $this->logger->info(sprintf(
                    'Received response from recipient: %d (body: %s)',
                    $response->getResponse()->getStatusCode(),
                    '' !== $responseBody ? $responseBody : '<empty>'
                ));
            }
        } catch (ActivityPubClientException $e) {
            $context = [];

            if (null !== $response = $e->getResponse()) {
                $context['responseBody'] = (string) $response->getBody();
            }

            $this->logger->error(
                sprintf('Could not send to recipient\'s inbox (url: %s): %s', $inboxUrl, $e->getMessage()),
                $context
            );
        }
    }

    /**
     * TODO also check bto, cc, bcc properties of the object
     * @param ObjectDto $object
     * @return array
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

        return $toInboxUrls;
    }

    /**
     * @param mixed $recipient
     * @return string|null
     */
    private function resolveRemoteObjectInboxUrl($recipient): ?string
    {
        try {
            if (null === $object = $this->remoteObjectResolver->resolve($recipient)) {
                return null;
            }

            return $object instanceof ActorInterface ? $object->getInbox() : null;
        } catch (RemoteObjectResolverException $e) {
            $this->logger->notice(sprintf('Could not resolve recipient: %s', $e->getMessage()));
        }
    }
}
