<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Mapping\Dto\InvalidEntityException;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteCollectorInterface;

final class PersonDtoMapping implements EntityToDtoMappingInterface
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
    public function toDto(object $entity, ServerRequestInterface $request): object
    {
        if (!$entity instanceof Person) {
            throw InvalidEntityException::fromEntity($entity, static::getEntityClass());
        }

        $routeParser = $this->routeCollector->getRouteParser();
        $uri = $request->getUri();

        $personDto = new PersonDto();
        $source = $entity->getSource();

        if ($source instanceof InternalUser) {
            $preferredUsername = $source->getUsername();
            $personDto->id = $routeParser->fullUrlFor($uri, 'user-read', ['preferredUsername' => $preferredUsername]);
            $personDto->preferredUsername = $preferredUsername;
            $personDto->inbox = $routeParser->fullUrlFor($uri, 'user-inbox', [
                'preferredUsername' => $preferredUsername
            ]);
            $personDto->outbox = $routeParser->fullUrlFor($uri, 'user-inbox', [
                'preferredUsername' => $preferredUsername
            ]);
        } elseif ($source instanceof ExternalUser) {
            $personDto->id = $source->getExternalId();
            $personDto->preferredUsername = $source->getPreferredUsername();
            $personDto->inbox = $source->getInbox();
            $personDto->outbox = $source->getOutbox();
        } else {
            throw new \RuntimeException(sprintf(
                'User `%s` can not be mapped to `%s` dto',
                get_class($source),
                PersonDto::class
            ));
        }

        $personDto->icon = $entity->getIcon();
        $personDto->name = $entity->getName();

        return $personDto;
    }
}
