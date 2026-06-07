-- ============================================================
-- IRS - International Registration Server
-- Base de données SQL - Version 1.0
-- Compatible: MySQL 5.7+ / MariaDB 10.3+
-- Test local: WAMP Server / XAMPP / Laragon
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================
-- CRÉATION DE LA BASE DE DONNÉES
-- ============================================================



-- ============================================================
-- TABLE: admins
-- ============================================================

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: users
-- ============================================================

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `status` ENUM('active','suspended','inactive') NOT NULL DEFAULT 'active',
  `profile_picture` VARCHAR(255) DEFAULT NULL,
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: documents (Registre officiel IRS)
-- ============================================================

DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `document_number` VARCHAR(100) NOT NULL,
  `holder_name` VARCHAR(100) NOT NULL,
  `document_type` VARCHAR(50) NOT NULL,
  `issuing_organization` VARCHAR(150) NOT NULL,
  `issue_date` DATE DEFAULT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `country_of_origin` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('verified','rejected') NOT NULL DEFAULT 'verified',
  `description` TEXT DEFAULT NULL,
  `file_path` VARCHAR(255) DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `added_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_number` (`document_number`),
  KEY `fk_doc_admin` (`added_by`),
  CONSTRAINT `fk_doc_admin` FOREIGN KEY (`added_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: submitted_documents (Soumissions utilisateurs)
-- ============================================================

DROP TABLE IF EXISTS `submitted_documents`;
CREATE TABLE `submitted_documents` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `document_name` VARCHAR(100) NOT NULL,
  `document_number` VARCHAR(100) NOT NULL,
  `document_type` VARCHAR(50) NOT NULL,
  `issuing_organization` VARCHAR(150) NOT NULL,
  `country_of_origin` VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `file_path` VARCHAR(255) DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending','validated','rejected','info_requested') NOT NULL DEFAULT 'pending',
  `rejection_reason` TEXT DEFAULT NULL,
  `admin_notes` TEXT DEFAULT NULL,
  `reviewed_by` INT(11) DEFAULT NULL,
  `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
  `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_submit_user` (`user_id`),
  KEY `fk_submit_admin` (`reviewed_by`),
  CONSTRAINT `fk_submit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_submit_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: notifications
-- ============================================================

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `admin_id` INT(11) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notif_user` (`user_id`),
  KEY `fk_notif_admin` (`admin_id`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notif_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: activity_log
-- ============================================================

DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `admin_id` INT(11) DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_log_user` (`user_id`),
  KEY `fk_log_admin` (`admin_id`),
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_log_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: verifications (Historique des vérifications publiques)
-- ============================================================

DROP TABLE IF EXISTS `verifications`;
CREATE TABLE `verifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `document_number` VARCHAR(100) NOT NULL,
  `ip_address` VARCHAR(50) DEFAULT NULL,
  `result` ENUM('verified','not_verified') NOT NULL,
  `verified_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_doc_number` (`document_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: settings
-- ============================================================

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DONNÉES DE TEST - ADMINS
-- ============================================================

INSERT INTO `admins` (`username`, `email`, `password`, `full_name`, `status`) VALUES
('superadmin', 'admin@irs-server.com', 'Admin@2024', 'Super Administrateur IRS', 'active'),
('moderateur', 'mod@irs-server.com', 'Mod@2024', 'Modérateur IRS', 'active');

-- ============================================================
-- DONNÉES DE TEST - UTILISATEURS
-- ============================================================

INSERT INTO `users` (`first_name`, `last_name`, `email`, `phone`, `country`, `password`, `status`) VALUES
('Jean', 'Dupont', 'jean.dupont@email.com', '+33612345678', 'France', 'User@1234', 'active'),
('Marie', 'Curie', 'marie.curie@email.com', '+33698765432', 'France', 'User@1234', 'active'),
('Ahmed', 'Hassan', 'ahmed.hassan@email.com', '+21698765432', 'Tunisie', 'User@1234', 'active'),
('Sofia', 'Martinez', 'sofia.martinez@email.com', '+34612345678', 'Espagne', 'User@1234', 'active'),
('David', 'Johnson', 'david.johnson@email.com', '+12025551234', 'États-Unis', 'User@1234', 'active'),
('Fatima', 'Al-Rashid', 'fatima.rashid@email.com', '+96512345678', 'Koweït', 'User@1234', 'suspended'),
('Pierre', 'Leblanc', 'pierre.leblanc@email.com', '+32478123456', 'Belgique', 'User@1234', 'active');

-- ============================================================
-- DONNÉES DE TEST - DOCUMENTS OFFICIELS
-- ============================================================

INSERT INTO `documents` (`document_number`, `holder_name`, `document_type`, `issuing_organization`, `issue_date`, `country_of_origin`, `status`, `description`, `added_by`) VALUES
('IRS-2024-FR-001234', 'Jean Dupont', 'Diplôme', 'Université Paris-Sorbonne', '2023-06-15', 'France', 'verified', 'Licence en Sciences Économiques', 1),
('IRS-2024-TN-005678', 'Ahmed Ben Salah', 'Baccalauréat', 'Ministère de l\'Éducation Nationale', '2022-07-10', 'Tunisie', 'verified', 'Baccalauréat série Sciences', 1),
('IRS-2024-MA-009012', 'Karim Alaoui', 'Attestation de travail', 'Office Chérifien des Phosphates', '2023-12-01', 'Maroc', 'verified', 'Attestation de travail certifiée', 1),
('IRS-2024-ES-003456', 'Sofia Martinez', 'Relevé de notes', 'Universidad Complutense de Madrid', '2023-09-30', 'Espagne', 'verified', 'Relevé de notes Master en Droit', 1),
('IRS-2024-US-007890', 'John Williams', 'Diplôme', 'Harvard University', '2023-05-20', 'États-Unis', 'verified', 'Bachelor of Science in Computer Science', 1),
('IRS-2024-CM-011122', 'Paul Biya Nguema', 'Acte de naissance', 'Mairie de Yaoundé', '1990-03-15', 'Cameroun', 'verified', 'Acte de naissance officiel', 1),
('IRS-2024-SN-033344', 'Aminata Diallo', 'Certificat médical', 'Hôpital Principal de Dakar', '2024-01-10', 'Sénégal', 'verified', 'Certificat médical de bonne santé', 1),
('IRS-2024-CI-055566', 'Kouassi Jean-Baptiste', 'Diplôme', 'Université Félix Houphouët-Boigny', '2023-11-25', 'Côte d\'Ivoire', 'verified', 'Master en Gestion des Entreprises', 1),
('IRS-2024-BE-077788', 'Marie Dubois', 'Contrat', 'Commission Européenne', '2024-01-01', 'Belgique', 'verified', 'Contrat de travail UE', 1),
('IRS-2024-DE-099900', 'Hans Mueller', 'Relevé de notes', 'Technische Universität München', '2023-08-15', 'Allemagne', 'verified', 'Transcript - Engineering Master', 1);

-- ============================================================
-- DONNÉES DE TEST - DOCUMENTS SOUMIS
-- ============================================================

INSERT INTO `submitted_documents` (`user_id`, `document_name`, `document_number`, `document_type`, `issuing_organization`, `country_of_origin`, `description`, `status`) VALUES
(1, 'Diplôme de Licence', 'UNIV-2023-78945', 'Diplôme', 'Université Lyon 3', 'France', 'Licence en droit privé obtenue en juin 2023', 'pending'),
(2, 'Attestation de travail', 'ATW-2024-12345', 'Attestation de travail', 'Société ACME France', 'France', 'Attestation pour poste d\'ingénieur', 'validated'),
(3, 'Baccalauréat 2022', 'BAC-TN-2022-55678', 'Baccalauréat', 'Ministère Tunisien de l\'Éducation', 'Tunisie', 'Baccalauréat mention Bien', 'rejected'),
(4, 'Master Droit européen', 'MASTER-EU-2023-789', 'Relevé de notes', 'Universidad de Salamanca', 'Espagne', 'Notes du Master 2 en droit européen', 'pending'),
(5, 'CS Bachelor Diploma', 'BACH-US-2023-4567', 'Diplôme', 'MIT', 'États-Unis', 'Bachelor of Science Computer Science', 'info_requested'),
(7, 'Attestation bancaire', 'BANK-BE-2024-001', 'Contrat', 'BNP Paribas Belgique', 'Belgique', 'Attestation de solde bancaire', 'pending');

-- ============================================================
-- DONNÉES DE TEST - NOTIFICATIONS
-- ============================================================

INSERT INTO `notifications` (`user_id`, `admin_id`, `title`, `message`, `type`, `is_read`) VALUES
(1, 1, 'Document reçu', 'Votre document "Diplôme de Licence" a été reçu et est en cours d\'analyse.', 'info', 0),
(2, 1, 'Document validé !', 'Votre document "Attestation de travail" a été validé avec succès.', 'success', 1),
(3, 1, 'Document rejeté', 'Votre document "Baccalauréat 2022" a été rejeté. Motif: Document incomplet', 'danger', 0),
(5, 1, 'Informations requises', 'Des informations complémentaires sont requises pour votre document "CS Bachelor Diploma"', 'warning', 0),
(1, 1, 'Bienvenue sur IRS', 'Bienvenue sur la plateforme IRS. Vous pouvez soumettre vos documents pour vérification.', 'info', 1);

-- ============================================================
-- DONNÉES DE TEST - JOURNAL D'ACTIVITÉ
-- ============================================================

INSERT INTO `activity_log` (`user_id`, `admin_id`, `action`, `description`, `ip_address`) VALUES
(NULL, 1, 'admin_login', 'Connexion administrateur: admin@irs-server.com', '127.0.0.1'),
(1, NULL, 'user_register', 'Nouvel utilisateur: jean.dupont@email.com', '127.0.0.1'),
(1, NULL, 'user_login', 'Connexion utilisateur: jean.dupont@email.com', '127.0.0.1'),
(1, NULL, 'document_submit', 'Document soumis: Diplôme de Licence (UNIV-2023-78945)', '127.0.0.1'),
(NULL, 1, 'admin_validate_doc', 'Validation soumission ID: 2', '127.0.0.1'),
(NULL, 1, 'admin_reject_doc', 'Rejet soumission ID: 3', '127.0.0.1'),
(NULL, 1, 'admin_add_doc', 'Ajout document officiel: IRS-2024-FR-001234', '127.0.0.1');

-- ============================================================
-- DONNÉES DE TEST - VÉRIFICATIONS
-- ============================================================

INSERT INTO `verifications` (`document_number`, `ip_address`, `result`) VALUES
('IRS-2024-FR-001234', '192.168.1.100', 'verified'),
('IRS-2024-TN-005678', '10.0.0.50', 'verified'),
('DOC-FAKE-99999', '192.168.1.101', 'not_verified'),
('IRS-2024-MA-009012', '172.16.0.1', 'verified'),
('UNKNOWN-DOC', '192.168.1.102', 'not_verified');

-- ============================================================
-- PARAMÈTRES SYSTÈME
-- ============================================================

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'IRS - International Registration Server'),
('site_email', 'admin@irs-server.com'),
('default_language', 'fr'),
('allow_registration', '1'),
('docs_per_page', '20'),
('max_upload_size', '10485760');

COMMIT;

-- ============================================================
-- RÉSUMÉ DES COMPTES DE TEST
-- ============================================================
-- 
-- ADMINISTRATEUR:
--   Email    : admin@irs-server.com
--   Password : Admin@2024
--   URL Admin: http://localhost/irs/admin/login.php
--
-- UTILISATEURS TEST:
--   Email    : jean.dupont@email.com      | Password: User@1234
--   Email    : marie.curie@email.com      | Password: User@1234
--   Email    : ahmed.hassan@email.com     | Password: User@1234
--
-- DOCUMENTS VÉRIFIABLES (test sur index.php):
--   IRS-2024-FR-001234  → VERIFIED (Diplôme - Jean Dupont)
--   IRS-2024-TN-005678  → VERIFIED (Bac - Ahmed Ben Salah)
--   IRS-2024-US-007890  → VERIFIED (Harvard - John Williams)
--   DOC-INEXISTANT      → NOT VERIFIED
--
-- ============================================================
