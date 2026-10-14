-- ============================================================
-- Κοινωνικό Δίκτυο Συνταγών - Σχήμα Βάσης Δεδομένων
-- ============================================================
-- Εκτέλεση: mysql -u root -p < schema.sql
-- ή μέσω phpMyAdmin: Import αυτού του αρχείου
-- ============================================================

DROP DATABASE IF EXISTS recipe_network;
CREATE DATABASE recipe_network CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE recipe_network;

-- ----------------------------
-- Πίνακας Χρηστών
-- ----------------------------
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    email           VARCHAR(100) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    full_name       VARCHAR(100),
    bio             TEXT,
    avatar          VARCHAR(255) DEFAULT 'default_avatar.png',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- ----------------------------
-- Πίνακας Συνταγών
-- ----------------------------
CREATE TABLE recipes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    title           VARCHAR(150) NOT NULL,
    description     TEXT,
    ingredients     TEXT NOT NULL,
    steps           TEXT NOT NULL,
    image           VARCHAR(255),
    category        VARCHAR(50),
    prep_time       INT, -- σε λεπτά
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    FULLTEXT INDEX idx_search (title, description, ingredients)
) ENGINE=InnoDB;

-- ----------------------------
-- Πίνακας Σχολίων
-- ----------------------------
CREATE TABLE comments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    recipe_id       INT NOT NULL,
    content         TEXT NOT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    INDEX idx_recipe (recipe_id)
) ENGINE=InnoDB;

-- ----------------------------
-- Πίνακας Likes (Many-to-Many)
-- ----------------------------
CREATE TABLE likes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    recipe_id       INT NOT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (user_id, recipe_id),
    INDEX idx_recipe (recipe_id)
) ENGINE=InnoDB;

-- ----------------------------
-- Παραδείγματα Δεδομένων (Demo)
-- ----------------------------
-- Κωδικός για όλους τους demo χρήστες: password123
-- Hash παραγμένο με password_hash('password123', PASSWORD_BCRYPT)
INSERT INTO users (username, email, password_hash, full_name, bio) VALUES
('maria_chef', 'maria@example.com', '$2b$10$jVTK36XLQQVWx9BWHS0oi.Iq7XhIuyvm2QixvDKxKl/NPpWoYCoky', 'Μαρία Παπαδοπούλου', 'Λάτρης της παραδοσιακής ελληνικής κουζίνας.'),
('nikos_baker', 'nikos@example.com', '$2b$10$9l0ioftz5xIcU3y82mGL9.IDhyTkQWZFCM4RDx6wU4hFQkQMv9qAq', 'Νίκος Δημητρίου', 'Αρτοποιός και ζαχαροπλάστης.'),
('elena_vegan', 'elena@example.com', '$2b$10$p3uV76a4dkM58Uezr6Dn5O4rr3wSmGB9/Pyo.nS5xEW.k8FO5k9zG', 'Έλενα Κωνσταντίνου', 'Vegan συνταγές με αγάπη.');

INSERT INTO recipes (user_id, title, description, ingredients, steps, category, prep_time) VALUES
(1, 'Μουσακάς Παραδοσιακός', 'Η αυθεντική συνταγή της γιαγιάς μου για μουσακά.',
'500γρ κιμάς μοσχαρίσιος\n3 μελιτζάνες\n3 πατάτες\n2 κρεμμύδια\n400γρ τομάτα\nΕλαιόλαδο\nΜπεσαμέλ',
'1. Τηγανίζουμε τις μελιτζάνες και τις πατάτες.\n2. Σοτάρουμε το κρεμμύδι και προσθέτουμε τον κιμά.\n3. Προσθέτουμε τομάτα και αφήνουμε να βράσει.\n4. Στρώνουμε σε ταψί.\n5. Φτιάχνουμε μπεσαμέλ.\n6. Ψήνουμε στους 180°C για 45 λεπτά.',
'Κυρίως πιάτο', 90),
(2, 'Τσουρέκι Πασχαλινό', 'Αφράτο και αρωματικό τσουρέκι.',
'1κιλό αλεύρι\n200γρ ζάχαρη\n200γρ βούτυρο\n3 αυγά\n300ml γάλα\nΜαστίχα και μαχλέπι\nΜαγιά',
'1. Διαλύουμε τη μαγιά.\n2. Ανακατεύουμε όλα τα υλικά.\n3. Ζυμώνουμε για 15 λεπτά.\n4. Αφήνουμε να φουσκώσει 2 ώρες.\n5. Πλέκουμε και ψήνουμε στους 170°C για 35 λεπτά.',
'Γλυκό', 240),
(3, 'Σαλάτα Quinoa', 'Vegan, υγιεινή και χορταστική.',
'1 φλιτζάνι κινόα\nΝτοματίνια\nΑγγούρι\nΚόκκινο κρεμμύδι\nΛεμόνι\nΕλαιόλαδο\nΦρέσκος μαϊντανός',
'1. Βράζουμε την κινόα.\n2. Κόβουμε τα λαχανικά.\n3. Ανακατεύουμε όλα τα υλικά.\n4. Σερβίρουμε κρύα.',
'Σαλάτα', 25);

INSERT INTO comments (user_id, recipe_id, content) VALUES
(2, 1, 'Καταπληκτική συνταγή! Την δοκίμασα και άρεσε σε όλους.'),
(3, 1, 'Μπορώ να την φτιάξω vegan εκδοχή;'),
(1, 2, 'Τέλειο τσουρέκι, ευχαριστούμε!');

INSERT INTO likes (user_id, recipe_id) VALUES
(2, 1), (3, 1), (1, 2), (3, 2), (1, 3), (2, 3);
