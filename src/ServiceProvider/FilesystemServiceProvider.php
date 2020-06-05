<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Aws\S3\S3Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class FilesystemServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container[FilesystemInterface::class] = static function (Container $container): FilesystemInterface {
            $adapterType = $container['filesystem']['adapter']['type'];

            if ('local' === $adapterType) {
                $adapter = new Local($container['filesystem']['adapter']['config']['root']);
            } elseif ('s3' === $adapterType) {
                $client = new S3Client([
                    'credentials' => [
                        'key'    => $container['filesystem']['adapter']['config']['credentials']['key'],
                        'secret' => $container['filesystem']['adapter']['config']['credentials']['secret'],
                    ],
                    'region' => $container['filesystem']['adapter']['config']['region'],
                    'version' => $container['filesystem']['adapter']['config']['version'],
                ]);
                $adapter = new AwsS3Adapter($client, $container['filesystem']['adapter']['config']['bucket']);
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Filesystem adapter type `%s` is not supported.',
                    $adapterType
                ));
            }

            return new Filesystem($adapter);
        };
    }
}
