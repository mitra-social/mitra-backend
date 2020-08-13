<?php

declare(strict_types=1);

namespace Mitra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200509075035 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX UNIQUE_EXTERNAL_USER_ID');
        $this->addSql('ALTER TABLE user_external ALTER external_id SET NOT NULL');
        $this->addSql('ALTER TABLE user_external ALTER external_id_hash SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQUE_EXTERNAL_USER_ID ON user_external (external_id_hash, external_id)');
        $this->addSql('ALTER TABLE activity_stream_content ADD external_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE activity_stream_content ADD external_id_hash VARCHAR(64) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQUE_EXTERNAL_CONTENT_ID ON activity_stream_content (external_id_hash, external_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX unique_external_user_id');
        $this->addSql('ALTER TABLE user_external ALTER external_id DROP NOT NULL');
        $this->addSql('ALTER TABLE user_external ALTER external_id_hash DROP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_external_user_id ON user_external (external_id)');
        $this->addSql('DROP INDEX UNIQUE_EXTERNAL_CONTENT_ID');
        $this->addSql('ALTER TABLE activity_stream_content DROP external_id');
        $this->addSql('ALTER TABLE activity_stream_content DROP external_id_hash');
    }
}
