<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250804200740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exchange_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, requester_id INTEGER NOT NULL, target_offer_id INTEGER NOT NULL, counter_offer_id INTEGER DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, CONSTRAINT FK_7C5D591EED442CF4 FOREIGN KEY (requester_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7C5D591E896DA487 FOREIGN KEY (target_offer_id) REFERENCES offer (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7C5D591E1F210FEC FOREIGN KEY (counter_offer_id) REFERENCES offer (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7C5D591EED442CF4 ON exchange_request (requester_id)');
        $this->addSql('CREATE INDEX IDX_7C5D591E896DA487 ON exchange_request (target_offer_id)');
        $this->addSql('CREATE INDEX IDX_7C5D591E1F210FEC ON exchange_request (counter_offer_id)');
        $this->addSql('CREATE TABLE offer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id INTEGER NOT NULL, skill_offered_id INTEGER NOT NULL, skill_requested_id INTEGER NOT NULL, title VARCHAR(100) NOT NULL, description CLOB NOT NULL, status VARCHAR(50) NOT NULL, CONSTRAINT FK_29D6873E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_29D6873ECE0F128 FOREIGN KEY (skill_offered_id) REFERENCES skill (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_29D6873EA5AB4B5 FOREIGN KEY (skill_requested_id) REFERENCES skill (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_29D6873E7E3C61F9 ON offer (owner_id)');
        $this->addSql('CREATE INDEX IDX_29D6873ECE0F128 ON offer (skill_offered_id)');
        $this->addSql('CREATE INDEX IDX_29D6873EA5AB4B5 ON offer (skill_requested_id)');
        $this->addSql('CREATE TABLE review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, target_user_id INTEGER NOT NULL, exchange_id INTEGER DEFAULT NULL, rating INTEGER NOT NULL, comment CLOB DEFAULT NULL, created_at DATETIME NOT NULL, CONSTRAINT FK_794381C6F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_794381C66C066AFE FOREIGN KEY (target_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_794381C668AFD1A0 FOREIGN KEY (exchange_id) REFERENCES exchange_request (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_794381C6F675F31B ON review (author_id)');
        $this->addSql('CREATE INDEX IDX_794381C66C066AFE ON review (target_user_id)');
        $this->addSql('CREATE INDEX IDX_794381C668AFD1A0 ON review (exchange_id)');
        $this->addSql('CREATE TABLE skill (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE skill_user (skill_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(skill_id, user_id), CONSTRAINT FK_CAD24AFB5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CAD24AFBA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CAD24AFB5585C142 ON skill_user (skill_id)');
        $this->addSql('CREATE INDEX IDX_CAD24AFBA76ED395 ON skill_user (user_id)');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, username VARCHAR(100) NOT NULL, bio CLOB DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE exchange_request');
        $this->addSql('DROP TABLE offer');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE skill_user');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
