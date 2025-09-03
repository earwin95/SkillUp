<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903172912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__conversation AS SELECT id, offer_id, owner_id, participant_id, created_at, updated_at, owner_last_seen_at, participant_last_seen_at FROM conversation');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('CREATE TABLE conversation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, offer_id INTEGER NOT NULL, owner_id INTEGER NOT NULL, participant_id INTEGER NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL --(DC2Type:datetime_immutable)
        , owner_last_seen_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , participant_last_seen_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_8A8E26E953C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8A8E26E97E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8A8E26E99D1C3019 FOREIGN KEY (participant_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO conversation (id, offer_id, owner_id, participant_id, created_at, updated_at, owner_last_seen_at, participant_last_seen_at) SELECT id, offer_id, owner_id, participant_id, created_at, updated_at, owner_last_seen_at, participant_last_seen_at FROM __temp__conversation');
        $this->addSql('DROP TABLE __temp__conversation');
        $this->addSql('CREATE UNIQUE INDEX uniq_conversation_offer_participant ON conversation (offer_id, participant_id)');
        $this->addSql('CREATE INDEX IDX_8A8E26E99D1C3019 ON conversation (participant_id)');
        $this->addSql('CREATE INDEX IDX_8A8E26E97E3C61F9 ON conversation (owner_id)');
        $this->addSql('CREATE INDEX IDX_8A8E26E953C674EE ON conversation (offer_id)');
        $this->addSql('ALTER TABLE message ADD COLUMN reactions CLOB DEFAULT \'[]\' NOT NULL');
        $this->addSql('ALTER TABLE message ADD COLUMN edited_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD COLUMN deleted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__conversation AS SELECT id, offer_id, owner_id, participant_id, created_at, updated_at, owner_last_seen_at, participant_last_seen_at FROM conversation');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('CREATE TABLE conversation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, offer_id INTEGER NOT NULL, owner_id INTEGER NOT NULL, participant_id INTEGER NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL --(DC2Type:datetime_immutable)
        , owner_last_seen_at DATETIME DEFAULT NULL, participant_last_seen_at DATETIME DEFAULT NULL, CONSTRAINT FK_8A8E26E953C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8A8E26E97E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8A8E26E99D1C3019 FOREIGN KEY (participant_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO conversation (id, offer_id, owner_id, participant_id, created_at, updated_at, owner_last_seen_at, participant_last_seen_at) SELECT id, offer_id, owner_id, participant_id, created_at, updated_at, owner_last_seen_at, participant_last_seen_at FROM __temp__conversation');
        $this->addSql('DROP TABLE __temp__conversation');
        $this->addSql('CREATE INDEX IDX_8A8E26E953C674EE ON conversation (offer_id)');
        $this->addSql('CREATE INDEX IDX_8A8E26E97E3C61F9 ON conversation (owner_id)');
        $this->addSql('CREATE INDEX IDX_8A8E26E99D1C3019 ON conversation (participant_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_conversation_offer_participant ON conversation (offer_id, participant_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__message AS SELECT id, conversation_id, author_id, content, created_at FROM message');
        $this->addSql('DROP TABLE message');
        $this->addSql('CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, conversation_id INTEGER NOT NULL, author_id INTEGER NOT NULL, content CLOB NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B6BD307FF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO message (id, conversation_id, author_id, content, created_at) SELECT id, conversation_id, author_id, content, created_at FROM __temp__message');
        $this->addSql('DROP TABLE __temp__message');
        $this->addSql('CREATE INDEX IDX_B6BD307F9AC0396 ON message (conversation_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF675F31B ON message (author_id)');
    }
}
