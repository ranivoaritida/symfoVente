<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240926075523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        //$this->addSql('CREATE TABLE productionMonth (mois VARCHAR(255) NOT NULL, quantite INT NOT NULL, id_site INT NOT NULL, PRIMARY KEY(mois, quantite)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE station (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE site CHANGE coord coord POINT NOT NULL');
        $this->addSql('ALTER TABLE zone CHANGE coord coord POLYGON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        //$this->addSql('DROP TABLE productionMonth');
        $this->addSql('DROP TABLE station');
        $this->addSql('ALTER TABLE site CHANGE coord coord POINT NOT NULL COMMENT \'(DC2Type:points)\'');
        $this->addSql('ALTER TABLE zone CHANGE coord coord POLYGON NOT NULL COMMENT \'(DC2Type:polygons)\'');
    }
}
