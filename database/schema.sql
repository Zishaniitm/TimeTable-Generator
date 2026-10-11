-- StudySync database schema (MySQL 5.7+ / MariaDB 10.3+)
-- CREATE DATABASE IF NOT EXISTS studysync CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE studysync;

CREATE TABLE IF NOT EXISTS users (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email          VARCHAR(190) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  display_name   VARCHAR(100) NULL,
  study_days     VARCHAR(20)  NOT NULL DEFAULT '0,1,2,3,4,5,6',
  day_start_hour TINYINT UNSIGNED NOT NULL DEFAULT 16,
  day_end_hour   TINYINT UNSIGNED NOT NULL DEFAULT 22,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subjects (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id        INT UNSIGNED NOT NULL,
  name           VARCHAR(100) NOT NULL,
  color          CHAR(7) NOT NULL DEFAULT '#2f7fb5',
  hours_per_week TINYINT UNSIGNED NOT NULL DEFAULT 3,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX subjects_user_idx (user_id),
  CONSTRAINT fk_subjects_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS timetable_slots (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED NOT NULL,
  subject_id   INT UNSIGNED NOT NULL,
  day_of_week  TINYINT UNSIGNED NOT NULL,          -- 0 = Sunday ... 6 = Saturday
  start_hour   TINYINT UNSIGNED NOT NULL,          -- 0-23
  is_completed TINYINT(1) NOT NULL DEFAULT 0,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX slots_user_idx (user_id),
  CONSTRAINT fk_slots_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_slots_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;
