# uni_record
Simple attendance failure mitigations
## 1. create table attendance_db and paste this
CREATE TABLE student_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_no VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE course_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id VARCHAR(20) UNIQUE,
    title VARCHAR(50),
    credit INT
);

CREATE TABLE faculty_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) UNIQUE,
    name VARCHAR(100),
    password VARCHAR(50)
);

CREATE TABLE session_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT,
    semester INT NOT NULL,
    UNIQUE (year, semester)
);

CREATE TABLE course_registration (
    student_id VARCHAR(255) NOT NULL,
    course_id VARCHAR(255) NOT NULL,
    session_id VARCHAR(10) NOT NULL,
    current_course VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    PRIMARY KEY (student_id, course_id, session_id)
);

CREATE TABLE course_allotment (
    faculty_id VARCHAR(50),
    course_id VARCHAR(20),
    session_id INT,
    PRIMARY KEY (faculty_id, course_id, session_id)
);

CREATE TABLE attendance_details (
     id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id VARCHAR(255) NOT NULL,
    course_id VARCHAR(255) NOT NULL,
    session_id VARCHAR(10) NOT NULL,
    student_id VARCHAR(255) NOT NULL,
    on_date DATE NOT NULL,
    status VARCHAR(50) NOT NULL,
    FOREIGN KEY (faculty_id) REFERENCES faculty_details(student_id),
    FOREIGN KEY (student_id) REFERENCES student_details(student_id)
);

## 2. if you want to update directly on sql run this to automatically propagate records first
DELIMITER $$

CREATE TRIGGER update_student_id
AFTER UPDATE ON student_details
FOR EACH ROW
BEGIN
    UPDATE faculty_details
    SET student_id = NEW.id
    WHERE student_id = OLD.id;

    UPDATE course_registration
    SET student_id = NEW.id
    WHERE student_id = OLD.id;

    UPDATE attendance_details
    SET student_id = NEW.id
    WHERE student_id = OLD.id;
END $$

DELIMITER $$

CREATE TRIGGER update_course_id
AFTER UPDATE ON course_details
FOR EACH ROW
BEGIN
    UPDATE course_registration
    SET course_id = NEW.course_id
    WHERE course_id = OLD.course_id;

    UPDATE course_allotment
    SET course_id = NEW.course_id
    WHERE course_id = OLD.course_id;

    UPDATE attendance_details
    SET course_id = NEW.course_id
    WHERE course_id = OLD.course_id;
END $$

DELIMITER $$

CREATE TRIGGER update_session_id
AFTER UPDATE ON session_details
FOR EACH ROW
BEGIN
    UPDATE course_registration
    SET session_id = NEW.id
    WHERE session_id = OLD.id;

    UPDATE course_allotment
    SET session_id = NEW.id
    WHERE session_id = OLD.id;

    UPDATE attendance_details
    SET session_id = NEW.id
    WHERE session_id = OLD.id;
END $$

DELIMITER ;
