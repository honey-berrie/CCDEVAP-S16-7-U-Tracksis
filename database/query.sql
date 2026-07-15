USE u_tracksis;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS milestones;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS group_members;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS users;


SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS users (
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

CREATE TABLE IF NOT EXISTS teams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_name VARCHAR(150) NOT NULL,
  thesis_title VARCHAR(255) NOT NULL,
  abstract TEXT NULL,
  adviser_id INT NULL,
  defense_date DATE NULL,
  academic_year VARCHAR(20) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_groups_adviser (adviser_id),
  CONSTRAINT fk_groups_adviser FOREIGN KEY (adviser_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS group_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT NOT NULL,
  user_id INT NOT NULL,
  member_role VARCHAR(50) NOT NULL DEFAULT 'Member',
  joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_group_user (group_id, user_id),
  INDEX idx_gm_user (user_id),
  CONSTRAINT fk_gm_group FOREIGN KEY (group_id) REFERENCES teams(id) ON DELETE CASCADE,
  CONSTRAINT fk_gm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS milestones (
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

CREATE TABLE IF NOT EXISTS submissions (
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

CREATE TABLE IF NOT EXISTS courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_code VARCHAR(20) NOT NULL UNIQUE, -- e.g., 'THSCC02', 'CCRESME'
  course_name VARCHAR(100) NOT NULL,       -- e.g., 'Thesis Writing 2'
  coordinator_id INT NULL,
  FOREIGN KEY (coordinator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section_code VARCHAR(10) NOT NULL, -- e.g., 'S11', 'S13'
  course_id INT NOT NULL,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  coordinator_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  is_pinned TINYINT(1) NOT NULL DEFAULT 0,
  due_date DATE NULL, -- e.g., 'Due on July 28, 2027'
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (coordinator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE teams 
  ADD COLUMN section_id INT NOT NULL AFTER thesis_title,
  ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected', 'coordinator-created') NOT NULL DEFAULT 'pending' AFTER adviser_id,
  ADD COLUMN progress_status ENUM('on track', 'falling behind') NOT NULL DEFAULT 'on track' AFTER approval_status,
  ADD COLUMN submission_date DATE NULL AFTER defense_date, -- Tracks 'Submitted on: July 28, 2027'
  ADD CONSTRAINT fk_teams_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE RESTRICT;

  ALTER TABLE users 
  ADD COLUMN adviser_thesis_load INT NOT NULL DEFAULT 0,
  ADD COLUMN adviser_lecture_load INT NOT NULL DEFAULT 0,
  ADD COLUMN adviser_research_load INT NOT NULL DEFAULT 0;