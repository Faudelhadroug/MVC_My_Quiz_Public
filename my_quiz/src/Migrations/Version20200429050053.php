<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200429050053 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC71E27F6BF');
        $this->addSql('DROP INDEX IDX_5FB6DEC71E27F6BF ON reponse');
        $this->addSql('ALTER TABLE reponse DROP question_id');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7E62CA5DB FOREIGN KEY (id_question) REFERENCES question (id)');
        $this->addSql('CREATE INDEX IDX_5FB6DEC7E62CA5DB ON reponse (id_question)');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE verified_at verified_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7E62CA5DB');
        $this->addSql('DROP INDEX IDX_5FB6DEC7E62CA5DB ON reponse');
        $this->addSql('ALTER TABLE reponse ADD question_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC71E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('CREATE INDEX IDX_5FB6DEC71E27F6BF ON reponse (question_id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE verified_at verified_at DATETIME DEFAULT \'NULL\'');
    }
}
