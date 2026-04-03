<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227062142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur CHANGE prenom prenom VARCHAR(50) DEFAULT NULL, CHANGE nom nom VARCHAR(50) DEFAULT NULL, CHANGE telephone telephone VARCHAR(50) DEFAULT NULL, CHANGE ville ville VARCHAR(50) DEFAULT NULL, CHANGE pays pays VARCHAR(50) DEFAULT NULL, CHANGE adresse_postal adresse_postal VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `Utilisateur` CHANGE prenom prenom VARCHAR(50) NOT NULL, CHANGE nom nom VARCHAR(50) NOT NULL, CHANGE telephone telephone VARCHAR(50) NOT NULL, CHANGE ville ville VARCHAR(50) NOT NULL, CHANGE pays pays VARCHAR(50) NOT NULL, CHANGE adresse_postal adresse_postal VARCHAR(50) NOT NULL');
    }
}
