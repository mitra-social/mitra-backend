<?php

declare(strict_types=1);

namespace Mitra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200608062508 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX index_original_uri_hash');
        $this->addSql('DROP INDEX index_checksum');
        $this->addSql('ALTER TABLE media ADD mime_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE media ADD size INT NOT NULL');
        $this->addSql('ALTER TABLE media RENAME COLUMN localuri TO local_uri');
        $this->addSql('CREATE UNIQUE INDEX UNIQUE_ORIGINAL_URI ON media (original_uri_hash, original_uri)');
        $this->addSql('CREATE UNIQUE INDEX UNIQUE_LOCAL_URI ON media (local_uri)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQUE_ORIGINAL_URI');
        $this->addSql('DROP INDEX UNIQUE_LOCAL_URI');
        $this->addSql('ALTER TABLE media ADD localuri VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE media DROP local_uri');
        $this->addSql('ALTER TABLE media DROP mime_type');
        $this->addSql('ALTER TABLE media DROP size');
        $this->addSql('CREATE INDEX index_original_uri_hash ON media (original_uri_hash)');
        $this->addSql('CREATE INDEX index_checksum ON media (checksum)');
    }
}
