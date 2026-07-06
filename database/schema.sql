-- RSVP Email/Messaging Platform - Database Schema
-- Charset padrão utf8mb4 para suportar emojis/acentos sem problemas.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'promoter', 'client') NOT NULL,
    promoter_id INT UNSIGNED NULL, -- preenchido quando role = 'client': aponta para o promotor dono do cliente
    messages_per_hour INT UNSIGNED NULL DEFAULT 100, -- limite de disparos/hora, definido pelo admin (aplica-se a promoters)
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_promoter FOREIGN KEY (promoter_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    promoter_id INT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    description TEXT NULL,
    event_date DATETIME NULL,
    location VARCHAR(255) NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    status ENUM('draft', 'active', 'closed') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_promoter FOREIGN KEY (promoter_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    type ENUM('confirmation', 'capture') NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    title VARCHAR(190) NOT NULL,
    intro_text TEXT NULL,
    success_message TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_event_type (event_id, type),
    CONSTRAINT fk_pages_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(40) NULL,
    guests_count INT UNSIGNED NOT NULL DEFAULT 0,
    source ENUM('manual', 'import', 'capture_page', 'confirmation_page') NOT NULL DEFAULT 'manual',
    rsvp_status ENUM('pending', 'confirmed', 'declined') NOT NULL DEFAULT 'pending',
    rsvp_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_event_email (event_id, email),
    CONSTRAINT fk_contacts_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS email_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    promoter_id INT UNSIGNED NOT NULL,
    event_id INT UNSIGNED NULL,
    name VARCHAR(190) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_html MEDIUMTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_templates_promoter FOREIGN KEY (promoter_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_templates_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS campaigns (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    template_id INT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    status ENUM('draft', 'queued', 'sending', 'completed', 'paused') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_campaigns_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_campaigns_template FOREIGN KEY (template_id) REFERENCES email_templates(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS campaign_recipients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT UNSIGNED NOT NULL,
    contact_id INT UNSIGNED NOT NULL,
    tracking_token CHAR(40) NOT NULL UNIQUE,
    status ENUM('pending', 'sent', 'failed', 'opened', 'clicked') NOT NULL DEFAULT 'pending',
    error_message VARCHAR(255) NULL,
    sent_at DATETIME NULL,
    opened_at DATETIME NULL,
    first_clicked_at DATETIME NULL,
    open_count INT UNSIGNED NOT NULL DEFAULT 0,
    click_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_campaign_contact (campaign_id, contact_id),
    CONSTRAINT fk_recipients_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    CONSTRAINT fk_recipients_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS email_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_recipient_id INT UNSIGNED NOT NULL,
    type ENUM('open', 'click') NOT NULL,
    url VARCHAR(500) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_recipient FOREIGN KEY (campaign_recipient_id) REFERENCES campaign_recipients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Estrutura preparada para WhatsApp; integração real com provedor fica para uma etapa futura.
CREATE TABLE IF NOT EXISTS whatsapp_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    contact_id INT UNSIGNED NOT NULL,
    message_text TEXT NOT NULL,
    status ENUM('pending', 'simulated_sent', 'failed') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME NULL,
    CONSTRAINT fk_whatsapp_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_whatsapp_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Estrutura preparada para acompanhamento de ligações; discagem real fica para uma etapa futura.
CREATE TABLE IF NOT EXISTS calls (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    contact_id INT UNSIGNED NOT NULL,
    outcome ENUM('scheduled', 'completed', 'no_answer', 'declined') NOT NULL DEFAULT 'scheduled',
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_calls_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_calls_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Controla o que cada cliente pode ver em cada evento do seu promotor.
CREATE TABLE IF NOT EXISTS event_client_visibility (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    can_view_contacts TINYINT(1) NOT NULL DEFAULT 1,
    can_view_email_stats TINYINT(1) NOT NULL DEFAULT 1,
    can_view_whatsapp TINYINT(1) NOT NULL DEFAULT 0,
    can_view_calls TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_event_client (event_id, client_id),
    CONSTRAINT fk_visibility_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_visibility_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
