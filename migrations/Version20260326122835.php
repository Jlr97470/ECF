<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326122835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_B89478F4CCD7E912 ON adapte');
        $this->addSql('DROP INDEX `primary` ON adapte');
        $this->addSql('ALTER TABLE adapte CHANGE regime_id regime_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adapte ADD PRIMARY KEY (menu_id)');
        $this->addSql('DROP INDEX IDX_F2B62C94FB88E14F ON possede');
        $this->addSql('ALTER TABLE possede CHANGE role_id role_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_2D4B3220CCD7E912 ON proposetheme');
        $this->addSql('ALTER TABLE proposetheme CHANGE theme_id theme_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_D57B8E1EFB88E14F ON publie');
        $this->addSql('ALTER TABLE publie CHANGE avis_id avis_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX `PRIMARY` ON `Adapte`');
        $this->addSql('ALTER TABLE `Adapte` CHANGE regime_id regime_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_B89478F4CCD7E912 ON `Adapte` (menu_id)');
        $this->addSql('ALTER TABLE `Adapte` ADD PRIMARY KEY (menu_id, regime_id)');
        $this->addSql('ALTER TABLE `ProposeTheme` CHANGE theme_id theme_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_2D4B3220CCD7E912 ON `ProposeTheme` (menu_id)');
        $this->addSql('ALTER TABLE `Possede` CHANGE role_id role_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_F2B62C94FB88E14F ON `Possede` (utilisateur_id)');
        $this->addSql('ALTER TABLE `Publie` CHANGE avis_id avis_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_D57B8E1EFB88E14F ON `Publie` (utilisateur_id)');
    }
}
