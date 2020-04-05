<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\User\InternalUser;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Mapping\Dto\InvalidEntityException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\Interfaces\RouteCollectorInterface;

final class UserResponseDtoMapping implements EntityToDtoMappingInterface
{
    /**
     * @var RouteCollectorInterface
     */
    private $routeCollector;

    /**
     * @var UriInterface
     */
    private $baseUri;

    public function __construct(RouteCollectorInterface $routeCollector, UriInterface $baseUri)
    {
        $this->routeCollector = $routeCollector;
        $this->baseUri = $baseUri;
    }

    public static function getDtoClass(): string
    {
        return UserResponseDto::class;
    }

    public static function getEntityClass(): string
    {
        return InternalUser::class;
    }

    /**
     * @param object|InternalUser $entity
     * @return object|UserResponseDto
     * @throws InvalidEntityException
     */
    public function toDto(object $entity): object
    {
        if (!$entity instanceof InternalUser) {
            throw InvalidEntityException::fromEntity($entity, static::getEntityClass());
        }

        $userResponseDto = new UserResponseDto();

        $userResponseDto->userId = $entity->getId();
        $userResponseDto->email = $entity->getEmail();
        $userResponseDto->registeredAt = $entity->getCreatedAt()->format('c');

        $userUrl = $this->routeCollector->getRouteParser()->fullUrlFor(
            $this->baseUri,
            'user-read',
            ['preferredUsername' => $entity->getUsername()]
        );

        // ActivityPub
        $userResponseDto->id = $userUrl;
        $userResponseDto->preferredUsername = $entity->getUsername();
        $userResponseDto->inbox = $this->routeCollector->getRouteParser()->fullUrlFor(
            $this->baseUri,
            'user-inbox-read',
            ['preferredUsername' => $entity->getUsername()]
        );
        $userResponseDto->outbox = $this->routeCollector->getRouteParser()->fullUrlFor(
            $this->baseUri,
            'user-outbox-read',
            ['preferredUsername' => $entity->getUsername()]
        );
        $userResponseDto->url = $userUrl;
        $userResponseDto->publicKey = [
            'id' => $userUrl . '#main-key',
            'owner' =>  $userResponseDto->url,
            'publicKeyPem' => $entity->getPublicKey(),
        ];

        return $userResponseDto;
    }
}
