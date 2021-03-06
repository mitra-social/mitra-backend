<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Dto\DataToDtoTransformer;
use Mitra\Dto\DataToDtoPopulator;
use Mitra\Dto\DtoToEntityMapper;
use Mitra\Dto\EntityToDtoMapper;
use Mitra\Dto\Populator\ActivityPubDtoPopulator;
use Mitra\Dto\Request\CreateUserRequestDto;
use Mitra\Dto\Request\TokenRequestDto;
use Mitra\Dto\Request\UpdateUserRequestDto;
use Mitra\Dto\RequestToDtoTransformer;
use Mitra\Dto\Response\ActivityStreams\Activity\AcceptDto;
use Mitra\Dto\Response\ActivityStreams\Activity\CreateDto;
use Mitra\Dto\Response\ActivityStreams\Activity\DeleteDto;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Dto\Response\ActivityStreams\Activity\UndoDto;
use Mitra\Dto\Response\ActivityStreams\ArticleDto;
use Mitra\Dto\Response\ActivityStreams\AudioDto;
use Mitra\Dto\Response\ActivityStreams\DocumentDto;
use Mitra\Dto\Response\ActivityStreams\EventDto;
use Mitra\Dto\Response\ActivityStreams\ImageDto;
use Mitra\Dto\Response\ActivityStreams\NoteDto;
use Mitra\Dto\Response\ActivityStreams\VideoDto;
use Mitra\Mapping\Dto\Request\UpdateUserRequestDtoMapping;
use Mitra\Mapping\Dto\Request\CreateUserRequestDtoMapping;
use Mitra\Mapping\Dto\Response\ApiProblemDtoMapping;
use Mitra\Mapping\Dto\Response\BadRequestApiProblemDtoMapping;
use Mitra\Mapping\Dto\Response\ForbiddenApiProblemDtoMapping;
use Mitra\Mapping\Dto\Response\NotFoundApiProblemDtoMapping;
use Mitra\Mapping\Dto\Response\OrganizationDtoMapping;
use Mitra\Mapping\Dto\Response\PersonDtoMapping;
use Mitra\Mapping\Dto\Response\UserResponseDtoMapping;
use Mitra\Mapping\Dto\Response\ViolationListDtoMapping;
use Mitra\Mapping\Dto\Response\ViolationDtoMapping;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Slim\UriGeneratorInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

