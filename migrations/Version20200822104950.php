<?php

declare(strict_types=1);

namespace Mitra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200822104950 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE activity_stream_content_assignment DROP CONSTRAINT FK_D08F72B084A0A3ED');
        $this->addSql('ALTER TABLE activity_stream_content_assignment ADD CONSTRAINT FK_D08F72B084A0A3ED FOREIGN KEY (content_id) REFERENCES activity_stream_content (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE activity_stream_content_assignment DROP CONSTRAINT fk_d08f72b084a0a3ed');
        $this->addSql('ALTER TABLE activity_stream_content_assignment ADD CONSTRAINT fk_d08f72b084a0a3ed FOREIGN KEY (content_id) REFERENCES activity_stream_content (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
