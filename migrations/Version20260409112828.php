<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260409112828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adapte CHANGE regime_id regime_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adapte RENAME INDEX idx_b89478f435e7d534 TO IDX_BF387DC235E7D534');
        $this->addSql('ALTER TABLE avis CHANGE statut statut VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE commande RENAME INDEX idx_979cc42bfb88e14f TO IDX_6EEAA67DFB88E14F');
        $this->addSql('ALTER TABLE commande RENAME INDEX idx_979cc42bccd7e912 TO IDX_6EEAA67DCCD7E912');
        $this->addSql('ALTER TABLE contient RENAME INDEX idx_25464c00d73db560 TO IDX_DC302E56D73DB560');
        $this->addSql('ALTER TABLE contient RENAME INDEX idx_25464c004646ab2 TO IDX_DC302E564646AB2');
        $this->addSql('ALTER TABLE menu RENAME INDEX uniq_dd3795adff7747b4 TO UNIQ_7D053A93FF7747B4');
        $this->addSql('DROP INDEX IDX_F2B62C94FB88E14F ON possede');
        $this->addSql('ALTER TABLE possede CHANGE role_id role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE possede RENAME INDEX idx_f2b62c94d60322ac TO IDX_3D0B1508D60322AC');
        $this->addSql('ALTER TABLE proposeplat RENAME INDEX idx_cedb8ff3ccd7e912 TO IDX_E875AF1ACCD7E912');
        $this->addSql('ALTER TABLE proposeplat RENAME INDEX idx_cedb8ff3d73db560 TO IDX_E875AF1AD73DB560');
        $this->addSql('DROP INDEX IDX_2D4B3220CCD7E912 ON proposetheme');
        $this->addSql('ALTER TABLE proposetheme CHANGE theme_id theme_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE proposetheme RENAME INDEX idx_2d4b322059027487 TO IDX_F4BBC6DC59027487');
        $this->addSql('DROP INDEX IDX_D57B8E1EFB88E14F ON publie');
        $this->addSql('ALTER TABLE publie CHANGE avis_id avis_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE publie RENAME INDEX idx_d57b8e1e197e709f TO IDX_D2D78B28197E709F');
        $this->addSql('ALTER TABLE role RENAME INDEX uniq_f75b2554a4d60759 TO UNIQ_57698A6AA4D60759');
        $this->addSql('ALTER TABLE utilisateur RENAME INDEX uniq_9b80ec64e7927c74 TO UNIQ_1D1C63B3E7927C74');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `adapte` CHANGE regime_id regime_id INT NOT NULL');
        $this->addSql('ALTER TABLE `adapte` RENAME INDEX idx_bf387dc235e7d534 TO IDX_B89478F435E7D534');
        $this->addSql('ALTER TABLE `contient` RENAME INDEX idx_dc302e564646ab2 TO IDX_25464C004646AB2');
        $this->addSql('ALTER TABLE `contient` RENAME INDEX idx_dc302e56d73db560 TO IDX_25464C00D73DB560');
        $this->addSql('ALTER TABLE `avis` CHANGE statut statut VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE `utilisateur` RENAME INDEX uniq_1d1c63b3e7927c74 TO UNIQ_9B80EC64E7927C74');
        $this->addSql('ALTER TABLE `menu` RENAME INDEX uniq_7d053a93ff7747b4 TO UNIQ_DD3795ADFF7747B4');
        $this->addSql('ALTER TABLE `role` RENAME INDEX uniq_57698a6aa4d60759 TO UNIQ_F75B2554A4D60759');
        $this->addSql('ALTER TABLE `proposetheme` CHANGE theme_id theme_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_2D4B3220CCD7E912 ON `proposetheme` (menu_id)');
        $this->addSql('ALTER TABLE `proposetheme` RENAME INDEX idx_f4bbc6dc59027487 TO IDX_2D4B322059027487');
        $this->addSql('ALTER TABLE `proposeplat` RENAME INDEX idx_e875af1accd7e912 TO IDX_CEDB8FF3CCD7E912');
        $this->addSql('ALTER TABLE `proposeplat` RENAME INDEX idx_e875af1ad73db560 TO IDX_CEDB8FF3D73DB560');
        $this->addSql('ALTER TABLE `possede` CHANGE role_id role_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_F2B62C94FB88E14F ON `possede` (utilisateur_id)');
        $this->addSql('ALTER TABLE `possede` RENAME INDEX idx_3d0b1508d60322ac TO IDX_F2B62C94D60322AC');
        $this->addSql('ALTER TABLE `commande` RENAME INDEX idx_6eeaa67dccd7e912 TO IDX_979CC42BCCD7E912');
        $this->addSql('ALTER TABLE `commande` RENAME INDEX idx_6eeaa67dfb88e14f TO IDX_979CC42BFB88E14F');
        $this->addSql('ALTER TABLE `publie` CHANGE avis_id avis_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_D57B8E1EFB88E14F ON `publie` (utilisateur_id)');
        $this->addSql('ALTER TABLE `publie` RENAME INDEX idx_d2d78b28197e709f TO IDX_D57B8E1E197E709F');
    }
}
