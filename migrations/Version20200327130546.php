<?php

declare(strict_types=1);

namespace Mitra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200327130546 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE activity_stream_content_assignment (id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, content_id VARCHAR(36) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D08F72B0A76ED395 ON activity_stream_content_assignment (user_id)');
        $this->addSql('CREATE INDEX IDX_D08F72B084A0A3ED ON activity_stream_content_assignment (content_id)');
        $this->addSql('ALTER TABLE activity_stream_content_assignment ADD CONSTRAINT FK_D08F72B0A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE activity_stream_content_assignment ADD CONSTRAINT FK_D08F72B084A0A3ED FOREIGN KEY (content_id) REFERENCES activity_stream_content (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE activity_stream_content_assignment');
    }
}
