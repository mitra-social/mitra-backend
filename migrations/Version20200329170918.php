<?php

declare(strict_types=1);

namespace Mitra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200329170918 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_external ALTER outbox TYPE VARCHAR(2048)');
        $this->addSql('ALTER TABLE user_external ALTER inbox TYPE VARCHAR(2048)');
        $this->addSql('ALTER TABLE user_external ALTER following TYPE VARCHAR(2048)');
        $this->addSql('ALTER TABLE user_external ALTER followers TYPE VARCHAR(2048)');
        $this->addSql('ALTER TABLE actor ADD name VARCHAR(2048) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_external ALTER outbox TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE user_external ALTER inbox TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE user_external ALTER following TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE user_external ALTER followers TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE actor DROP name');
    }
}
