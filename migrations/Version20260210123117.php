<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210123117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `Adapte` (menu_id INT NOT NULL, regime_id INT NOT NULL, INDEX IDX_B89478F4CCD7E912 (menu_id), INDEX IDX_B89478F435E7D534 (regime_id), PRIMARY KEY (menu_id, regime_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Allergene` (allergene_id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) NOT NULL, PRIMARY KEY (allergene_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Avis` (avis_id INT NOT NULL, note VARCHAR(50) NOT NULL, description VARCHAR(50) NOT NULL, statut VARCHAR(50) NOT NULL, PRIMARY KEY (avis_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Commande` (numero_commande VARCHAR(50) NOT NULL, date_commande DATE NOT NULL, date_prestation DATE NOT NULL, heure_livraison VARCHAR(50) NOT NULL, prix_menu DOUBLE PRECISION NOT NULL, nombre_personne INT NOT NULL, prix_livraison DOUBLE PRECISION NOT NULL, statut VARCHAR(50) NOT NULL, pret_materiel TINYINT NOT NULL, restition_materiel TINYINT NOT NULL, utilisateur_id INT DEFAULT NULL, menu_id INT DEFAULT NULL, INDEX IDX_979CC42BFB88E14F (utilisateur_id), INDEX IDX_979CC42BCCD7E912 (menu_id), PRIMARY KEY (numero_commande)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Contient` (plat_id INT NOT NULL, allergene_id INT NOT NULL, INDEX IDX_25464C00D73DB560 (plat_id), INDEX IDX_25464C004646AB2 (allergene_id), PRIMARY KEY (plat_id, allergene_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Horaire` (horaire_id INT AUTO_INCREMENT NOT NULL, jour VARCHAR(50) NOT NULL, heure_ouverture VARCHAR(50) NOT NULL, heure_fermeture VARCHAR(50) NOT NULL, PRIMARY KEY (horaire_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Menu` (menu_id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(50) NOT NULL, nombre_personne_minimum INT NOT NULL, prix_par_personne DOUBLE PRECISION NOT NULL, regime VARCHAR(50) NOT NULL, description VARCHAR(50) NOT NULL, quantite_restante INT NOT NULL, UNIQUE INDEX UNIQ_DD3795ADFF7747B4 (titre), PRIMARY KEY (menu_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Plat` (plat_id INT AUTO_INCREMENT NOT NULL, titre_plat VARCHAR(50) NOT NULL, photo LONGBLOB NOT NULL, PRIMARY KEY (plat_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Possede` (utilisateur_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_F2B62C94FB88E14F (utilisateur_id), INDEX IDX_F2B62C94D60322AC (role_id), PRIMARY KEY (utilisateur_id, role_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `ProposePlat` (menu_id INT NOT NULL, plat_id INT NOT NULL, INDEX IDX_CEDB8FF3CCD7E912 (menu_id), INDEX IDX_CEDB8FF3D73DB560 (plat_id), PRIMARY KEY (menu_id, plat_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `ProposeTheme` (menu_id INT NOT NULL, theme_id INT NOT NULL, INDEX IDX_2D4B3220CCD7E912 (menu_id), INDEX IDX_2D4B322059027487 (theme_id), PRIMARY KEY (menu_id, theme_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Publie` (utilisateur_id INT NOT NULL, avis_id INT NOT NULL, INDEX IDX_D57B8E1EFB88E14F (utilisateur_id), INDEX IDX_D57B8E1E197E709F (avis_id), PRIMARY KEY (utilisateur_id, avis_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Regime` (regime_id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) NOT NULL, PRIMARY KEY (regime_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Role` (role_id INT NOT NULL, libelle VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_F75B2554A4D60759 (libelle), PRIMARY KEY (role_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Theme` (theme_id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) NOT NULL, PRIMARY KEY (theme_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `Utilisateur` (utilisateur_id INT AUTO_INCREMENT NOT NULL, email VARCHAR(50) NOT NULL, password VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, nom VARCHAR(50) NOT NULL, telephone VARCHAR(50) NOT NULL, ville VARCHAR(50) NOT NULL, pays VARCHAR(50) NOT NULL, adresse_postal VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_9B80EC64E7927C74 (email), PRIMARY KEY (utilisateur_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE `Adapte` ADD CONSTRAINT FK_B89478F4CCD7E912 FOREIGN KEY (menu_id) REFERENCES `Menu` (menu_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `Adapte` ADD CONSTRAINT FK_B89478F435E7D534 FOREIGN KEY (regime_id) REFERENCES `Regime` (regime_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `Commande` ADD CONSTRAINT FK_979CC42BFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES `Utilisateur` (utilisateur_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `Commande` ADD CONSTRAINT FK_979CC42BCCD7E912 FOREIGN KEY (menu_id) REFERENCES `Menu` (menu_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `Contient` ADD CONSTRAINT FK_25464C00D73DB560 FOREIGN KEY (plat_id) REFERENCES `Plat` (plat_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `Contient` ADD CONSTRAINT FK_25464C004646AB2 FOREIGN KEY (allergene_id) REFERENCES `Allergene` (allergene_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `Possede` ADD CONSTRAINT FK_F2B62C94FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES `Utilisateur` (utilisateur_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `Possede` ADD CONSTRAINT FK_F2B62C94D60322AC FOREIGN KEY (role_id) REFERENCES `Role` (role_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `ProposePlat` ADD CONSTRAINT FK_CEDB8FF3CCD7E912 FOREIGN KEY (menu_id) REFERENCES `Menu` (menu_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `ProposePlat` ADD CONSTRAINT FK_CEDB8FF3D73DB560 FOREIGN KEY (plat_id) REFERENCES `Plat` (plat_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `ProposeTheme` ADD CONSTRAINT FK_2D4B3220CCD7E912 FOREIGN KEY (menu_id) REFERENCES `Menu` (menu_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `ProposeTheme` ADD CONSTRAINT FK_2D4B322059027487 FOREIGN KEY (theme_id) REFERENCES `Theme` (theme_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `Publie` ADD CONSTRAINT FK_D57B8E1EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES `Utilisateur` (utilisateur_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `Publie` ADD CONSTRAINT FK_D57B8E1E197E709F FOREIGN KEY (avis_id) REFERENCES `Avis` (avis_id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `Adapte` DROP FOREIGN KEY FK_B89478F4CCD7E912');
        $this->addSql('ALTER TABLE `Adapte` DROP FOREIGN KEY FK_B89478F435E7D534');
        $this->addSql('ALTER TABLE `Commande` DROP FOREIGN KEY FK_979CC42BFB88E14F');
        $this->addSql('ALTER TABLE `Commande` DROP FOREIGN KEY FK_979CC42BCCD7E912');
        $this->addSql('ALTER TABLE `Contient` DROP FOREIGN KEY FK_25464C00D73DB560');
        $this->addSql('ALTER TABLE `Contient` DROP FOREIGN KEY FK_25464C004646AB2');
        $this->addSql('ALTER TABLE `Possede` DROP FOREIGN KEY FK_F2B62C94FB88E14F');
        $this->addSql('ALTER TABLE `Possede` DROP FOREIGN KEY FK_F2B62C94D60322AC');
        $this->addSql('ALTER TABLE `ProposePlat` DROP FOREIGN KEY FK_CEDB8FF3CCD7E912');
        $this->addSql('ALTER TABLE `ProposePlat` DROP FOREIGN KEY FK_CEDB8FF3D73DB560');
        $this->addSql('ALTER TABLE `ProposeTheme` DROP FOREIGN KEY FK_2D4B3220CCD7E912');
        $this->addSql('ALTER TABLE `ProposeTheme` DROP FOREIGN KEY FK_2D4B322059027487');
        $this->addSql('ALTER TABLE `Publie` DROP FOREIGN KEY FK_D57B8E1EFB88E14F');
        $this->addSql('ALTER TABLE `Publie` DROP FOREIGN KEY FK_D57B8E1E197E709F');
        $this->addSql('DROP TABLE `Adapte`');
        $this->addSql('DROP TABLE `Allergene`');
        $this->addSql('DROP TABLE `Avis`');
        $this->addSql('DROP TABLE `Commande`');
        $this->addSql('DROP TABLE `Contient`');
        $this->addSql('DROP TABLE `Horaire`');
        $this->addSql('DROP TABLE `Menu`');
        $this->addSql('DROP TABLE `Plat`');
        $this->addSql('DROP TABLE `Possede`');
        $this->addSql('DROP TABLE `ProposePlat`');
        $this->addSql('DROP TABLE `ProposeTheme`');
        $this->addSql('DROP TABLE `Publie`');
        $this->addSql('DROP TABLE `Regime`');
        $this->addSql('DROP TABLE `Role`');
        $this->addSql('DROP TABLE `Theme`');
        $this->addSql('DROP TABLE `Utilisateur`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
