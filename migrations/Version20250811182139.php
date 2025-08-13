<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250811182139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_skill (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, skill_id INTEGER NOT NULL, level VARCHAR(255) NOT NULL, notes CLOB DEFAULT NULL, CONSTRAINT FK_BCFF1F2FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BCFF1F2F5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BCFF1F2FA76ED395 ON user_skill (user_id)');
        $this->addSql('CREATE INDEX IDX_BCFF1F2F5585C142 ON user_skill (skill_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_skill_pair ON user_skill (user_id, skill_id)');
        $this->addSql('DROP TABLE skill_user');
        $this->addSql('CREATE TEMPORARY TABLE __temp__skill AS SELECT id, name FROM skill');
        $this->addSql('DROP TABLE skill');
        $this->addSql('CREATE TABLE skill (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO skill (id, name) SELECT id, name FROM __temp__skill');
        $this->addSql('DROP TABLE __temp__skill');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5E3DE4775E237E06 ON skill (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE skill_user (skill_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(skill_id, user_id), CONSTRAINT FK_CAD24AFB5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CAD24AFBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CAD24AFBA76ED395 ON skill_user (user_id)');
        $this->addSql('CREATE INDEX IDX_CAD24AFB5585C142 ON skill_user (skill_id)');
        $this->addSql('DROP TABLE user_skill');
        $this->addSql('CREATE TEMPORARY TABLE __temp__skill AS SELECT id, name FROM skill');
        $this->addSql('DROP TABLE skill');
        $this->addSql('CREATE TABLE skill (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO skill (id, name) SELECT id, name FROM __temp__skill');
        $this->addSql('DROP TABLE __temp__skill');
    }
}
