CREATE DATABASE IF NOT EXISTS utracksis_db;
USE utracksis_db;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS consultations;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS milestones;
DROP TABLE IF EXISTS thesis_groups;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'adviser', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE thesis_groups (
    group_id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(100) NOT NULL,
    thesis_title VARCHAR(255) NOT NULL,
    adviser_id INT,
    status VARCHAR(50) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (adviser_id) REFERENCES users(user_id)
);

CREATE TABLE milestones (
    milestone_id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    milestone_name VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL,
    due_date DATE NOT NULL,
    submitted_at DATE NULL,
    comments TEXT,
    FOREIGN KEY (group_id) REFERENCES thesis_groups(group_id)
);

CREATE TABLE submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    file_name VARCHAR(150) NOT NULL,
    status VARCHAR(50) NOT NULL,
    adviser_feedback TEXT NULL,
    submitted_at DATETIME NOT NULL,
    FOREIGN KEY (group_id) REFERENCES thesis_groups(group_id)
);

CREATE TABLE consultations (
    consultation_id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    consultation_date DATETIME NOT NULL,
    consultation_end DATETIME NULL,
    meeting_link VARCHAR(255),
    notes TEXT,
    reschedule_reason TEXT NULL,
    status VARCHAR(50) NOT NULL,
    FOREIGN KEY (group_id) REFERENCES thesis_groups(group_id)
);

CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

INSERT INTO users (full_name, email, password, role) VALUES
('Admin User', 'admin@utracksis.test', 'admin123', 'admin'),
('Dr. Maria Santos', 'maria.santos@utracksis.test', 'adviser123', 'adviser'),
('Prof. Carlo Reyes', 'carlo.reyes@utracksis.test', 'adviser123', 'adviser'),
('Ana Dela Cruz', 'ana.delacruz@utracksis.test', 'student123', 'student'),
('Miguel Ramos', 'miguel.ramos@utracksis.test', 'student123', 'student');

INSERT INTO thesis_groups (group_name, thesis_title, adviser_id, status) VALUES
('Group A', 'U-Tracksis Adviser Monitoring System', 2, 'Active'),
('Group B', 'Campus Event Reservation Portal', 2, 'Active'),
('Group C', 'Library Queue Management System', 3, 'Active'),
('Group D', 'Student Clearance Tracking Platform', 3, 'Revision'),
('Group E', 'Faculty Consultation Scheduler', 2, 'Active');

INSERT INTO milestones (group_id, milestone_name, status, due_date, submitted_at, comments) VALUES
(1, 'Proposal Defense', 'Completed', '2026-03-15', '2026-03-12', 'Defense completed.'),
(2, 'Chapters 1-3', 'In Progress', '2026-04-18', '2026-04-16', 'Needs more related studies.'),
(3, 'Data Collection', 'Pending', '2026-05-20', NULL, 'Waiting for adviser approval.'),
(4, 'Chapter 4 Draft', 'Revision', '2026-05-30', '2026-05-22', 'Revise analysis section.'),
(5, 'Final Manuscript', 'Pending', '2026-06-15', NULL, 'Not yet submitted.');

INSERT INTO submissions (group_id, title, file_name, status, adviser_feedback, submitted_at) VALUES
(1, 'Proposal Manuscript', 'group-a-proposal.pdf', 'Approved', 'Ready for next milestone.', '2026-03-12 09:30:00'),
(2, 'Chapter 2 Draft', 'group-b-chapter-2.pdf', 'Revision', 'Add clearer synthesis of related studies.', '2026-04-16 14:00:00'),
(3, 'Research Instrument', 'group-c-instrument.pdf', 'Pending', NULL, '2026-05-02 10:15:00'),
(4, 'Chapter 4 Draft', 'group-d-chapter-4.pdf', 'Rejected', 'Results are incomplete. Re-upload a corrected draft.', '2026-05-22 16:45:00'),
(5, 'Consultation Summary', 'group-e-summary.pdf', 'Approved', 'Summary is accepted.', '2026-05-25 11:20:00');

INSERT INTO consultations (group_id, consultation_date, consultation_end, meeting_link, notes, reschedule_reason, status) VALUES
(1, '2026-08-28 13:00:00', '2026-08-28 14:00:00', 'https://meet.example/group-a', 'Discuss final revisions.', NULL, 'Scheduled'),
(2, '2026-08-29 10:00:00', '2026-08-29 11:00:00', 'https://meet.example/group-b', 'Review Chapter 2 synthesis.', NULL, 'Scheduled'),
(3, '2026-05-30 15:30:00', '2026-05-30 16:30:00', 'https://meet.example/group-c', 'Clarify the research instruments and upload the revised survey questions.', NULL, 'Completed'),
(4, '2026-06-01 09:00:00', '2026-06-01 10:00:00', 'https://meet.example/group-d', 'Review the citations and revise these sections before the next submission.', NULL, 'Completed'),
(5, '2026-06-02 14:00:00', '2026-06-02 15:00:00', 'https://meet.example/group-e', 'Prepare the next milestone checklist before the final manuscript upload.', NULL, 'Completed');

INSERT INTO notifications (user_id, title, message, is_read) VALUES
(2, 'New submission', 'Group A uploaded a proposal manuscript.', 0),
(2, 'Consultation reminder', 'Group B consultation is scheduled tomorrow.', 0),
(3, 'Milestone update', 'Group C updated their data collection milestone.', 1),
(3, 'Revision needed', 'Group D needs feedback on Chapter 4.', 0),
(2, 'Approved submission', 'Group E consultation summary was approved.', 1);
