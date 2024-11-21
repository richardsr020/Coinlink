<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241120135121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fee CHANGE feeamount feeamount DOUBLE PRECISION NOT NULL, CHANGE feepercentage feepercentage DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE incomes CHANGE amount amount NUMERIC(10, 2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fee CHANGE feeamount feeamount INT NOT NULL, CHANGE feepercentage feepercentage INT NOT NULL');
        $this->addSql('ALTER TABLE incomes CHANGE amount amount INT NOT NULL');
    }
}
