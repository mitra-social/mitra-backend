<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\User\InternalUser;
use Mitra\Mapping\Dto\EntityToDtoMappingContext;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Mapping\Dto\InvalidEntityException;
use Mitra\Slim\UriGeneratorInterface;

final class UserResponseDtoMapping implements EntityToDtoMappingInterface
{
    /**
     * @var UriGeneratorInterface
     */
    private $uriGenerator;

    public function __construct(UriGeneratorInterface $uriGenerator)
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
     * @param EntityToDtoMappingContext $context
     * @return object|UserResponseDto
     * @throws InvalidEntityException
     */
    public function toDto(object $entity, EntityToDtoMappingContext $context): object
    {
        if (!$entity instanceof InternalUser) {
            throw InvalidEntityException::fromEntity($entity, static::getEntityClass());
        }

        $userResponseDto = new UserResponseDto();

        $userResponseDto->internalUserId = $entity->getId();
        $userResponseDto->published = $entity->getCreatedAt()->format('c');

        if (null !== $entity->getUpdatedAt()) {
            $userResponseDto->updated = $entity->getUpdatedAt()->format('c');
        }

        if ($this->isResourceOwner($entity, $context)) {
            $userResponseDto->context[2]['email'] = 'mitra:email';
            $userResponseDto->email = $entity->getEmail();
        }

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
                'owner' =>  $userResponseDto->id,
                'publicKeyPem' => $publicKey,
            ];
        }

        return $userResponseDto;
    }

    /**
     * @param object|InternalUser $entity
     * @param EntityToDtoMappingContext $context
     * @return bool
     */
    private function isResourceOwner(object $entity, EntityToDtoMappingContext $context): bool
    {
        if (null === $requestContext = $context->getRequest()) {
            return false;
        }

        if (null === $authenticatedUser = $requestContext->getAttribute('authenticatedUser')) {
            return false;
        }

        /** @var InternalUser $authenticatedUser */

        return $authenticatedUser->getId() === $entity->getId();
    }
}
