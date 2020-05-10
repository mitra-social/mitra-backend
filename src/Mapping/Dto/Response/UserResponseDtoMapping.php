<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\User\InternalUser;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Mapping\Dto\InvalidEntityException;
use Mitra\Slim\UriGenerator;

final class UserResponseDtoMapping implements EntityToDtoMappingInterface
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

        $userResponseDto->context = [
            'https://www.w3.org/ns/activitystreams',
            'https://w3id.org/security/v1'
        ];
        /*$userResponseDto->userId = $entity->getId();
        $userResponseDto->email = $entity->getEmail();
        $userResponseDto->registeredAt = $entity->getCreatedAt()->format('c');*/

        $userUrl = $this->uriGenerator->fullUrlFor(
            'user-read',
            ['username' => $entity->getUsername()]
        );

        // ActivityPub
        $userResponseDto->id = $userUrl;
        $userResponseDto->preferredUsername = $entity->getUsername();
        $userResponseDto->inbox = $this->uriGenerator->fullUrlFor(
            'user-inbox-read',
            ['username' => $entity->getUsername()]
        );
        $userResponseDto->outbox = $this->uriGenerator->fullUrlFor(
            'user-outbox-read',
            ['username' => $entity->getUsername()]
        );
        $userResponseDto->following = $this->uriGenerator->fullUrlFor(
            'user-following',
            ['username' => $entity->getUsername()]
        );
        $userResponseDto->url = $userUrl;

        if (null !== $publicKey = $entity->getPublicKey()) {
            $userResponseDto->publicKey = [
                'id' => $userUrl . '#main-key',
                'owner' =>  $userResponseDto->url,
                'publicKeyPem' => $publicKey,
            ];
        }

        return $userResponseDto;
    }
}
