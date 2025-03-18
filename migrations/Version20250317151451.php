<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250317151451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE login_history (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, login_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ip_address VARCHAR(255) DEFAULT NULL, device VARCHAR(255) DEFAULT NULL, os VARCHAR(255) DEFAULT NULL, browser VARCHAR(255) NOT NULL, INDEX IDX_37976E36A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE novel (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, abstract LONGTEXT NOT NULL, is_published TINYINT(1) NOT NULL, released_at DATE DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', pic VARCHAR(255) NOT NULL, file VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, ref VARCHAR(255) NOT NULL, isbn VARCHAR(13) DEFAULT NULL, is_for_adult TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE renting_history (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, novel_id INT NOT NULL, start DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', end DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_page VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_484BDBB7A76ED395 (user_id), INDEX IDX_484BDBB7B9E41394 (novel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, rented_novels_count SMALLINT NOT NULL, is_adult TINYINT(1) NOT NULL, ref VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, is_terms TINYINT(1) NOT NULL, is_gpdr TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE login_history ADD CONSTRAINT FK_37976E36A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE renting_history ADD CONSTRAINT FK_484BDBB7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE renting_history ADD CONSTRAINT FK_484BDBB7B9E41394 FOREIGN KEY (novel_id) REFERENCES novel (id)');
        $this->addSql('ALTER TABLE novel_user ADD CONSTRAINT FK_756D77FBB9E41394 FOREIGN KEY (novel_id) REFERENCES novel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE novel_user ADD CONSTRAINT FK_756D77FBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_novel ADD CONSTRAINT FK_AD7B6611BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_novel ADD CONSTRAINT FK_AD7B6611B9E41394 FOREIGN KEY (novel_id) REFERENCES novel (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE novel_user DROP FOREIGN KEY FK_756D77FBB9E41394');
        $this->addSql('ALTER TABLE tag_novel DROP FOREIGN KEY FK_AD7B6611B9E41394');
        $this->addSql('ALTER TABLE tag_novel DROP FOREIGN KEY FK_AD7B6611BAD26311');
        $this->addSql('ALTER TABLE novel_user DROP FOREIGN KEY FK_756D77FBA76ED395');
        $this->addSql('ALTER TABLE login_history DROP FOREIGN KEY FK_37976E36A76ED395');
        $this->addSql('ALTER TABLE renting_history DROP FOREIGN KEY FK_484BDBB7A76ED395');
        $this->addSql('ALTER TABLE renting_history DROP FOREIGN KEY FK_484BDBB7B9E41394');
        $this->addSql('DROP TABLE login_history');
        $this->addSql('DROP TABLE novel');
        $this->addSql('DROP TABLE renting_history');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
