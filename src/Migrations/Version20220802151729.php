<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220802151729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_payment ADD bridge_payment_api_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_payment_method ADD bridge_test_mode TINYINT(1) DEFAULT 1 NOT NULL, ADD bridge_logo TINYINT(1) DEFAULT 1 NOT NULL, ADD bridge_test_webhook_configuration_date DATETIME DEFAULT NULL, ADD bridge_production_webhook_configuration_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_payment DROP bridge_payment_api_id');
        $this->addSql('ALTER TABLE sylius_payment_method DROP bridge_test_mode, DROP bridge_logo, DROP bridge_test_webhook_configuration_date, DROP bridge_production_webhook_configuration_date');
    }
}
