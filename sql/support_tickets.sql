-- Table structure for support_tickets
CREATE TABLE IF NOT EXISTS support_tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    exam_id INT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    admin_response TEXT NULL,
    status ENUM('pending', 'resolved') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    CONSTRAINT fk_tickets_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    CONSTRAINT fk_tickets_exam FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE SET NULL
) ENGINE=InnoDB;
