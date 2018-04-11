<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180411043016 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case "sqlite":
                $this->addSql('CREATE TEMPORARY TABLE __temp__forum AS SELECT id, title, description FROM forum');
                $this->addSql('DROP TABLE forum');
                $this->addSql('CREATE TABLE forum (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
                , parent_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
                , title VARCHAR(255) NOT NULL COLLATE BINARY, description CLOB NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_852BBECD727ACA70 FOREIGN KEY (parent_id) REFERENCES forum (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO forum (id, title, description) SELECT id, title, description FROM __temp__forum');
                $this->addSql('DROP TABLE __temp__forum');
                $this->addSql('CREATE INDEX IDX_852BBECD727ACA70 ON forum (parent_id)');
                $this->addSql('DROP INDEX IDX_C905664CA76ED395');
                $this->addSql('DROP INDEX IDX_C905664C29CCBAD0');
                $this->addSql('CREATE TEMPORARY TABLE __temp__grant AS SELECT attribute, forum_id, user_id, is_granted, is_denied FROM grant');
                $this->addSql('DROP TABLE grant');
                $this->addSql('CREATE TABLE grant (forum_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
                , user_id VARCHAR(255) NOT NULL COLLATE BINARY, attribute VARCHAR(255) NOT NULL, is_granted BOOLEAN NOT NULL, is_denied BOOLEAN NOT NULL, PRIMARY KEY(attribute, forum_id, user_id), CONSTRAINT FK_C905664C29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C905664CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO grant (attribute, forum_id, user_id, is_granted, is_denied) SELECT attribute, forum_id, user_id, is_granted, is_denied FROM __temp__grant');
                $this->addSql('DROP TABLE __temp__grant');
                $this->addSql('CREATE INDEX IDX_C905664CA76ED395 ON grant (user_id)');
                $this->addSql('CREATE INDEX IDX_C905664C29CCBAD0 ON grant (forum_id)');
                $this->addSql('DROP INDEX IDX_E04992AA29CCBAD0');
                $this->addSql('CREATE TEMPORARY TABLE __temp__permission AS SELECT forum_id, attribute, is_granted_auth, is_granted_anon FROM permission');
                $this->addSql('DROP TABLE permission');
                $this->addSql('CREATE TABLE permission (forum_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
                , attribute VARCHAR(255) NOT NULL COLLATE BINARY, is_granted_auth BOOLEAN DEFAULT \'0\' NOT NULL, is_granted_anon BOOLEAN DEFAULT \'0\' NOT NULL, is_denied_auth BOOLEAN DEFAULT \'0\' NOT NULL, is_denied_anon BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(attribute, forum_id), CONSTRAINT FK_E04992AA29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO permission (forum_id, attribute, is_granted_auth, is_granted_anon) SELECT forum_id, attribute, is_granted_auth, is_granted_anon FROM __temp__permission');
                $this->addSql('DROP TABLE __temp__permission');
                $this->addSql('CREATE INDEX IDX_E04992AA29CCBAD0 ON permission (forum_id)');
                $this->addSql('DROP INDEX IDX_5A8A6C8D5A6D2235');
                $this->addSql('DROP INDEX IDX_5A8A6C8DE2904019');
                $this->addSql('CREATE TEMPORARY TABLE __temp__post AS SELECT id, thread_id, posted_by_id, title, message, et_order, ctime FROM post');
                $this->addSql('DROP TABLE post');
                $this->addSql('CREATE TABLE post (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
                , thread_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
                , posted_by_id VARCHAR(255) DEFAULT NULL COLLATE BINARY, title VARCHAR(255) NOT NULL COLLATE BINARY, message CLOB NOT NULL COLLATE BINARY, et_order INTEGER NOT NULL, ctime DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_5A8A6C8DE2904019 FOREIGN KEY (thread_id) REFERENCES thread (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5A8A6C8D5A6D2235 FOREIGN KEY (posted_by_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO post (id, thread_id, posted_by_id, title, message, et_order, ctime) SELECT id, thread_id, posted_by_id, title, message, et_order, ctime FROM __temp__post');
                $this->addSql('DROP TABLE __temp__post');
                $this->addSql('CREATE INDEX IDX_5A8A6C8D5A6D2235 ON post (posted_by_id)');
                $this->addSql('CREATE INDEX IDX_5A8A6C8DE2904019 ON post (thread_id)');
                $this->addSql('DROP INDEX IDX_31204C8329CCBAD0');
                $this->addSql('CREATE TEMPORARY TABLE __temp__thread AS SELECT id, forum_id FROM thread');
                $this->addSql('DROP TABLE thread');
                $this->addSql('CREATE TABLE thread (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
                , forum_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
                , is_locked BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_31204C8329CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO thread (id, forum_id) SELECT id, forum_id FROM __temp__thread');
                $this->addSql('DROP TABLE __temp__thread');
                $this->addSql('CREATE INDEX IDX_31204C8329CCBAD0 ON thread (forum_id)');
                break;
            case "mysql":
                $this->addSql('ALTER TABLE forum ADD parent_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
                $this->addSql('ALTER TABLE forum ADD CONSTRAINT FK_852BBECD727ACA70 FOREIGN KEY (parent_id) REFERENCES forum (id)');
                $this->addSql('CREATE INDEX IDX_852BBECD727ACA70 ON forum (parent_id)');
                $this->addSql('ALTER TABLE permission ADD is_denied_auth TINYINT(1) DEFAULT \'0\' NOT NULL, ADD is_denied_anon TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_granted_auth is_granted_auth TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_granted_anon is_granted_anon TINYINT(1) DEFAULT \'0\' NOT NULL');
                $this->addSql('ALTER TABLE thread ADD is_locked TINYINT(1) DEFAULT \'0\' NOT NULL');
                break;
            default:
                $this->abortIf(true, 'Migration can only be executed safely on \'sqlite\' or \'mysql\'.');
                break;
        }
    }

    public function down(Schema $schema)
    {
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case "sqlite":
                $this->addSql('DROP INDEX IDX_852BBECD727ACA70');
                $this->addSql('CREATE TEMPORARY TABLE __temp__forum AS SELECT id, title, description FROM forum');
                $this->addSql('DROP TABLE forum');
                $this->addSql('CREATE TABLE forum (id CHAR(36) NOT NULL --(DC2Type:guid)
                , title VARCHAR(255) NOT NULL, description CLOB NOT NULL, PRIMARY KEY(id))');
                $this->addSql('INSERT INTO forum (id, title, description) SELECT id, title, description FROM __temp__forum');
                $this->addSql('DROP TABLE __temp__forum');
                $this->addSql('DROP INDEX IDX_C905664C29CCBAD0');
                $this->addSql('DROP INDEX IDX_C905664CA76ED395');
                $this->addSql('CREATE TEMPORARY TABLE __temp__grant AS SELECT attribute, forum_id, user_id, is_granted, is_denied FROM "grant"');
                $this->addSql('DROP TABLE "grant"');
                $this->addSql('CREATE TABLE "grant" (forum_id CHAR(36) NOT NULL --(DC2Type:guid)
                , user_id VARCHAR(255) NOT NULL, attribute CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
                , is_granted BOOLEAN NOT NULL, is_denied BOOLEAN NOT NULL, PRIMARY KEY(attribute, forum_id, user_id))');
                $this->addSql('INSERT INTO "grant" (attribute, forum_id, user_id, is_granted, is_denied) SELECT attribute, forum_id, user_id, is_granted, is_denied FROM __temp__grant');
                $this->addSql('DROP TABLE __temp__grant');
                $this->addSql('CREATE INDEX IDX_C905664C29CCBAD0 ON "grant" (forum_id)');
                $this->addSql('CREATE INDEX IDX_C905664CA76ED395 ON "grant" (user_id)');
                $this->addSql('DROP INDEX IDX_E04992AA29CCBAD0');
                $this->addSql('CREATE TEMPORARY TABLE __temp__permission AS SELECT attribute, forum_id, is_granted_auth, is_granted_anon FROM permission');
                $this->addSql('DROP TABLE permission');
                $this->addSql('CREATE TABLE permission (attribute VARCHAR(255) NOT NULL, forum_id CHAR(36) NOT NULL --(DC2Type:guid)
                , is_granted_auth BOOLEAN NOT NULL, is_granted_anon BOOLEAN NOT NULL, PRIMARY KEY(attribute, forum_id))');
                $this->addSql('INSERT INTO permission (attribute, forum_id, is_granted_auth, is_granted_anon) SELECT attribute, forum_id, is_granted_auth, is_granted_anon FROM __temp__permission');
                $this->addSql('DROP TABLE __temp__permission');
                $this->addSql('CREATE INDEX IDX_E04992AA29CCBAD0 ON permission (forum_id)');
                $this->addSql('DROP INDEX IDX_5A8A6C8DE2904019');
                $this->addSql('DROP INDEX IDX_5A8A6C8D5A6D2235');
                $this->addSql('CREATE TEMPORARY TABLE __temp__post AS SELECT id, thread_id, posted_by_id, title, message, et_order, ctime FROM post');
                $this->addSql('DROP TABLE post');
                $this->addSql('CREATE TABLE post (id CHAR(36) NOT NULL --(DC2Type:guid)
                , thread_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
                , posted_by_id VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, message CLOB NOT NULL, et_order INTEGER NOT NULL, ctime DATETIME NOT NULL, PRIMARY KEY(id))');
                $this->addSql('INSERT INTO post (id, thread_id, posted_by_id, title, message, et_order, ctime) SELECT id, thread_id, posted_by_id, title, message, et_order, ctime FROM __temp__post');
                $this->addSql('DROP TABLE __temp__post');
                $this->addSql('CREATE INDEX IDX_5A8A6C8DE2904019 ON post (thread_id)');
                $this->addSql('CREATE INDEX IDX_5A8A6C8D5A6D2235 ON post (posted_by_id)');
                $this->addSql('DROP INDEX IDX_31204C8329CCBAD0');
                $this->addSql('CREATE TEMPORARY TABLE __temp__thread AS SELECT id, forum_id FROM thread');
                $this->addSql('DROP TABLE thread');
                $this->addSql('CREATE TABLE thread (id CHAR(36) NOT NULL --(DC2Type:guid)
                , forum_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
                , PRIMARY KEY(id))');
                $this->addSql('INSERT INTO thread (id, forum_id) SELECT id, forum_id FROM __temp__thread');
                $this->addSql('DROP TABLE __temp__thread');
                $this->addSql('CREATE INDEX IDX_31204C8329CCBAD0 ON thread (forum_id)');
                break;
            case "mysql":
                $this->addSql('ALTER TABLE forum DROP FOREIGN KEY FK_852BBECD727ACA70');
                $this->addSql('DROP INDEX IDX_852BBECD727ACA70 ON forum');
                $this->addSql('ALTER TABLE forum DROP parent_id');
                $this->addSql('ALTER TABLE permission DROP is_denied_auth, DROP is_denied_anon, CHANGE is_granted_auth is_granted_auth TINYINT(1) NOT NULL, CHANGE is_granted_anon is_granted_anon TINYINT(1) NOT NULL');
                $this->addSql('ALTER TABLE thread DROP is_locked');
                break;
            default:
                $this->abortIf(true, 'Migration can only be executed safely on \'sqlite\' or \'mysql\'.');
                break;
        }
    }
}
