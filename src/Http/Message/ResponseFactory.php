<?php

declare(strict_types=1);

namespace Mitra\Http\Message;

use Mitra\Dto\ViolationDto;
use Mitra\Dto\ViolationListDto;
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
     * @param PsrResponseFactoryInterface $responseFactory
     * @param EncoderInterface $encoder
     */
    public function __construct(PsrResponseFactoryInterface $responseFactory, EncoderInterface $encoder)
    {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
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
            $violationDto->message = $violation->getMessage();
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
}
