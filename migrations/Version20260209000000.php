<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial migration: Create users, products, and subscriptions tables
 */
final class Version20260209000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users, products, and subscriptions tables for subscription management system';
    }

    public function up(Schema $schema): void
    {
        // Create users table
        $this->addSql('
            CREATE TABLE users (
                id VARCHAR(36) NOT NULL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                INDEX idx_email (email)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');

        // Create products table
        $this->addSql('
            CREATE TABLE products (
                id VARCHAR(36) NOT NULL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                pricing_options JSON NOT NULL,
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                INDEX idx_name (name)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');

        // Create subscriptions table
        $this->addSql('
            CREATE TABLE subscriptions (
                id VARCHAR(36) NOT NULL PRIMARY KEY,
                user_id VARCHAR(36) NOT NULL,
                product_id VARCHAR(36) NOT NULL,
                pricing_option_name VARCHAR(255) NOT NULL,
                start_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                end_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                cancelled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                INDEX idx_user_id (user_id),
                INDEX idx_product_id (product_id),
                INDEX idx_dates (start_date, end_date),
                INDEX idx_cancelled (cancelled_at)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE subscriptions');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE users');
    }
}
