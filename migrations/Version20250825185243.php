<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250825185243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__exchange_request AS SELECT id, requester_id, target_offer_id, status, created_at FROM exchange_request');
        $this->addSql('DROP TABLE exchange_request');
        $this->addSql('CREATE TABLE exchange_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, requester_id INTEGER NOT NULL, offer_id INTEGER NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , message VARCHAR(1000) DEFAULT NULL, updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_7C5D591EED442CF4 FOREIGN KEY (requester_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7C5D591E53C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO exchange_request (id, requester_id, offer_id, status, created_at) SELECT id, requester_id, target_offer_id, status, created_at FROM __temp__exchange_request');
        $this->addSql('DROP TABLE __temp__exchange_request');
        $this->addSql('CREATE INDEX IDX_7C5D591EED442CF4 ON exchange_request (requester_id)');
        $this->addSql('CREATE INDEX IDX_7C5D591E53C674EE ON exchange_request (offer_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_pending_request_per_user_offer ON exchange_request (requester_id, offer_id, status)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__review AS SELECT id, author_id, target_user_id, exchange_id, rating, comment, created_at FROM review');
        $this->addSql('DROP TABLE review');
        $this->addSql('CREATE TABLE review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, subject_user_id INTEGER NOT NULL, offer_id INTEGER DEFAULT NULL, exchange_request_id INTEGER DEFAULT NULL, rating SMALLINT NOT NULL, comment VARCHAR(2000) DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_794381C6F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_794381C62EC7F37 FOREIGN KEY (subject_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_794381C653C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_794381C6E5A8062 FOREIGN KEY (exchange_request_id) REFERENCES exchange_request (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO review (id, author_id, subject_user_id, offer_id, rating, comment, created_at) SELECT id, author_id, target_user_id, exchange_id, rating, comment, created_at FROM __temp__review');
        $this->addSql('DROP TABLE __temp__review');
        $this->addSql('CREATE INDEX IDX_794381C6F675F31B ON review (author_id)');
        $this->addSql('CREATE INDEX IDX_794381C62EC7F37 ON review (subject_user_id)');
        $this->addSql('CREATE INDEX IDX_794381C653C674EE ON review (offer_id)');
        $this->addSql('CREATE INDEX IDX_794381C6E5A8062 ON review (exchange_request_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_author_subject_exchange ON review (author_id, subject_user_id, exchange_request_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__exchange_request AS SELECT id, requester_id, offer_id, status, created_at FROM exchange_request');
        $this->addSql('DROP TABLE exchange_request');
        $this->addSql('CREATE TABLE exchange_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, requester_id INTEGER NOT NULL, target_offer_id INTEGER NOT NULL, counter_offer_id INTEGER DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, CONSTRAINT FK_7C5D591EED442CF4 FOREIGN KEY (requester_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7C5D591E896DA487 FOREIGN KEY (target_offer_id) REFERENCES offer (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7C5D591E1F210FEC FOREIGN KEY (counter_offer_id) REFERENCES offer (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO exchange_request (id, requester_id, target_offer_id, status, created_at) SELECT id, requester_id, offer_id, status, created_at FROM __temp__exchange_request');
        $this->addSql('DROP TABLE __temp__exchange_request');
        $this->addSql('CREATE INDEX IDX_7C5D591EED442CF4 ON exchange_request (requester_id)');
        $this->addSql('CREATE INDEX IDX_7C5D591E1F210FEC ON exchange_request (counter_offer_id)');
        $this->addSql('CREATE INDEX IDX_7C5D591E896DA487 ON exchange_request (target_offer_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__review AS SELECT id, author_id, subject_user_id, rating, comment, created_at FROM review');
        $this->addSql('DROP TABLE review');
        $this->addSql('CREATE TABLE review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, target_user_id INTEGER NOT NULL, exchange_id INTEGER DEFAULT NULL, rating INTEGER NOT NULL, comment CLOB DEFAULT NULL, created_at DATETIME NOT NULL, CONSTRAINT FK_794381C6F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_794381C66C066AFE FOREIGN KEY (target_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_794381C668AFD1A0 FOREIGN KEY (exchange_id) REFERENCES exchange_request (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO review (id, author_id, target_user_id, rating, comment, created_at) SELECT id, author_id, subject_user_id, rating, comment, created_at FROM __temp__review');
        $this->addSql('DROP TABLE __temp__review');
        $this->addSql('CREATE INDEX IDX_794381C6F675F31B ON review (author_id)');
        $this->addSql('CREATE INDEX IDX_794381C668AFD1A0 ON review (exchange_id)');
        $this->addSql('CREATE INDEX IDX_794381C66C066AFE ON review (target_user_id)');
    }
}
