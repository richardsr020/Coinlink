<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241121113406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE loan (id INT AUTO_INCREMENT NOT NULL, loanauthor_id INT NOT NULL, accountid INT NOT NULL, amount DOUBLE PRECISION NOT NULL, duedate DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', acceptedterm TINYINT(1) NOT NULL, INDEX IDX_C5D30D038FF34CD0 (loanauthor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE loan_roules (id INT AUTO_INCREMENT NOT NULL, minamount DOUBLE PRECISION NOT NULL, maxamount DOUBLE PRECISION NOT NULL, duration INT NOT NULL, interestrate INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D038FF34CD0 FOREIGN KEY (loanauthor_id) REFERENCES account (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loan DROP FOREIGN KEY FK_C5D30D038FF34CD0');
        $this->addSql('DROP TABLE loan');
        $this->addSql('DROP TABLE loan_roules');
    }
}
