<?php

declare(strict_types=1);

namespace Mitra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200411071812 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT fk_a3c664d3a6b45179');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT fk_a3c664d37bdb7dea');
        $this->addSql('DROP INDEX idx_a3c664d3a6b45179');
        $this->addSql('DROP INDEX idx_a3c664d37bdb7dea');
        $this->addSql('ALTER TABLE subscription ADD subscribing_actor_id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE subscription ADD subscribed_actor_id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE subscription DROP subscribing_actor');
        $this->addSql('ALTER TABLE subscription DROP subscribed_actor');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D352CD8DC9 FOREIGN KEY (subscribing_actor_id) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D349A49CDA FOREIGN KEY (subscribed_actor_id) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A3C664D352CD8DC9 ON subscription (subscribing_actor_id)');
        $this->addSql('CREATE INDEX IDX_A3C664D349A49CDA ON subscription (subscribed_actor_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D352CD8DC9');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D349A49CDA');
        $this->addSql('DROP INDEX IDX_A3C664D352CD8DC9');
        $this->addSql('DROP INDEX IDX_A3C664D349A49CDA');
        $this->addSql('ALTER TABLE subscription ADD subscribing_actor VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE subscription ADD subscribed_actor VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE subscription DROP subscribing_actor_id');
        $this->addSql('ALTER TABLE subscription DROP subscribed_actor_id');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT fk_a3c664d3a6b45179 FOREIGN KEY (subscribing_actor) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT fk_a3c664d37bdb7dea FOREIGN KEY (subscribed_actor) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_a3c664d3a6b45179 ON subscription (subscribing_actor)');
        $this->addSql('CREATE INDEX idx_a3c664d37bdb7dea ON subscription (subscribed_actor)');
    }
}
