<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240926193624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D3656A42A272AB9 ON account (accountnumber)');
        $this->addSql('ALTER TABLE user CHANGE is_email_verified is_email_verified TINYINT(1) DEFAULT 0 NOT NULL, CHANGE has_accepted_terms has_accepted_terms TINYINT(1) DEFAULT 0 NOT NULL, CHANGE activation_token activation_token VARCHAR(64) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_7D3656A42A272AB9 ON account');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user CHANGE is_email_verified is_email_verified TINYINT(1) NOT NULL, CHANGE has_accepted_terms has_accepted_terms TINYINT(1) NOT NULL, CHANGE activation_token activation_token VARCHAR(255) NOT NULL');
    }
}
