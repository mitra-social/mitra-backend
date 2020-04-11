<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration;

use Mitra\ActivityPub\Client\ActivityPubClient;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class TestServiceProvider implements ServiceProviderInterface
{
    /**
     * @var TestCase
     */
    private $testCase;

    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    public function register(Container $container): void
    {
        $container[ActivityPubClient::class] = static function () {
            return $this->testCase->getMockBuilder(ActivityPubClient::class)->getMock();
        };
    }
}
