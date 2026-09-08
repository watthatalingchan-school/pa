CREATE DATABASE IF NOT EXISTS pa_system DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pa_system;

CREATE TABLE school (
  id TINYINT PRIMARY KEY DEFAULT 1,
  school_name VARCHAR(255) NOT NULL,
  affiliation VARCHAR(255) NOT NULL,
  logo VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;
INSERT INTO school VALUES (1,'โรงเรียนตัวอย่างวิทยา','สำนักงานเขตพื้นที่การศึกษามัธยมศึกษา',NULL);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  fullname VARCHAR(255) NOT NULL,
  avatar VARCHAR(255) DEFAULT NULL,
  role ENUM('admin','director','deputy','teacher') NOT NULL DEFAULT 'teacher',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE fiscal_years (
  id INT AUTO_INCREMENT PRIMARY KEY,
  year_name VARCHAR(20) NOT NULL UNIQUE,
  is_current TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  year_id INT NOT NULL,
  doc_type ENUM('pa1','annual','present') NOT NULL,
  file_path VARCHAR(255) DEFAULT NULL,
  link_url VARCHAR(500) DEFAULT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  reason TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_sub (user_id, year_id, doc_type),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (year_id) REFERENCES fiscal_years(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ข้อมูลเริ่มต้น (รหัสผ่านทุกคน: 123456)
INSERT INTO fiscal_years (year_name,is_current) VALUES ('2567',0),('2568',0),('2569',1);
INSERT INTO users (username,password,fullname,role) VALUES
('admin','$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HxQ8k5jJpTS0VZm9y2QbBBDN8yGZ2y','ผู้ดูแลระบบ','admin'),
('director','$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HxQ8k5jJpTS0VZm9y2QbBBDN8yGZ2y','นายสมชาย ใจดี','director'),
('deputy1','$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HxQ8k5jJpTS0VZm9y2QbBBDN8yGZ2y','นางสาวสมหญิง รักงาน','deputy'),
('teacher1','$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HxQ8k5jJpTS0VZm9y2QbBBDN8yGZ2y','นายวิชัย สอนดี','teacher');