<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251225003554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE classe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, niveau VARCHAR(50) NOT NULL, annee_scolaire VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE eleve_parent (id INT AUTO_INCREMENT NOT NULL, eleve_id INT DEFAULT NULL, parent_id INT NOT NULL, relation VARCHAR(20) NOT NULL, INDEX IDX_E4475A8EA6CC7B2 (eleve_id), INDEX IDX_E4475A8E727ACA70 (parent_id), UNIQUE INDEX unique_eleve_parent (eleve_id, parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enseignant_matiere_classe (id INT AUTO_INCREMENT NOT NULL, enseignant_id INT NOT NULL, matiere_id INT NOT NULL, classe_id INT NOT NULL, annee_scolaire VARCHAR(20) NOT NULL, INDEX IDX_25155D2E455FCC0 (enseignant_id), INDEX IDX_25155D2F46CD258 (matiere_id), INDEX IDX_25155D28F5EA509 (classe_id), UNIQUE INDEX unique_ens_mat_classe_annee (enseignant_id, matiere_id, classe_id, annee_scolaire), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inscription (id INT AUTO_INCREMENT NOT NULL, eleve_id INT NOT NULL, classe_id INT NOT NULL, annee_scolaire VARCHAR(20) NOT NULL, date_inscription DATETIME NOT NULL, statut VARCHAR(20) NOT NULL, INDEX IDX_5E90F6D6A6CC7B2 (eleve_id), INDEX IDX_5E90F6D68F5EA509 (classe_id), UNIQUE INDEX unique_eleve_annee (eleve_id, annee_scolaire), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE matiere (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(100) NOT NULL, coefficient NUMERIC(3, 1) NOT NULL, nbr_controle INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, eleve_id INT NOT NULL, matiere_id INT NOT NULL, enseignant_id INT NOT NULL, valeur NUMERIC(5, 2) NOT NULL, type VARCHAR(20) NOT NULL, date_note DATETIME NOT NULL, commentaire LONGTEXT DEFAULT NULL, trimestre VARCHAR(1) NOT NULL, annee_scolaire VARCHAR(20) NOT NULL, INDEX IDX_CFBDFA14A6CC7B2 (eleve_id), INDEX IDX_CFBDFA14F46CD258 (matiere_id), INDEX IDX_CFBDFA14E455FCC0 (enseignant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, role VARCHAR(20) NOT NULL, specialite VARCHAR(100) DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, numero_inscription VARCHAR(50) DEFAULT NULL, date_naissance DATE DEFAULT NULL, lieu_naissance VARCHAR(100) DEFAULT NULL, date_creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_modification DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_1D1C63B3112FF919 (numero_inscription), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE eleve_parent ADD CONSTRAINT FK_E4475A8EA6CC7B2 FOREIGN KEY (eleve_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE eleve_parent ADD CONSTRAINT FK_E4475A8E727ACA70 FOREIGN KEY (parent_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE enseignant_matiere_classe ADD CONSTRAINT FK_25155D2E455FCC0 FOREIGN KEY (enseignant_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE enseignant_matiere_classe ADD CONSTRAINT FK_25155D2F46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id)');
        $this->addSql('ALTER TABLE enseignant_matiere_classe ADD CONSTRAINT FK_25155D28F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6A6CC7B2 FOREIGN KEY (eleve_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D68F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14A6CC7B2 FOREIGN KEY (eleve_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14F46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14E455FCC0 FOREIGN KEY (enseignant_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eleve_parent DROP FOREIGN KEY FK_E4475A8EA6CC7B2');
        $this->addSql('ALTER TABLE eleve_parent DROP FOREIGN KEY FK_E4475A8E727ACA70');
        $this->addSql('ALTER TABLE enseignant_matiere_classe DROP FOREIGN KEY FK_25155D2E455FCC0');
        $this->addSql('ALTER TABLE enseignant_matiere_classe DROP FOREIGN KEY FK_25155D2F46CD258');
        $this->addSql('ALTER TABLE enseignant_matiere_classe DROP FOREIGN KEY FK_25155D28F5EA509');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6A6CC7B2');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D68F5EA509');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14A6CC7B2');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14F46CD258');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14E455FCC0');
        $this->addSql('DROP TABLE classe');
        $this->addSql('DROP TABLE eleve_parent');
        $this->addSql('DROP TABLE enseignant_matiere_classe');
        $this->addSql('DROP TABLE inscription');
        $this->addSql('DROP TABLE matiere');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
