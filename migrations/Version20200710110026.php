<?php

declare(strict_types=1);

namespace Mitra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200710110026 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE activity_stream_content_linked_objects (linked_content_id VARCHAR(36) NOT NULL, parent_content_id VARCHAR(36) NOT NULL, PRIMARY KEY(linked_content_id, parent_content_id))');
        $this->addSql('CREATE INDEX IDX_3C95C8D5136478AA ON activity_stream_content_linked_objects (linked_content_id)');
        $this->addSql('CREATE INDEX IDX_3C95C8D5C3E327BD ON activity_stream_content_linked_objects (parent_content_id)');
        $this->addSql('ALTER TABLE activity_stream_content_linked_objects ADD CONSTRAINT FK_3C95C8D5136478AA FOREIGN KEY (linked_content_id) REFERENCES activity_stream_content (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE activity_stream_content_linked_objects ADD CONSTRAINT FK_3C95C8D5C3E327BD FOREIGN KEY (parent_content_id) REFERENCES activity_stream_content (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE activity_stream_content_linked_objects');
    }
}
