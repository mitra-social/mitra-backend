<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command\ActivityPub;

/**
 * Validates received activitypub content from remote server:
 *  - is any subscriber on this server interested in it
 *  - does the content look malicious or spam-like, etc
 */
final class ValidateContentCommand
{

}
