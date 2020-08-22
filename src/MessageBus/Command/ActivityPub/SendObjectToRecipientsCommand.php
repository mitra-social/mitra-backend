<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Command\ActivityPub;

use Doctrine\Common\Util\Debug;
use Mitra\MessageBus\CommandInterface;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\User\InternalUser;

/**
 * Analyzes the objects, bto to, cc, bcc normalizes the list and sends out the object
 */
final class SendObjectToRecipientsCommand implements CommandInterface
{
    /**
     * @var ObjectDto
     */
    private $object;

    /**
     * @var InternalUser
     */
    private $sender;

    public function __construct(InternalUser $sender, ObjectDto $object)
    {
        $this->sender = $sender;
        $this->object = $object;
    }

    /**
     * @return ObjectDto
     */
    public function getObject(): ObjectDto
    {
        return $this->object;
    }

    /**
     * @return InternalUser
     */
    public function getSender(): InternalUser
    {
        return $this->sender;
    }
}
