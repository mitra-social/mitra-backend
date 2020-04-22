<?php

declare(strict_types=1);

namespace Mitra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200410224116 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE subscription (id VARCHAR(36) NOT NULL, subscribing_actor VARCHAR(36) NOT NULL, subscribed_actor VARCHAR(36) NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A3C664D3A6B45179 ON subscription (subscribing_actor)');
        $this->addSql('CREATE INDEX IDX_A3C664D37BDB7DEA ON subscription (subscribed_actor)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3A6B45179 FOREIGN KEY (subscribing_actor) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D37BDB7DEA FOREIGN KEY (subscribed_actor) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE subscription');
    }
}
