<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Client;

use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Psr\Http\Message\ResponseInterface;

final class ActivityPubClientResponse
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var ObjectDto|null
     */
    private $receivedObject;

    /**
     * ActivityPubClientResponse constructor.
     * @param ResponseInterface $response
     * @param ObjectDto|null $receivedObject
     */
    public function __construct(ResponseInterface $response, ?ObjectDto $receivedObject)
    {
        $this->response = $response;
        $this->receivedObject = $receivedObject;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return ObjectDto|null
     */
    public function getReceivedObject(): ?ObjectDto
    {
        return $this->receivedObject;
    }
}
