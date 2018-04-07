<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180403013000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case "sqlite":
                $this->addSql('DROP INDEX IDX_5A8A6C8D5A6D2235');
                $this->addSql('DROP INDEX IDX_5A8A6C8DE2904019');
                $this->addSql('CREATE TEMPORARY TABLE __temp__post AS SELECT id, thread_id, posted_by_id, title, message, et_order FROM post');
                $this->addSql('DROP TABLE post');
                $this->addSql('CREATE TABLE post (id CHAR(36) NOT NULL --(DC2Type:guid)
                , posted_by_id VARCHAR(255) DEFAULT NULL COLLATE BINARY, thread_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
                , title VARCHAR(255) NOT NULL COLLATE BINARY, message CLOB NOT NULL COLLATE BINARY, et_order INTEGER NOT NULL, ctime DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_5A8A6C8DE2904019 FOREIGN KEY (thread_id) REFERENCES thread (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5A8A6C8D5A6D2235 FOREIGN KEY (posted_by_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO post (id, thread_id, posted_by_id, title, message, et_order) SELECT id, thread_id, posted_by_id, title, message, et_order FROM __temp__post');
                $this->addSql('DROP TABLE __temp__post');
                $this->addSql('CREATE INDEX IDX_5A8A6C8D5A6D2235 ON post (posted_by_id)');
                $this->addSql('CREATE INDEX IDX_5A8A6C8DE2904019 ON post (thread_id)');
                $this->addSql('CREATE TEMPORARY TABLE __temp__thread AS SELECT id FROM thread');
                $this->addSql('DROP TABLE thread');
                $this->addSql('CREATE TABLE thread (id CHAR(36) NOT NULL --(DC2Type:guid)
                , PRIMARY KEY(id))');
                $this->addSql('INSERT INTO thread (id) SELECT id FROM __temp__thread');
                $this->addSql('DROP TABLE __temp__thread');
                break;
            case "mysql":
                $this->addSql('ALTER TABLE post ADD ctime DATETIME NOT NULL');
                break;
            default:
                $this->abort('Migration can only be executed safely on \'sqlite\' or \'mysql\'.');
                break;
        }
    }

    public function down(Schema $schema)
    {
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case "sqlite":
                $this->addSql('DROP INDEX IDX_5A8A6C8DE2904019');
                $this->addSql('DROP INDEX IDX_5A8A6C8D5A6D2235');
                $this->addSql('CREATE TEMPORARY TABLE __temp__post AS SELECT id, thread_id, posted_by_id, title, message, et_order FROM post');
                $this->addSql('DROP TABLE post');
                $this->addSql('CREATE TABLE post (id CHAR(36) NOT NULL COLLATE BINARY --
        (DC2Type:guid)
                , posted_by_id VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, message CLOB NOT NULL, et_order INTEGER NOT NULL, thread_id CHAR(36) DEFAULT NULL COLLATE BINARY --
        (DC2Type:guid)
                , PRIMARY KEY(id))');
                $this->addSql('INSERT INTO post (id, thread_id, posted_by_id, title, message, et_order) SELECT id, thread_id, posted_by_id, title, message, et_order FROM __temp__post');
                $this->addSql('DROP TABLE __temp__post');
                $this->addSql('CREATE INDEX IDX_5A8A6C8DE2904019 ON post (thread_id)');
                $this->addSql('CREATE INDEX IDX_5A8A6C8D5A6D2235 ON post (posted_by_id)');
                $this->addSql('CREATE TEMPORARY TABLE __temp__thread AS SELECT id FROM thread');
                $this->addSql('DROP TABLE thread');
                $this->addSql('CREATE TABLE thread (id CHAR(36) NOT NULL COLLATE BINARY --
        (DC2Type:guid)
                , PRIMARY KEY(id))');
                $this->addSql('INSERT INTO thread (id) SELECT id FROM __temp__thread');
                $this->addSql('DROP TABLE __temp__thread');
                break;
            case "mysql":
                $this->addSql('ALTER TABLE post DROP ctime');
                break;
            default:
                $this->abort('Migration can only be executed safely on \'sqlite\' or \'mysql\'.');
                break;
        }
    }
}
