<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\User;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Mapping\Dto\InvalidEntityException;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteCollectorInterface;

final class UserResponseDtoMapping implements EntityToDtoMappingInterface
{
    /**
     * @var RouteCollectorInterface
     */
    private $routeCollector;

    public function __construct(RouteCollectorInterface $routeCollector)
    {
        $this->routeCollector = $routeCollector;
    }

    public static function getDtoClass(): string
    {
        return UserResponseDto::class;
    }

    public static function getEntityClass(): string
    {
        return User::class;
    }

    /**
     * @param object|User $entity
     * @param ServerRequestInterface $request
     * @return object|UserResponseDto
     * @throws InvalidEntityException
     */
    public function toDto(object $entity, ServerRequestInterface $request): object
    {
        if (!$entity instanceof User) {
            throw InvalidEntityException::fromEntity($entity, static::getEntityClass());
        }

        $userResponseDto = new UserResponseDto();

        $userResponseDto->userId = $entity->getId();
        $userResponseDto->email = $entity->getEmail();
        $userResponseDto->registeredAt = $entity->getCreatedAt()->format('c');

        // ActivityPub
        $userResponseDto->preferredUsername = $entity->getPreferredUsername();
        $userResponseDto->inbox = $this->routeCollector->getRouteParser()->fullUrlFor(
            $request->getUri(),
            'user-inbox',
            ['preferredUsername' => $entity->getPreferredUsername()]
        );

        return $userResponseDto;
    }
}
