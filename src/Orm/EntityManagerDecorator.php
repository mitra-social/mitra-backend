<?php

declare(strict_types=1);

namespace Mitra\Orm;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Decorator\EntityManagerDecorator as DoctrineEntityManagerDecorator;
use Doctrine\ORM\EntityManager;

class EntityManagerDecorator extends DoctrineEntityManagerDecorator
{

    /**
     * @return void
     */
    public function restoreIfClosed(): void
    {
        if (false === $this->isOpen()) {
            $this->restore();
        }
    }

    /**
     * @return void
     */
    public function reconnectIfNotPinged(): void
    {
        $connection = $this->getConnection();

        if (false === $this->ping($connection)) {
            $connection->close();
            $connection->connect();
        }
    }

    /**
     * @param Connection $connection
     * @return boolean
     */
    private function ping(Connection $connection): bool
    {
        set_error_handler(function (int $errNo, string $errStr) {
            if (0 < ($errNo & (E_WARNING | E_NOTICE))) {
                return true;
            }

            return false;
        });

        $ping = $connection->ping();

        restore_error_handler();

        return $ping;
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\ORMException
     */
    private function restore(): void
    {
        $this->wrapped = EntityManager::create(
            $this->wrapped->getConnection(),
            $this->wrapped->getConfiguration(),
            $this->wrapped->getEventManager()
        );
    }
}