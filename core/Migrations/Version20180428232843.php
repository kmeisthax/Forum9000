<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180428232843 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case "sqlite":
                $this->addSql('CREATE TABLE "group" (id VARCHAR(255) NOT NULL, estate CHAR(36) DEFAULT NULL --(DC2Type:guid)
                , PRIMARY KEY(id))');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_6DC044C58C4A1AAC ON "group" (estate)');
                $this->addSql('CREATE TABLE membership (group_id VARCHAR(255) NOT NULL, member_id VARCHAR(255) NOT NULL, ctime DATETIME DEFAULT NULL, PRIMARY KEY(group_id, member_id))');
                $this->addSql('CREATE INDEX IDX_86FFD285FE54D947 ON membership (group_id)');
                $this->addSql('CREATE INDEX IDX_86FFD2857597D3FE ON membership (member_id)');
                $this->addSql('DROP INDEX UNIQ_852BBECD989D9B62');
                $this->addSql('DROP INDEX IDX_852BBECD727ACA70');
                $this->addSql('CREATE TEMPORARY TABLE __temp__forum AS SELECT id, parent_id, title, description, slug, "order" FROM forum');
                $this->addSql('DROP TABLE forum');
                $this->addSql('CREATE TABLE forum (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
                , parent_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
                , title VARCHAR(255) NOT NULL COLLATE BINARY, description CLOB NOT NULL COLLATE BINARY, slug VARCHAR(255) NOT NULL COLLATE BINARY, "order" INTEGER DEFAULT 0 NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_852BBECDBF396750 FOREIGN KEY (id) REFERENCES estate (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_852BBECD727ACA70 FOREIGN KEY (parent_id) REFERENCES forum (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO forum (id, parent_id, title, description, slug, "order") SELECT id, parent_id, title, description, slug, "order" FROM __temp__forum');
                $this->addSql('DROP TABLE __temp__forum');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_852BBECD989D9B62 ON forum (slug)');
                $this->addSql('CREATE INDEX IDX_852BBECD727ACA70 ON forum (parent_id)');
                $this->addSql('DROP INDEX IDX_C905664CA76ED395');
                $this->addSql('DROP INDEX IDX_C905664C900733ED');
                $this->addSql('CREATE TEMPORARY TABLE __temp__grant AS SELECT estate_id, user_id, attribute, is_granted, is_denied FROM grant');
                $this->addSql('DROP TABLE grant');
                $this->addSql('CREATE TABLE grant (estate_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
                , user_id VARCHAR(255) NOT NULL COLLATE BINARY, attribute VARCHAR(255) NOT NULL COLLATE BINARY, is_granted BOOLEAN NOT NULL, is_denied BOOLEAN NOT NULL, PRIMARY KEY(attribute, estate_id, user_id), CONSTRAINT FK_C905664C900733ED FOREIGN KEY (estate_id) REFERENCES estate (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C905664CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO grant (estate_id, user_id, attribute, is_granted, is_denied) SELECT estate_id, user_id, attribute, is_granted, is_denied FROM __temp__grant');
                $this->addSql('DROP TABLE __temp__grant');
                $this->addSql('CREATE INDEX IDX_C905664CA76ED395 ON grant (user_id)');
                $this->addSql('CREATE INDEX IDX_C905664C900733ED ON grant (estate_id)');
                $this->addSql('DROP INDEX IDX_E04992AA900733ED');
                $this->addSql('CREATE TEMPORARY TABLE __temp__permission AS SELECT estate_id, attribute, is_granted_auth, is_granted_anon, is_denied_auth, is_denied_anon FROM permission');
                $this->addSql('DROP TABLE permission');
                $this->addSql('CREATE TABLE permission (estate_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
                , attribute VARCHAR(255) NOT NULL COLLATE BINARY, is_granted_auth BOOLEAN DEFAULT \'0\' NOT NULL, is_granted_anon BOOLEAN DEFAULT \'0\' NOT NULL, is_denied_auth BOOLEAN DEFAULT \'0\' NOT NULL, is_denied_anon BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(attribute, estate_id), CONSTRAINT FK_E04992AA900733ED FOREIGN KEY (estate_id) REFERENCES estate (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO permission (estate_id, attribute, is_granted_auth, is_granted_anon, is_denied_auth, is_denied_anon) SELECT estate_id, attribute, is_granted_auth, is_granted_anon, is_denied_auth, is_denied_anon FROM __temp__permission');
                $this->addSql('DROP TABLE __temp__permission');
                $this->addSql('CREATE INDEX IDX_E04992AA900733ED ON permission (estate_id)');
                $this->addSql('DROP INDEX IDX_5A8A6C8D5A6D2235');
                $this->addSql('DROP INDEX IDX_5A8A6C8DE2904019');
                $this->addSql('CREATE TEMPORARY TABLE __temp__post AS SELECT id, thread_id, posted_by_id, title, message, et_order, ctime, markup_language FROM post');
                $this->addSql('DROP TABLE post');
                $this->addSql('CREATE TABLE post (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
                , thread_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
                , posted_by_id VARCHAR(255) DEFAULT NULL COLLATE BINARY, title VARCHAR(255) NOT NULL COLLATE BINARY, message CLOB NOT NULL COLLATE BINARY, et_order INTEGER NOT NULL, ctime DATETIME NOT NULL, markup_language VARCHAR(255) DEFAULT \'plaintext\' NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_5A8A6C8DE2904019 FOREIGN KEY (thread_id) REFERENCES thread (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5A8A6C8D5A6D2235 FOREIGN KEY (posted_by_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO post (id, thread_id, posted_by_id, title, message, et_order, ctime, markup_language) SELECT id, thread_id, posted_by_id, title, message, et_order, ctime, markup_language FROM __temp__post');
                $this->addSql('DROP TABLE __temp__post');
                $this->addSql('CREATE INDEX IDX_5A8A6C8D5A6D2235 ON post (posted_by_id)');
                $this->addSql('CREATE INDEX IDX_5A8A6C8DE2904019 ON post (thread_id)');
                $this->addSql('DROP INDEX IDX_31204C8329CCBAD0');
                $this->addSql('CREATE TEMPORARY TABLE __temp__thread AS SELECT id, forum_id, is_locked, "order" FROM thread');
                $this->addSql('DROP TABLE thread');
                $this->addSql('CREATE TABLE thread (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
                , forum_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
                , is_locked BOOLEAN DEFAULT \'0\' NOT NULL, "order" INTEGER DEFAULT 0 NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_31204C8329CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO thread (id, forum_id, is_locked, "order") SELECT id, forum_id, is_locked, "order" FROM __temp__thread');
                $this->addSql('DROP TABLE __temp__thread');
                $this->addSql('CREATE INDEX IDX_31204C8329CCBAD0 ON thread (forum_id)');
                $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, password, email, site_role FROM user');
                $this->addSql('DROP TABLE user');
                $this->addSql('CREATE TABLE user (id VARCHAR(255) NOT NULL COLLATE BINARY, password VARCHAR(255) NOT NULL COLLATE BINARY, email VARCHAR(255) NOT NULL COLLATE BINARY, site_role VARCHAR(255) DEFAULT \'ROLE_USER\' NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_8D93D649BF396750 FOREIGN KEY (id) REFERENCES actor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO user (id, password, email, site_role) SELECT id, password, email, site_role FROM __temp__user');
                $this->addSql('DROP TABLE __temp__user');
                break;
            case "mysql":
                $this->addSql('CREATE TABLE `group` (id VARCHAR(255) NOT NULL, estate CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', UNIQUE INDEX UNIQ_6DC044C58C4A1AAC (estate), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
                $this->addSql('CREATE TABLE membership (group_id VARCHAR(255) NOT NULL, member_id VARCHAR(255) NOT NULL, ctime DATETIME DEFAULT NULL, INDEX IDX_86FFD285FE54D947 (group_id), INDEX IDX_86FFD2857597D3FE (member_id), PRIMARY KEY(group_id, member_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
                $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C58C4A1AAC FOREIGN KEY (estate) REFERENCES estate (id)');
                $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C5BF396750 FOREIGN KEY (id) REFERENCES actor (id) ON DELETE CASCADE');
                $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD285FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
                $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD2857597D3FE FOREIGN KEY (member_id) REFERENCES actor (id)');
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
                $this->addSql('DROP TABLE "group"');
                $this->addSql('DROP TABLE membership');
                $this->addSql('DROP INDEX UNIQ_852BBECD989D9B62');
                $this->addSql('DROP INDEX IDX_852BBECD727ACA70');
                $this->addSql('CREATE TEMPORARY TABLE __temp__forum AS SELECT id, parent_id, title, description, slug, "order" FROM forum');
                $this->addSql('DROP TABLE forum');
                $this->addSql('CREATE TABLE forum (id CHAR(36) NOT NULL --(DC2Type:guid)
                , parent_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
                , title VARCHAR(255) NOT NULL, description CLOB NOT NULL, slug VARCHAR(255) NOT NULL, "order" INTEGER DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
                $this->addSql('INSERT INTO forum (id, parent_id, title, description, slug, "order") SELECT id, parent_id, title, description, slug, "order" FROM __temp__forum');
                $this->addSql('DROP TABLE __temp__forum');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_852BBECD989D9B62 ON forum (slug)');
                $this->addSql('CREATE INDEX IDX_852BBECD727ACA70 ON forum (parent_id)');
                $this->addSql('DROP INDEX IDX_C905664C900733ED');
                $this->addSql('DROP INDEX IDX_C905664CA76ED395');
                $this->addSql('CREATE TEMPORARY TABLE __temp__grant AS SELECT attribute, estate_id, user_id, is_granted, is_denied FROM "grant"');
                $this->addSql('DROP TABLE "grant"');
                $this->addSql('CREATE TABLE "grant" (attribute VARCHAR(255) NOT NULL, estate_id CHAR(36) NOT NULL --(DC2Type:guid)
                , user_id VARCHAR(255) NOT NULL, is_granted BOOLEAN NOT NULL, is_denied BOOLEAN NOT NULL, PRIMARY KEY(attribute, estate_id, user_id))');
                $this->addSql('INSERT INTO "grant" (attribute, estate_id, user_id, is_granted, is_denied) SELECT attribute, estate_id, user_id, is_granted, is_denied FROM __temp__grant');
                $this->addSql('DROP TABLE __temp__grant');
                $this->addSql('CREATE INDEX IDX_C905664C900733ED ON "grant" (estate_id)');
                $this->addSql('CREATE INDEX IDX_C905664CA76ED395 ON "grant" (user_id)');
                $this->addSql('DROP INDEX IDX_E04992AA900733ED');
                $this->addSql('CREATE TEMPORARY TABLE __temp__permission AS SELECT attribute, estate_id, is_granted_auth, is_granted_anon, is_denied_auth, is_denied_anon FROM permission');
                $this->addSql('DROP TABLE permission');
                $this->addSql('CREATE TABLE permission (attribute VARCHAR(255) NOT NULL, estate_id CHAR(36) NOT NULL --(DC2Type:guid)
                , is_granted_auth BOOLEAN DEFAULT \'0\' NOT NULL, is_granted_anon BOOLEAN DEFAULT \'0\' NOT NULL, is_denied_auth BOOLEAN DEFAULT \'0\' NOT NULL, is_denied_anon BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(attribute, estate_id))');
                $this->addSql('INSERT INTO permission (attribute, estate_id, is_granted_auth, is_granted_anon, is_denied_auth, is_denied_anon) SELECT attribute, estate_id, is_granted_auth, is_granted_anon, is_denied_auth, is_denied_anon FROM __temp__permission');
                $this->addSql('DROP TABLE __temp__permission');
                $this->addSql('CREATE INDEX IDX_E04992AA900733ED ON permission (estate_id)');
                $this->addSql('DROP INDEX IDX_5A8A6C8DE2904019');
                $this->addSql('DROP INDEX IDX_5A8A6C8D5A6D2235');
                $this->addSql('CREATE TEMPORARY TABLE __temp__post AS SELECT id, thread_id, posted_by_id, title, message, et_order, ctime, markup_language FROM post');
                $this->addSql('DROP TABLE post');
                $this->addSql('CREATE TABLE post (id CHAR(36) NOT NULL --(DC2Type:guid)
                , thread_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
                , posted_by_id VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, message CLOB NOT NULL, et_order INTEGER NOT NULL, ctime DATETIME NOT NULL, markup_language VARCHAR(255) DEFAULT \'plaintext\' NOT NULL, PRIMARY KEY(id))');
                $this->addSql('INSERT INTO post (id, thread_id, posted_by_id, title, message, et_order, ctime, markup_language) SELECT id, thread_id, posted_by_id, title, message, et_order, ctime, markup_language FROM __temp__post');
                $this->addSql('DROP TABLE __temp__post');
                $this->addSql('CREATE INDEX IDX_5A8A6C8DE2904019 ON post (thread_id)');
                $this->addSql('CREATE INDEX IDX_5A8A6C8D5A6D2235 ON post (posted_by_id)');
                $this->addSql('DROP INDEX IDX_31204C8329CCBAD0');
                $this->addSql('CREATE TEMPORARY TABLE __temp__thread AS SELECT id, forum_id, is_locked, "order" FROM thread');
                $this->addSql('DROP TABLE thread');
                $this->addSql('CREATE TABLE thread (id CHAR(36) NOT NULL --(DC2Type:guid)
                , forum_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
                , is_locked BOOLEAN DEFAULT \'0\' NOT NULL, "order" INTEGER DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
                $this->addSql('INSERT INTO thread (id, forum_id, is_locked, "order") SELECT id, forum_id, is_locked, "order" FROM __temp__thread');
                $this->addSql('DROP TABLE __temp__thread');
                $this->addSql('CREATE INDEX IDX_31204C8329CCBAD0 ON thread (forum_id)');
                $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, password, email, site_role FROM user');
                $this->addSql('DROP TABLE user');
                $this->addSql('CREATE TABLE user (id VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, site_role VARCHAR(255) DEFAULT \'ROLE_USER\' NOT NULL, PRIMARY KEY(id))');
                $this->addSql('INSERT INTO user (id, password, email, site_role) SELECT id, password, email, site_role FROM __temp__user');
                $this->addSql('DROP TABLE __temp__user');
                break;
            case "mysql":
                $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD285FE54D947');
                $this->addSql('DROP TABLE `group`');
                $this->addSql('DROP TABLE membership');
                break;
            default:
                $this->abortIf(true, 'Migration can only be executed safely on \'sqlite\' or \'mysql\'.');
                break;
        }
    }
}
