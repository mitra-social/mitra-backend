<?php

declare(strict_types=1);

namespace Mitra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200411092302 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE activity_stream_content DROP CONSTRAINT FK_34A40F3A1FDF9ABB');
        $this->addSql('ALTER TABLE activity_stream_content_assignment DROP CONSTRAINT FK_D08F72B010DAF24A');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D352CD8DC9');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D349A49CDA');
        $this->addSql('ALTER TABLE actor DROP CONSTRAINT fk_447556f9bf396750');
        $this->addSql('ALTER TABLE actor DROP CONSTRAINT actor_pkey');
        $this->addSql('ALTER TABLE actor RENAME COLUMN id TO user_id');
        $this->addSql('ALTER TABLE actor ADD CONSTRAINT FK_447556F9A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE actor ADD PRIMARY KEY (user_id)');
        $this->addSql('ALTER TABLE activity_stream_content ADD CONSTRAINT FK_34A40F3A1FDF9ABB FOREIGN KEY (attributed_to) REFERENCES actor (user_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE activity_stream_content_assignment ADD CONSTRAINT FK_D08F72B010DAF24A FOREIGN KEY (actor_id) REFERENCES actor (user_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D352CD8DC9 FOREIGN KEY (subscribing_actor_id) REFERENCES actor (user_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D349A49CDA FOREIGN KEY (subscribed_actor_id) REFERENCES actor (user_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE activity_stream_content_assignment DROP CONSTRAINT fk_d08f72b010daf24a');
        $this->addSql('ALTER TABLE activity_stream_content_assignment ADD CONSTRAINT fk_d08f72b010daf24a FOREIGN KEY (actor_id) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE activity_stream_content DROP CONSTRAINT fk_34a40f3a1fdf9abb');
        $this->addSql('ALTER TABLE activity_stream_content ADD CONSTRAINT fk_34a40f3a1fdf9abb FOREIGN KEY (attributed_to) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE actor DROP CONSTRAINT FK_447556F9A76ED395');
        $this->addSql('DROP INDEX actor_pkey');
        $this->addSql('ALTER TABLE actor RENAME COLUMN user_id TO id');
        $this->addSql('ALTER TABLE actor ADD CONSTRAINT fk_447556f9bf396750 FOREIGN KEY (id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE actor ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT fk_a3c664d352cd8dc9');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT fk_a3c664d349a49cda');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT fk_a3c664d352cd8dc9 FOREIGN KEY (subscribing_actor_id) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT fk_a3c664d349a49cda FOREIGN KEY (subscribed_actor_id) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
