<?php

declare(strict_types=1);

namespace Mitra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200329163446 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE user_external (id VARCHAR(36) NOT NULL, external_id VARCHAR(255) DEFAULT NULL, external_id_hash VARCHAR(64) DEFAULT NULL, preferred_username VARCHAR(255) DEFAULT NULL, outbox VARCHAR(255) NOT NULL, inbox VARCHAR(255) NOT NULL, following VARCHAR(255) NOT NULL, followers VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE user_internal (id VARCHAR(36) NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB7EF903F85E0677 ON user_internal (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB7EF903E7927C74 ON user_internal (email)');
        $this->addSql('CREATE TABLE actor (id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, icon VARCHAR(255) DEFAULT NULL, type VARCHAR(12) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_447556F9A76ED395 ON actor (user_id)');
        $this->addSql('ALTER TABLE user_external ADD CONSTRAINT FK_B8DF45B8BF396750 FOREIGN KEY (id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_internal ADD CONSTRAINT FK_EB7EF903BF396750 FOREIGN KEY (id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE actor ADD CONSTRAINT FK_447556F9A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX uniq_8d93d649e7927c74');
        $this->addSql('DROP INDEX uniq_8d93d649df93af83');
        $this->addSql('ALTER TABLE "user" ADD type VARCHAR(12) NOT NULL');
        $this->addSql('ALTER TABLE "user" DROP preferredusername');
        $this->addSql('ALTER TABLE "user" DROP email');
        $this->addSql('ALTER TABLE "user" DROP created_at');
        $this->addSql('ALTER TABLE "user" DROP updated_at');
        $this->addSql('ALTER TABLE "user" DROP password');
        $this->addSql('ALTER TABLE activity_stream_content ADD attributed_to VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE activity_stream_content ADD CONSTRAINT FK_34A40F3A1FDF9ABB FOREIGN KEY (attributed_to) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34A40F3A1FDF9ABB ON activity_stream_content (attributed_to)');
        $this->addSql('ALTER TABLE activity_stream_content_assignment DROP CONSTRAINT fk_d08f72b0a76ed395');
        $this->addSql('DROP INDEX idx_d08f72b0a76ed395');
        $this->addSql('ALTER TABLE activity_stream_content_assignment RENAME COLUMN user_id TO actor_id');
        $this->addSql('ALTER TABLE activity_stream_content_assignment ADD CONSTRAINT FK_D08F72B010DAF24A FOREIGN KEY (actor_id) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D08F72B010DAF24A ON activity_stream_content_assignment (actor_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE activity_stream_content DROP CONSTRAINT FK_34A40F3A1FDF9ABB');
        $this->addSql('ALTER TABLE activity_stream_content_assignment DROP CONSTRAINT FK_D08F72B010DAF24A');
        $this->addSql('DROP TABLE user_external');
        $this->addSql('DROP TABLE user_internal');
        $this->addSql('DROP TABLE actor');
        $this->addSql('ALTER TABLE "user" ADD preferredusername VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD password VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE "user" DROP type');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649e7927c74 ON "user" (email)');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649df93af83 ON "user" (preferredusername)');
        $this->addSql('DROP INDEX IDX_D08F72B010DAF24A');
        $this->addSql('ALTER TABLE activity_stream_content_assignment RENAME COLUMN actor_id TO user_id');
        $this->addSql('ALTER TABLE activity_stream_content_assignment ADD CONSTRAINT fk_d08f72b0a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d08f72b0a76ed395 ON activity_stream_content_assignment (user_id)');
        $this->addSql('DROP INDEX UNIQ_34A40F3A1FDF9ABB');
        $this->addSql('ALTER TABLE activity_stream_content DROP attributed_to');
    }
}
