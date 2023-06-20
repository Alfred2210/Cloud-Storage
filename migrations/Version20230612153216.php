<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230612153216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }


    public function up(Schema $schema): void

    {

        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('INSERT INTO plan (nom, capacite, prix) VALUES ("Basic", 20, 20)');

        $this->addSql('INSERT INTO plan (nom, capacite, prix) VALUES ("Standard", 50, 50)');

        $this->addSql('INSERT INTO plan (nom, capacite, prix) VALUES ("Premium", 100, 100)');
    }

    public function down(Schema $schema): void

    {

        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('DROP TABLE plan');

        $this->addSql('DROP TABLE user');

        $this->addSql('DROP TABLE messenger_messages');
    }
}