final class DtoServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $this->registerDataToDtoPopulators($container);
        $this->registerDtoToEntityMappings($container);
        $this->registerEntityToDtoMappings($container);

        $container[DtoToEntityMapper::class] = static function (Container $container): DtoToEntityMapper {
            return new DtoToEntityMapper($container[ContainerInterface::class], [
                CreateUserRequestDtoMapping::class,
                UpdateUserRequestDtoMapping::class,
            ]);
        };

        $container[EntityToDtoMapper::class] = static function (Container $container): EntityToDtoMapper {
            return new EntityToDtoMapper($container[ContainerInterface::class], [
                PersonDtoMapping::class,
                OrganizationDtoMapping::class,
                UserResponseDtoMapping::class,
                ViolationListDtoMapping::class,
                ViolationDtoMapping::class,
                ApiProblemDtoMapping::class,
                BadRequestApiProblemDtoMapping::class,
                NotFoundApiProblemDtoMapping::class,
                ForbiddenApiProblemDtoMapping::class,
            ]);
        };

        $container[ActivityPubDtoPopulator::class] = static function (): ActivityPubDtoPopulator {
            return new ActivityPubDtoPopulator();
        };

        $container[RequestToDtoTransformer::class] = static function (Container $container): RequestToDtoTransformer {
            return new RequestToDtoTransformer(
                $container[DataToDtoTransformer::class],
                $container[DecoderInterface::class]
            );
        };
    }

    private function registerDataToDtoPopulators(Container $container): void
    {
        $container[DataToDtoPopulator::class . CreateUserRequestDto::class] = static function (): DataToDtoPopulator {
            return new DataToDtoPopulator(CreateUserRequestDto::class);
        };

        $container[DataToDtoPopulator::class . TokenRequestDto::class] = static function (): DataToDtoPopulator {
            return new DataToDtoPopulator(TokenRequestDto::class);
        };

        $dtoClassMap = [
            CreateUserRequestDto::class => DataToDtoPopulator::class . CreateUserRequestDto::class,
            UpdateUserRequestDto::class => DataToDtoPopulator::class . UpdateUserRequestDto::class,

            TokenRequestDto::class => DataToDtoPopulator::class . TokenRequestDto::class,
            ArticleDto::class => DataToDtoPopulator::class . ArticleDto::class,
            DocumentDto::class => DataToDtoPopulator::class . DocumentDto::class,
            AudioDto::class => DataToDtoPopulator::class . AudioDto::class,
            ImageDto::class => DataToDtoPopulator::class . ImageDto::class,
            VideoDto::class => DataToDtoPopulator::class . VideoDto::class,
            NoteDto::class => DataToDtoPopulator::class . NoteDto::class,
            EventDto::class => DataToDtoPopulator::class . EventDto::class,

            CreateDto::class => DataToDtoPopulator::class . CreateDto::class,
            FollowDto::class => DataToDtoPopulator::class . FollowDto::class,
            UndoDto::class => DataToDtoPopulator::class . UndoDto::class,
            DeleteDto::class => DataToDtoPopulator::class . DeleteDto::class,
            AcceptDto::class => DataToDtoPopulator::class . AcceptDto::class,
        ];

        foreach ($dtoClassMap as $dtoClass => $serviceId) {
            $container[$serviceId] = static function () use ($dtoClass): DataToDtoPopulator {
                return new DataToDtoPopulator($dtoClass);
            };
        }

        $container[DataToDtoTransformer::class] = static function (
            Container $container
        ) use ($dtoClassMap): DataToDtoTransformer {
            return new DataToDtoTransformer($container[ContainerInterface::class], $dtoClassMap);
        };
    }

    private function registerDtoToEntityMappings(Container $container): void
    {
        $container[CreateUserRequestDtoMapping::class] = static function (): CreateUserRequestDtoMapping {
            return new CreateUserRequestDtoMapping();
        };

        $container[UpdateUserRequestDtoMapping::class] = static function (): UpdateUserRequestDtoMapping {
            return new UpdateUserRequestDtoMapping();
        };
    }

    private function registerEntityToDtoMappings(Container $container): void
    {
        $container[ViolationDtoMapping::class] = static function (): ViolationDtoMapping {
            return new ViolationDtoMapping();
        };

        $container[ViolationListDtoMapping::class] = static function (Container $container): ViolationListDtoMapping {
            return new ViolationListDtoMapping($container[ViolationDtoMapping::class]);
        };

        $container[UserResponseDtoMapping::class] = static function (Container $container): UserResponseDtoMapping {
            return new UserResponseDtoMapping($container[UriGeneratorInterface::class]);
        };

        $container[PersonDtoMapping::class] = static function (): PersonDtoMapping {
            return new PersonDtoMapping();
        };

        $container[ApiProblemDtoMapping::class] = static function (): ApiProblemDtoMapping {
            return new ApiProblemDtoMapping();
        };

        $container[BadRequestApiProblemDtoMapping::class] = static function (): BadRequestApiProblemDtoMapping {
            return new BadRequestApiProblemDtoMapping();
        };

        $container[ForbiddenApiProblemDtoMapping::class] = static function (): ForbiddenApiProblemDtoMapping {
            return new ForbiddenApiProblemDtoMapping();
        };

        $container[NotFoundApiProblemDtoMapping::class] = static function (): NotFoundApiProblemDtoMapping {
            return new NotFoundApiProblemDtoMapping();
        };
    }
}
