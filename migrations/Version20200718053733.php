<?php

declare(strict_types=1);

namespace Mitra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200718053733 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE "user" ADD user_id VARCHAR(36) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649A76ED395 ON "user" (user_id)');
        //$this->addSql('DROP INDEX "primary"');
        $this->addSql('ALTER TABLE actor ADD id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE actor DROP CONSTRAINT actor_pkey CASCADE');
        $this->addSql('ALTER TABLE actor ALTER user_id DROP NOT NULL');
        $this->addSql('CREATE INDEX IDX_447556F9A76ED395 ON actor (user_id)');
        $this->addSql('ALTER TABLE actor ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649A76ED395 FOREIGN KEY (user_id) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE activity_stream_content ADD CONSTRAINT FK_34A40F3A1FDF9ABB FOREIGN KEY (attributed_to) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE activity_stream_content_assignment ADD CONSTRAINT FK_D08F72B010DAF24A FOREIGN KEY (actor_id) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D352CD8DC9 FOREIGN KEY (subscribing_actor_id) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D349A49CDA FOREIGN KEY (subscribed_actor_id) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE activity_stream_content_assignment DROP CONSTRAINT fk_d08f72b010daf24a');
        $this->addSql('ALTER TABLE activity_stream_content_assignment ADD CONSTRAINT fk_d08f72b010daf24a FOREIGN KEY (actor_id) REFERENCES actor (user_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649A76ED395');
        $this->addSql('DROP INDEX UNIQ_8D93D649A76ED395');
        $this->addSql('ALTER TABLE "user" DROP user_id');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT fk_a3c664d352cd8dc9');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT fk_a3c664d349a49cda');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT fk_a3c664d352cd8dc9 FOREIGN KEY (subscribing_actor_id) REFERENCES actor (user_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT fk_a3c664d349a49cda FOREIGN KEY (subscribed_actor_id) REFERENCES actor (user_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE activity_stream_content DROP CONSTRAINT fk_34a40f3a1fdf9abb');
        $this->addSql('ALTER TABLE activity_stream_content ADD CONSTRAINT fk_34a40f3a1fdf9abb FOREIGN KEY (attributed_to) REFERENCES actor (user_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX IDX_447556F9A76ED395');
        $this->addSql('DROP INDEX actor_pkey');
        $this->addSql('ALTER TABLE actor DROP id');
        $this->addSql('ALTER TABLE actor ALTER user_id SET NOT NULL');
        $this->addSql('ALTER TABLE actor ADD PRIMARY KEY (user_id)');
    }
}
