<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190218191657 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room ADD subcategory_id INT NOT NULL');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B5DC6FE57 FOREIGN KEY (subcategory_id) REFERENCES sub_category (id)');
        $this->addSql('CREATE INDEX IDX_729F519B5DC6FE57 ON room (subcategory_id)');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C15DC6FE57');
        $this->addSql('DROP INDEX IDX_64C19C15DC6FE57 ON category');
        $this->addSql('ALTER TABLE category DROP subcategory_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE category ADD subcategory_id INT NOT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C15DC6FE57 FOREIGN KEY (subcategory_id) REFERENCES sub_category (id)');
        $this->addSql('CREATE INDEX IDX_64C19C15DC6FE57 ON category (subcategory_id)');
        $this->addSql('ALTER TABLE room DROP FOREIGN KEY FK_729F519B5DC6FE57');
        $this->addSql('DROP INDEX IDX_729F519B5DC6FE57 ON room');
        $this->addSql('ALTER TABLE room DROP subcategory_id');
    }
}
