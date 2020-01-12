<?php

declare(strict_types=1);

namespace Mitra\Config;

use Chubbyphp\Config\ConfigInterface;

final class DevConfig implements ConfigInterface
{

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param string $rootDir
     */
    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        return [
            'env' => $this->getEnv(),
            'debug' => true,
            'rootDir' => $this->rootDir,
            'routerCacheFile' => null,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getDirectories(): array
    {
        $environment = $this->getEnv();

        return [
            'cache' => $this->rootDir . '/var/cache/' . $environment,
            'logs' => $this->rootDir . '/var/logs/' . $environment,
        ];
    }

    public function getEnv(): string
    {
        return 'dev';
    }
}
