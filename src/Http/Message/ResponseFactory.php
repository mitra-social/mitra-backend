<?php

declare(strict_types=1);

namespace Mitra\Http\Message;

use Mitra\Dto\EntityToDtoManager;
use Mitra\Dto\Response\UserResponseDto;
use Mitra\Dto\Response\ViolationDto;
use Mitra\Dto\Response\ViolationListDto;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Validator\ViolationInterface;
use Mitra\Validator\ViolationListInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface as PsrResponseFactoryInterface;

final class ResponseFactory implements ResponseFactoryInterface, PsrResponseFactoryInterface
{

    /**
     * @var PsrResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var EntityToDtoManager
     */
    private $entityToDtoManager;

    /**
     * @param PsrResponseFactoryInterface $responseFactory
     * @param EncoderInterface $encoder
     * @param EntityToDtoManager $entityToDtoManager
     */
    public function __construct(
        PsrResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        EntityToDtoManager $entityToDtoManager
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->entityToDtoManager = $entityToDtoManager;
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return ResponseInterface
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->responseFactory->createResponse($code, $reasonPhrase);
    }

    /**
     * @param ViolationListInterface $violationList
     * @param string $mimeType
     * @return ResponseInterface
     * @throws \Mitra\Serialization\Encode\EncoderException
     */
    public function createResponseFromViolationList(
        ViolationListInterface $violationList,
        string $mimeType
    ): ResponseInterface {
        $violationListDto = new ViolationListDto();

        foreach ($violationList as $violation) {
            /** @var ViolationInterface $violation */
            $violationDto = new ViolationDto();
            $violationDto->message = (string) $violation->getMessage();
            $violationDto->messageTemplate = $violation->getMessageTemplate();
            $violationDto->propertyPath = $violation->getPropertyPath();
            $violationDto->invalidValue = $violation->getInvalidValue();
            $violationDto->code = $violation->getCode();

            $violationListDto->violations[] = $violationDto;
        }

        $response = $this->responseFactory->createResponse(400)->withHeader('Content-Type', $mimeType);

        $response->getBody()->write($this->encoder->encode($violationListDto, $mimeType));

        return $response;
    }

    public function createResponseFromEntity(object $entity, string $dtoClass, string $mimeType, int $code = 200)
    {
        $dto = $this->entityToDtoManager->populate($dtoClass, $entity);
        $response = $this->responseFactory->createResponse($code);

        $response->getBody()->write($this->encoder->encode($dto, $mimeType));

        return $response;
    }
}
