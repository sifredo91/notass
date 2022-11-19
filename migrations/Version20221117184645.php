<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221117184645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE nota_tag (nota_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_86FA31A3A98F9F02 (nota_id), INDEX IDX_86FA31A3BAD26311 (tag_id), PRIMARY KEY(nota_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, titulo VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE nota_tag ADD CONSTRAINT FK_86FA31A3A98F9F02 FOREIGN KEY (nota_id) REFERENCES nota (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nota_tag ADD CONSTRAINT FK_86FA31A3BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nota_tag DROP FOREIGN KEY FK_86FA31A3A98F9F02');
        $this->addSql('ALTER TABLE nota_tag DROP FOREIGN KEY FK_86FA31A3BAD26311');
        $this->addSql('DROP TABLE nota_tag');
        $this->addSql('DROP TABLE tag');
    }
}
