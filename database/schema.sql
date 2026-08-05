-- ============================================================================
-- U-Tracksis Database Schema (Merged & Consolidated)
-- ============================================================================
CREATE SCHEMA IF NOT EXISTS u_tracksis
DEFAULT CHARACTER SET utf8mb4
DEFAULT COLLATE utf8mb4_unicode_ci;

USE u_tracksis;

SET FOREIGN_KEY_CHECKS = 0;

-- Drop all tables (ordered from most dependent to least dependent)
DROP TABLE IF EXISTS announcement_reads;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS activities;
DROP TABLE IF EXISTS consultations;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS milestones;
DROP TABLE IF EXISTS group_members;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS sections;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS system_settings;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 1. STANDALONE TABLES
-- ============================================================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('student','adviser','admin','faculty') NOT NULL,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(500) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at TIMESTAMP NULL,
    adviser_thesis_load INT NOT NULL DEFAULT 0,    -- Merged from query.sql
    adviser_lecture_load INT NOT NULL DEFAULT 0,   -- Merged from query.sql
    adviser_research_load INT NOT NULL DEFAULT 0,  -- Merged from query.sql
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE system_settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value VARCHAR(500) NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. ACADEMIC STRUCTURE TABLES (Merged from query.sql)
-- ============================================================================

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE, 
    course_name VARCHAR(100) NOT NULL      
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_code VARCHAR(10) NOT NULL, 
    course_id INT NOT NULL,
    CONSTRAINT fk_sections_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. TEAMS & MEMBERSHIP TABLES
-- ============================================================================

CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(150) NOT NULL,
    thesis_title VARCHAR(255) NOT NULL,
    abstract TEXT NULL,
    adviser_id INT NULL,
    section_id INT NOT NULL,                                                                           -- Merged from query.sql
    approval_status ENUM('pending', 'approved', 'rejected', 'created') NOT NULL DEFAULT 'pending', -- Merged from query.sql
    progress_status ENUM('on track', 'falling behind') NOT NULL DEFAULT 'on track',                    -- Merged from query.sql
    status ENUM('active','archived') NOT NULL DEFAULT 'active',
    defense_date DATE NULL,
    submission_date DATE NULL,                                                                         -- Merged from query.sql
    academic_year VARCHAR(20) NULL,
    archived_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_teams_adviser (adviser_id),
    INDEX idx_teams_status (status),
    CONSTRAINT fk_teams_adviser FOREIGN KEY (adviser_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_teams_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE RESTRICT   -- Merged from query.sql
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    member_role VARCHAR(50) NOT NULL DEFAULT 'Member',
    joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_group_user (group_id, user_id),                                                    -- Unified naming
    INDEX idx_gm_user (user_id),
    CONSTRAINT fk_gm_group FOREIGN KEY (group_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_gm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. MILESTONES, SUBMISSIONS, FEEDBACK & CONSULTATIONS
-- ============================================================================

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
    INDEX idx_milestones_group (group_id),
    INDEX idx_milestones_status (status),
    CONSTRAINT fk_milestones_group FOREIGN KEY (group_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_milestones_chk_progress CHECK (progress <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    milestone_id INT NULL,
    document_type ENUM('title-proposal','chapter-1','chapter-2','chapter-3','chapter-4','chapter-5','final-thesis','revision','other') NOT NULL,
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
    INDEX idx_subs_group (group_id),
    INDEX idx_subs_status (status),
    INDEX idx_subs_uploaded_at (uploaded_at),
    INDEX idx_subs_uploader (uploaded_by),
    CONSTRAINT fk_subs_group FOREIGN KEY (group_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_subs_milestone FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE SET NULL,
    CONSTRAINT fk_subs_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_subs_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
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

-- ============================================================================
-- 5. ANNOUNCEMENTS, READS & SYSTEM ACTIVITIES
-- ============================================================================

-- Unified Announcements Schema (handles both course-level and global/group structures)
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,                                                                           -- Maps to author (admin)
    course_id INT NULL,                                                                               -- Nullable for non-course specific/global
    group_id INT NULL,                                                                                -- Nullable for broad/course specific announcements
    is_broadcast TINYINT(1) NOT NULL DEFAULT 0,
    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    due_date DATE NULL, 
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ann_sender (sender_id),
    INDEX idx_ann_course (course_id),
    INDEX idx_ann_group (group_id),
    INDEX idx_ann_created (created_at),
    CONSTRAINT fk_ann_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ann_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
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


-- ============================================================================
-- DEMO DATA (Updated with relational safety constraints)
-- ============================================================================

INSERT INTO system_settings (setting_key, setting_value) VALUES
  ('current_academic_year', '2025-2026'),
  ('site_name', 'U-Tracksis'),
  ('maintenance_mode', '0');

INSERT INTO users (role, firstname, lastname, email, password_hash, is_active, last_login_at, adviser_thesis_load, adviser_lecture_load, adviser_research_load) VALUES
('admin',       'Maria Corazon', 'Reyes',       'maria.reyes@dlsu.edu.ph',       '$2a$10$g86TLJk8OCTC6FUfmQ/kButsTfpCXqRHQbLreqM131F4qgF8qhKS2', 1, NOW() - INTERVAL 1 DAY, 0, 0, 0),
('admin', 'Ramon',         'Villanueva',  'ramon.villanueva@dlsu.edu.ph',  '$2a$10$BQ48TEdpbZj4xzYoGdiwKe4EROyAfxUH1QV/q/qIZ.YnLTYLKhooO', 1, NOW() - INTERVAL 2 DAY, 0, 0, 0),
('admin', 'Angelica',      'Bautista',    'angelica.bautista@dlsu.edu.ph','$2a$10$nwO2HVEbLFCAhnCh.Ls3PenmjBh6EmFuxuX9L1s/WGMm4cve9hLDS', 1, NOW() - INTERVAL 5 DAY, 0, 0, 0),
('adviser',     'Ferdinand',     'Santos',      'ferdinand.santos@dlsu.edu.ph', '$2a$10$0OnS.BkvfffaSvE575he/u15wKg1Mq17V1sfB1TNpK9BAd49B1/jq', 1, NOW() - INTERVAL 1 DAY, 3, 6, 2),
('adviser',     'Liza',          'Mendoza',     'liza.mendoza@dlsu.edu.ph',     '$2a$10$0OnS.BkvfffaSvE575he/u15wKg1Mq17V1sfB1TNpK9BAd49B1/jq', 1, NOW() - INTERVAL 3 DAY, 2, 9, 0),
('adviser',     'Antonio',       'Cruz',        'antonio.cruz@dlsu.edu.ph',     '$2a$10$0R8vYDgPLif3rHYvpo847OfdpZ9KJ.rXW1tA42y.c9nzErdqky75i', 1, NOW() - INTERVAL 7 DAY, 4, 3, 4),
('student',     'Juan Miguel',   'Dela Cruz',   'juan.delacruz@dlsu.edu.ph',    '$2a$10$Uk./6y/rlBQn/YE/w8RJcexmr4NtrYQDHFrlZtShk1P98apLoaL9O', 1, NOW() - INTERVAL 1 DAY, 0, 0, 0),
('student',     'Ana Bianca',    'Santos',      'ana.santos@dlsu.edu.ph',       '$2a$10$iJ.c2D3c8Ged.M7AYgtyqOFs2p98PMcuTmq/83SppDCEqQ/0/F6/W', 1, NOW() - INTERVAL 2 DAY, 0, 0, 0),
('student',     'Carlo Jandino', 'Ramos',       'carlo.ramos@dlsu.edu.ph',      '$2a$10$QVo2dqgEpGkCLuYHKgpM3eBqeOReKFzTKDpsh0ZXtuJqNTD3iSV66', 1, NOW() - INTERVAL 4 DAY, 0, 0, 0),
('student',     'Kristine Joy',  'Aquino',      'kristine.aquino@dlsu.edu.ph',  '$2a$10$i91fE6cRiZktXnxcwt7InOUai.GsCdoe8TdaTUiobsfyzjhz6d0bS', 1, NOW() - INTERVAL 2 DAY, 0, 0, 0),
('student',     'Paolo Gabriel', 'Reyes',       'paolo.reyes@dlsu.edu.ph',      '$2a$10$ltq04JLTPB3b2kw2g8ZypODbGVGzm7ysVvW5zmzYoXUC0Qyp1rX2m', 1, NOW() - INTERVAL 3 DAY, 0, 0, 0),
('student',     'Samantha Nicole','Garcia',     'samantha.garcia@dlsu.edu.ph',  '$2a$10$KT2PgsKY6z7DX2Na8E8ZWeH.hkYeXUCa4rXbpXhHUL00UGZSvjUSS', 1, NOW() - INTERVAL 6 DAY, 0, 0, 0),
('student',     'Marc Anthony',  'Torres',      'marc.torres@dlsu.edu.ph',      '$2a$10$Rs3NzrhWILDAKS8jmVZ9zOLv2f6Py0eHbVXBBOFwFxXFJgWYSvlN2', 1, NOW() - INTERVAL 10 DAY, 0, 0, 0),
('student',     'Bea Alexandra', 'Lim',         'bea.lim@dlsu.edu.ph',          '$2a$10$/6hlkXkt2l5uEt2r5cVM7uZdFxCZfnXbTxynqsT/MUNI/YWRE/G6S', 1, NOW() - INTERVAL 10 DAY, 0, 0, 0),
('student',     'Joshua Daniel', 'Fernandez',   'joshua.fernandez@dlsu.edu.ph', '$2a$10$0.XHbs6GVOmHwXnu0uy25.KvAn.RQQdTFdXQW19CZUeVJUILxV3HK', 1, NOW() - INTERVAL 10 DAY, 0, 0, 0);

-- Insert Demo Courses (Needed for Sections)
INSERT INTO courses (course_code, course_name) VALUES
('THESIS1', 'Thesis Writing 1'),
('THESIS2', 'Thesis Writing 2');

-- Insert Demo Sections (Needed for Teams)
INSERT INTO sections (section_code, course_id) VALUES
('S11', 1),
('S12', 1),
('S21', 2);

-- Insert Demo Teams (Equipped with integrated track columns and foreign sections)
INSERT INTO teams (group_name, thesis_title, abstract, adviser_id, section_id, approval_status, progress_status, status, defense_date, submission_date, academic_year, archived_at) VALUES
('Cortana', 'AI-Powered Attendance Monitoring System Using Facial Recognition',
 'A facial-recognition based attendance system for university classrooms that reduces proxy attendance and manual record-keeping.',
 4, 1, 'approved', 'on track', 'active', DATE_ADD(CURDATE(), INTERVAL 45 DAY), NULL, '2025-2026', NULL),
('ByteForce', 'Barangay Health Record Management System',
 'A digital health record system for barangay health centers to replace paper-based patient records and improve reporting.',
 5, 2, 'approved', 'falling behind', 'active', DATE_ADD(CURDATE(), INTERVAL 60 DAY), NULL, '2025-2026', NULL),
('NexGen', 'IoT-Based Flood Monitoring and Early Warning System',
 'A low-cost IoT sensor network that monitors water levels in flood-prone areas and sends real-time alerts to residents.',
 6, 3, 'approved', 'on track', 'archived', CURDATE() - INTERVAL 20 DAY, CURDATE() - INTERVAL 22 DAY, '2025-2026', NOW() - INTERVAL 5 DAY);

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

-- Insert Demo Announcements (Using the unified course/group announcement schema)
INSERT INTO announcements (sender_id, course_id, group_id, is_broadcast, is_pinned, title, message, created_at) VALUES
(1, NULL, NULL, 1, 0, 'Final Defense Schedule', 'All groups with an approved final chapter must confirm their defense slot with the assigned adviser by the end of the month.', NOW() - INTERVAL 3 DAY),
(1, NULL, NULL, 1, 0, 'Submission Deadline Reminder', 'Chapter 3 submissions are due within two weeks. Late submissions will need adviser approval for an extension.', NOW() - INTERVAL 1 DAY),
(1, NULL, NULL, 1, 0, 'Portal Maintenance Notice', 'The submission portal will be briefly unavailable for maintenance next weekend. Draft content will not be affected.', NOW() - INTERVAL 7 DAY),
(2, 1, NULL, 0, 1, 'Thesis 1 Defense Guidelines', 'Please check the uploaded PDF for details on the presentation format.', NOW() - INTERVAL 2 DAY);


INSERT INTO feedback (group_id, submission_id, given_by, author_role, message, created_at) VALUES
(1, 3, 4, 'adviser', 'Please expand the related studies section and fix citations.', NOW() - INTERVAL 14 DAY);

INSERT INTO activities (group_id, user_id, type, description, created_at) VALUES
(1, 9, 'submission', 'Submitted Chapter 2 for review.', NOW() - INTERVAL 6 DAY);