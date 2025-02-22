<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220142541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE incomes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, amount NUMERIC(13, 3) NOT NULL, source VARCHAR(255) NOT NULL, date DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT UNSIGNED DEFAULT NULL, INDEX IDX_9DE2B5BDA76ED395 (user_id), PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE incomes ADD CONSTRAINT FK_9DE2B5BDA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE incomes DROP FOREIGN KEY FK_9DE2B5BDA76ED395');
        $this->addSql('DROP TABLE incomes');
    }
}
