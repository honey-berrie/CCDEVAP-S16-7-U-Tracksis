-- ============================================================================
-- U-Tracksis Database Schema
-- ============================================================================
CREATE SCHEMA IF NOT EXISTS u_tracksis
DEFAULT CHARACTER SET utf8mb4
DEFAULT COLLATE utf8mb4_unicode_ci;

USE u_tracksis;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS announcement_reads;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS activities;
DROP TABLE IF EXISTS consultations;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS milestones;
DROP TABLE IF EXISTS group_members;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS system_settings;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('student','adviser','coordinator','admin') NOT NULL,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(500) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(150) NOT NULL,
    thesis_title VARCHAR(255) NOT NULL,
    abstract TEXT NULL,
    adviser_id INT NULL,
    defense_date DATE NULL,
    academic_year VARCHAR(20) NULL,
    status ENUM('active','archived') NOT NULL DEFAULT 'active',
    archived_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_teams_adviser (adviser_id),
    INDEX idx_teams_status (status),
    CONSTRAINT fk_teams_adviser FOREIGN KEY (adviser_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    member_role VARCHAR(50) NOT NULL DEFAULT 'Member',
    joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_gm (group_id, user_id),
    INDEX idx_gm_user (user_id),
    CONSTRAINT fk_gm_group FOREIGN KEY (group_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_gm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE milestones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    due_date DATE NULL,
    progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('in-progress','in-review','approved','rejected') NOT NULL DEFAULT 'in-progress',
    display_order INT NOT NULL DEFAULT 0,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ms_group (group_id),
    INDEX idx_ms_status (status),
    CONSTRAINT fk_ms_group FOREIGN KEY (group_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT chk_ms_progress CHECK (progress <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    milestone_id INT NULL,
    document_type ENUM( 'title-proposal','chapter-1','chapter-2','chapter-3', 'chapter-4','chapter-5','final-thesis','revision','other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    mime_type VARCHAR(100) NOT NULL DEFAULT 'application/pdf',
    uploaded_by INT NOT NULL,
    status ENUM('in-review','approved','rejected','revision-requested') NOT NULL DEFAULT 'in-review',
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    review_notes TEXT NULL,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sub_group (group_id),
    INDEX idx_sub_status (status),
    INDEX idx_sub_uploaded (uploaded_at),
    INDEX idx_sub_uploader (uploaded_by),
    CONSTRAINT fk_sub_group FOREIGN KEY (group_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_sub_ms FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE SET NULL,
    CONSTRAINT fk_sub_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_sub_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    submission_id INT NULL,
    given_by INT NOT NULL,
    author_role VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fb_group (group_id),
    INDEX idx_fb_submission (submission_id),
    INDEX idx_fb_created (created_at),
    CONSTRAINT fk_fb_group FOREIGN KEY (group_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_fb_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_fb_giver FOREIGN KEY (given_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    group_id INT NULL,
    is_broadcast TINYINT(1) NOT NULL DEFAULT 0,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ann_created (created_at),
    INDEX idx_ann_sender (sender_id),
    INDEX idx_ann_group (group_id),
    CONSTRAINT fk_ann_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ann_group FOREIGN KEY (group_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE announcement_reads (
    announcement_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (announcement_id, user_id),
    INDEX idx_ar_user (user_id),
    CONSTRAINT fk_ar_ann FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE,
    CONSTRAINT fk_ar_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    recipient_id INT NOT NULL,
    topic VARCHAR(255) NOT NULL,
    agenda TEXT NULL,
    proposed_schedule DATETIME NULL,
    consultation_end DATETIME NULL,
    meeting_link VARCHAR(500) NULL,
    status ENUM('pending','approved','completed','cancelled') NOT NULL DEFAULT 'pending',
    adviser_notes TEXT NULL,
    reschedule_reason TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cons_group (group_id),
    INDEX idx_cons_user (user_id),
    INDEX idx_cons_recipient (recipient_id),
    INDEX idx_cons_status (status),
    INDEX idx_cons_created (created_at),
    CONSTRAINT fk_cons_group FOREIGN KEY (group_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_cons_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cons_recipient FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NULL,
    user_id INT NULL,
    type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_act_group (group_id),
    INDEX idx_act_created (created_at),
    CONSTRAINT fk_act_group FOREIGN KEY (group_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_act_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE system_settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value VARCHAR(500) NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DEMO DATA
-- ============================================================================

INSERT INTO system_settings (setting_key, setting_value) VALUES
  ('current_academic_year', '2025-2026'),
  ('site_name', 'U-Tracksis'),
  ('maintenance_mode', '0');

INSERT INTO users (role, firstname, lastname, email, password_hash, is_active, last_login_at) VALUES
('admin',       'Maria Corazon', 'Reyes',       'maria.reyes@dlsu.edu.ph',       'demo1234', 1, NOW() - INTERVAL 1 DAY),
('coordinator', 'Ramon',         'Villanueva',  'ramon.villanueva@dlsu.edu.ph',  'demo1234', 1, NOW() - INTERVAL 2 DAY),
('coordinator', 'Angelica',      'Bautista',    'angelica.bautista@dlsu.edu.ph','demo1234', 1, NOW() - INTERVAL 5 DAY),
('adviser',     'Ferdinand',     'Santos',      'ferdinand.santos@dlsu.edu.ph', 'demo1234', 1, NOW() - INTERVAL 1 DAY),
('adviser',     'Liza',          'Mendoza',     'liza.mendoza@dlsu.edu.ph',     'demo1234', 1, NOW() - INTERVAL 3 DAY),
('adviser',     'Antonio',       'Cruz',        'antonio.cruz@dlsu.edu.ph',     'demo1234', 1, NOW() - INTERVAL 7 DAY),
('student',     'Juan Miguel',   'Dela Cruz',   'juan.delacruz@dlsu.edu.ph',    'demo1234', 1, NOW() - INTERVAL 1 DAY),
('student',     'Ana Bianca',    'Santos',      'ana.santos@dlsu.edu.ph',       'demo1234', 1, NOW() - INTERVAL 2 DAY),
('student',     'Carlo Jandino', 'Ramos',       'carlo.ramos@dlsu.edu.ph',      'demo1234', 1, NOW() - INTERVAL 4 DAY),
('student',     'Kristine Joy',  'Aquino',      'kristine.aquino@dlsu.edu.ph',  'demo1234', 1, NOW() - INTERVAL 2 DAY),
('student',     'Paolo Gabriel', 'Reyes',       'paolo.reyes@dlsu.edu.ph',      'demo1234', 1, NOW() - INTERVAL 3 DAY),
('student',     'Samantha Nicole','Garcia',     'samantha.garcia@dlsu.edu.ph',  'demo1234', 1, NOW() - INTERVAL 6 DAY),
('student',     'Marc Anthony',  'Torres',      'marc.torres@dlsu.edu.ph',      'demo1234', 1, NOW() - INTERVAL 10 DAY),
('student',     'Bea Alexandra', 'Lim',         'bea.lim@dlsu.edu.ph',          'demo1234', 1, NOW() - INTERVAL 10 DAY),
('student',     'Joshua Daniel', 'Fernandez',   'joshua.fernandez@dlsu.edu.ph', 'demo1234', 1, NOW() - INTERVAL 10 DAY);

INSERT INTO teams (group_name, thesis_title, abstract, adviser_id, defense_date, academic_year, status, archived_at) VALUES
('Cortana', 'AI-Powered Attendance Monitoring System Using Facial Recognition',
 'A facial-recognition based attendance system for university classrooms that reduces proxy attendance and manual record-keeping.',
 4, DATE_ADD(CURDATE(), INTERVAL 45 DAY), '2025-2026', 'active', NULL),
('ByteForce', 'Barangay Health Record Management System',
 'A digital health record system for barangay health centers to replace paper-based patient records and improve reporting.',
 5, DATE_ADD(CURDATE(), INTERVAL 60 DAY), '2025-2026', 'active', NULL),
('NexGen', 'IoT-Based Flood Monitoring and Early Warning System',
 'A low-cost IoT sensor network that monitors water levels in flood-prone areas and sends real-time alerts to residents.',
 6, CURDATE() - INTERVAL 20 DAY, '2025-2026', 'archived', NOW() - INTERVAL 5 DAY);

INSERT INTO group_members (group_id, user_id, member_role) VALUES
(1, 7, 'Leader'), (1, 8, 'Member'), (1, 9, 'Member'),
(2, 10, 'Leader'), (2, 11, 'Member'), (2, 12, 'Member'),
(3, 13, 'Leader'), (3, 14, 'Member'), (3, 15, 'Member');

INSERT INTO milestones (group_id, name, description, due_date, progress, status, display_order, completed_at) VALUES
(1, 'Title Proposal', 'Initial thesis title and scope proposal.', CURDATE() - INTERVAL 60 DAY, 100, 'approved', 1, CURDATE() - INTERVAL 55 DAY),
(1, 'Chapter 1',      'Introduction and background of the study.', CURDATE() - INTERVAL 40 DAY, 100, 'approved', 2, CURDATE() - INTERVAL 35 DAY),
(1, 'Chapter 2',      'Review of related literature.',             CURDATE() - INTERVAL 15 DAY, 80,  'in-review', 3, NULL),
(1, 'Chapter 3',      'Theoretical framework and methodology.',    CURDATE() + INTERVAL 10 DAY, 40,  'in-progress', 4, NULL),
(1, 'Chapter 4',      'Results and analysis.',                     CURDATE() + INTERVAL 30 DAY, 10,  'in-progress', 5, NULL),

(2, 'Title Proposal', 'Initial thesis title and scope proposal.', CURDATE() - INTERVAL 65 DAY, 100, 'approved', 1, CURDATE() - INTERVAL 60 DAY),
(2, 'Chapter 1',      'Introduction and background of the study.', CURDATE() - INTERVAL 45 DAY, 100, 'approved', 2, CURDATE() - INTERVAL 40 DAY),
(2, 'Chapter 2',      'Review of related literature.',             CURDATE() - INTERVAL 20 DAY, 60,  'rejected', 3, NULL),
(2, 'Chapter 3',      'Theoretical framework and methodology.',    CURDATE() + INTERVAL 12 DAY, 20,  'in-progress', 4, NULL),
(2, 'Chapter 4',      'Results and analysis.',                     CURDATE() + INTERVAL 35 DAY, 0,   'in-progress', 5, NULL),

(3, 'Title Proposal', 'Initial thesis title and scope proposal.', CURDATE() - INTERVAL 120 DAY, 100, 'approved', 1, CURDATE() - INTERVAL 115 DAY),
(3, 'Chapter 1',      'Introduction and background of the study.', CURDATE() - INTERVAL 100 DAY, 100, 'approved', 2, CURDATE() - INTERVAL 95 DAY),
(3, 'Chapter 2',      'Review of related literature.',             CURDATE() - INTERVAL 80 DAY,  100, 'approved', 3, CURDATE() - INTERVAL 75 DAY),
(3, 'Chapter 3',      'Theoretical framework and methodology.',    CURDATE() - INTERVAL 50 DAY,  100, 'approved', 4, CURDATE() - INTERVAL 45 DAY),
(3, 'Final Defense',  'Final thesis defense before the panel.',    CURDATE() - INTERVAL 20 DAY,  100, 'approved', 5, CURDATE() - INTERVAL 20 DAY);

INSERT INTO submissions (group_id, milestone_id, document_type, title, file_name, file_path, file_size, mime_type, uploaded_by, status, reviewed_by, reviewed_at, review_notes, uploaded_at) VALUES
(1, 1, 'title-proposal', 'Title Proposal - Cortana', 'cortana_title_proposal.pdf', '/uploads/cortana/title_proposal.pdf', 482300, 'application/pdf', 7, 'approved', 4, CURDATE() - INTERVAL 55 DAY, 'Approved. Well-scoped proposal.', CURDATE() - INTERVAL 58 DAY),
(1, 2, 'chapter-1',      'Chapter 1 - Cortana',      'cortana_chapter1.pdf',       '/uploads/cortana/chapter1.pdf',       915400, 'application/pdf', 8, 'approved', 4, CURDATE() - INTERVAL 35 DAY, 'Good introduction, minor grammar fixes made.', CURDATE() - INTERVAL 38 DAY),
(1, 3, 'chapter-2',      'Chapter 2 - Cortana',      'cortana_chapter2.pdf',       '/uploads/cortana/chapter2.pdf',      1204500, 'application/pdf', 9, 'in-review', NULL, NULL, NULL, CURDATE() - INTERVAL 6 DAY),
(1, 3, 'revision',       'Chapter 2 Revision - Cortana', 'cortana_chapter2_rev1.pdf', '/uploads/cortana/chapter2_rev1.pdf', 998200, 'application/pdf', 7, 'revision-requested', 4, CURDATE() - INTERVAL 14 DAY, 'Please expand the related studies section and fix citations.', CURDATE() - INTERVAL 16 DAY),

(2, 6, 'title-proposal', 'Title Proposal - ByteForce', 'byteforce_title_proposal.pdf', '/uploads/byteforce/title_proposal.pdf', 455100, 'application/pdf', 10, 'approved', 5, CURDATE() - INTERVAL 60 DAY, 'Approved.', CURDATE() - INTERVAL 63 DAY),
(2, 7, 'chapter-1',      'Chapter 1 - ByteForce',      'byteforce_chapter1.pdf',       '/uploads/byteforce/chapter1.pdf',      878900, 'application/pdf', 11, 'approved', 5, CURDATE() - INTERVAL 40 DAY, 'Approved with minor notes.', CURDATE() - INTERVAL 42 DAY),
(2, 8, 'chapter-2',      'Chapter 2 - ByteForce',      'byteforce_chapter2.pdf',      '/uploads/byteforce/chapter2.pdf', 1102300, 'application/pdf', 12, 'rejected', 5, CURDATE() - INTERVAL 18 DAY, 'Literature review is too thin. Please redo with more recent sources.', CURDATE() - INTERVAL 20 DAY),
(2, 9, 'other',          'Data Collection Instrument - ByteForce', 'byteforce_survey.pdf', '/uploads/byteforce/survey.pdf', 210400, 'application/pdf', 10, 'in-review', NULL, NULL, NULL, CURDATE() - INTERVAL 3 DAY),

(3, 11, 'title-proposal', 'Title Proposal - NexGen', 'nexgen_title_proposal.pdf', '/uploads/nexgen/title_proposal.pdf', 402100, 'application/pdf', 13, 'approved', 6, CURDATE() - INTERVAL 115 DAY, 'Approved.', CURDATE() - INTERVAL 118 DAY),
(3, 12, 'chapter-1',      'Chapter 1 - NexGen',      'nexgen_chapter1.pdf',       '/uploads/nexgen/chapter1.pdf',       845600, 'application/pdf', 14, 'approved', 6, CURDATE() - INTERVAL 95 DAY,  'Approved.', CURDATE() - INTERVAL 98 DAY),
(3, 13, 'chapter-2',      'Chapter 2 - NexGen',      'nexgen_chapter2.pdf',       '/uploads/nexgen/chapter2.pdf',      1050200, 'application/pdf', 15, 'approved', 6, CURDATE() - INTERVAL 75 DAY,  'Approved.', CURDATE() - INTERVAL 78 DAY),
(3, 14, 'chapter-3',      'Chapter 3 - NexGen',      'nexgen_chapter3.pdf',       '/uploads/nexgen/chapter3.pdf',      1183400, 'application/pdf', 13, 'approved', 6, CURDATE() - INTERVAL 45 DAY,  'Approved.', CURDATE() - INTERVAL 48 DAY),
(3, 15, 'final-thesis',   'Final Thesis - NexGen',   'nexgen_final.pdf',          '/uploads/nexgen/final_thesis.pdf', 3021500, 'application/pdf', 14, 'approved', 6, CURDATE() - INTERVAL 20 DAY,  'Approved by the panel. Congratulations!', CURDATE() - INTERVAL 22 DAY);

INSERT INTO announcements (sender_id, group_id, is_broadcast, title, message, created_at) VALUES
(1, NULL, 1, 'Final Defense Schedule', 'All groups with an approved final chapter must confirm their defense slot with the coordinator by the end of the month.', NOW() - INTERVAL 3 DAY),
(1, NULL, 1, 'Submission Deadline Reminder', 'Chapter 3 submissions are due within two weeks. Late submissions will need adviser approval for an extension.', NOW() - INTERVAL 1 DAY),
(1, NULL, 1, 'Portal Maintenance Notice', 'The submission portal will be briefly unavailable for maintenance next weekend. Draft content will not be affected.', NOW() - INTERVAL 7 DAY);
