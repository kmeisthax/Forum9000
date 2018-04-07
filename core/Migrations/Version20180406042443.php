<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180406042443 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case "sqlite":
                $this->addSql('ALTER TABLE user ADD COLUMN site_role VARCHAR(255) DEFAULT \'ROLE_USER\' NOT NULL');
                break;
            case "mysql":
                $this->addSql('ALTER TABLE user ADD site_role VARCHAR(255) DEFAULT \'ROLE_USER\' NOT NULL');
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
                $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, handle, password, email FROM user');
                $this->addSql('DROP TABLE user');
                $this->addSql('CREATE TABLE user (id VARCHAR(255) NOT NULL, handle VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
                $this->addSql('INSERT INTO user (id, handle, password, email) SELECT id, handle, password, email FROM __temp__user');
                $this->addSql('DROP TABLE __temp__user');
                break;
            case "mysql":
                $this->addSql('ALTER TABLE user DROP site_role');
                break;
            default:
                $this->abortIf(true, 'Migration can only be executed safely on \'sqlite\' or \'mysql\'.');
                break;
        }
    }
}
