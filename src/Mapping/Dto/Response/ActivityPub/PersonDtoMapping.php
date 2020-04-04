<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response\ActivityPub;

use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Mapping\Dto\InvalidEntityException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\Interfaces\RouteCollectorInterface;

final class PersonDtoMapping implements EntityToDtoMappingInterface
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
        return PersonDto::class;
    }

    public static function getEntityClass(): string
    {
        return Person::class;
    }

    /**
     * @param object|InternalUser $entity
     * @param ServerRequestInterface $request
     * @return object|UserResponseDto
     * @throws InvalidEntityException
     */
    public function toDto(object $entity): object
    {
        if (!$entity instanceof Person) {
            throw InvalidEntityException::fromEntity($entity, static::getEntityClass());
        }

        $routeParser = $this->routeCollector->getRouteParser();

        $personDto = new PersonDto();
        $user = $entity->getUser();

        if ($user instanceof InternalUser) {
            $preferredUsername = $user->getUsername();
            $userUrl = $routeParser->fullUrlFor($this->baseUri, 'user-read', [
                'preferredUsername' => $preferredUsername
            ]);

            $personDto->id = $userUrl;
            $personDto->preferredUsername = $preferredUsername;
            $personDto->inbox = $routeParser->fullUrlFor($this->baseUri, 'user-inbox-read', [
                'preferredUsername' => $preferredUsername
            ]);
            $personDto->outbox = $routeParser->fullUrlFor($this->baseUri, 'user-outbox-read', [
                'preferredUsername' => $preferredUsername
            ]);
            $personDto->url = $userUrl;
        } elseif ($user instanceof ExternalUser) {
            $personDto->id = $user->getExternalId();
            $personDto->preferredUsername = $user->getPreferredUsername();
            $personDto->inbox = $user->getInbox();
            $personDto->outbox = $user->getOutbox();
        } else {
            throw new \RuntimeException(sprintf(
                'User `%s` can not be mapped to `%s` dto',
                get_class($user),
                PersonDto::class
            ));
        }

        $personDto->icon = $entity->getIcon();
        $personDto->name = $entity->getName();

        return $personDto;
    }
}
