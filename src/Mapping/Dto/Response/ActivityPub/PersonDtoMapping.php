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
use Mitra\Slim\UriGenerator;
use Psr\Http\Message\ServerRequestInterface;

final class PersonDtoMapping implements EntityToDtoMappingInterface
{
    /**
     * @var UriGenerator
     */
    private $uriGenerator;

    public function __construct(UriGenerator $uriGenerator)
    {
        $this->uriGenerator = $uriGenerator;
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
     * @return object|UserResponseDto
     * @throws InvalidEntityException
     */
    public function toDto(object $entity): object
    {
        if (!$entity instanceof Person) {
            throw InvalidEntityException::fromEntity($entity, static::getEntityClass());
        }

        $personDto = new PersonDto();
        $user = $entity->getUser();

        if ($user instanceof InternalUser) {
            $preferredUsername = $user->getUsername();
            $userUrl = $this->uriGenerator->fullUrlFor('user-read', [
                'username' => $preferredUsername
            ]);

            $personDto->id = $userUrl;
            $personDto->preferredUsername = $preferredUsername;
            $personDto->inbox = $this->uriGenerator->fullUrlFor('user-inbox-read', [
                'username' => $preferredUsername
            ]);
            $personDto->outbox = $this->uriGenerator->fullUrlFor('user-outbox-read', [
                'username' => $preferredUsername
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
