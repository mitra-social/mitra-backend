<?php

declare(strict_types=1);

namespace Mitra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200605194137 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE media (id VARCHAR(36) NOT NULL, checksum VARCHAR(64) NOT NULL, original_uri VARCHAR(255) NOT NULL, original_uri_hash VARCHAR(64) NOT NULL, localUri VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE actor DROP iconchecksum');
        $this->addSql('ALTER TABLE actor ALTER icon TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE actor ADD CONSTRAINT FK_447556F9659429DB FOREIGN KEY (icon) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_447556F9659429DB ON actor (icon)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE actor DROP CONSTRAINT FK_447556F9659429DB');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP INDEX IDX_447556F9659429DB');
        $this->addSql('ALTER TABLE actor ADD iconchecksum VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE actor ALTER icon TYPE VARCHAR(255)');
    }
}
