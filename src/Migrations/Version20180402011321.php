<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180402011321 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE post (id CHAR(36) NOT NULL --(DC2Type:guid)
        , thread_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , title VARCHAR(255) NOT NULL, message CLOB NOT NULL, et_order INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DE2904019 ON post (thread_id)');
        $this->addSql('CREATE TABLE thread (id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(id))');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE thread');
    }
}
