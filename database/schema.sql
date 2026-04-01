PRAGMA foreign_keys = ON;
PRAGMA journal_mode = WAL;


-- ============================================================
-- USER
-- ============================================================
CREATE TABLE IF NOT EXISTS user (
    user_id       INTEGER PRIMARY KEY AUTOINCREMENT,
    name          VARCHAR NOT NULL,
    username      VARCHAR NOT NULL UNIQUE,
    email         VARCHAR NOT NULL UNIQUE,
    password_hash VARCHAR NOT NULL,
    profile_photo VARCHAR,
    is_active     BOOLEAN NOT NULL DEFAULT 1 CHECK (is_active IN (0, 1)),
    role          VARCHAR NOT NULL CHECK (role IN ('member', 'trainer', 'admin')),
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ============================================================
-- TRAINER PROFILE
-- ============================================================
CREATE TABLE IF NOT EXISTS trainer_profile (
    trainer_id      INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL UNIQUE,
    bio             TEXT,
    specializations VARCHAR,
    certifications  VARCHAR,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);



-- ============================================================
-- CLASS TYPE (managed by admins) > e.g., Yoga, Spinning, HIIT
-- ============================================================
CREATE TABLE IF NOT EXISTS class_type (
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR NOT NULL UNIQUE
);

-- ============================================================
-- CLASS (template for scheduled sessions) -
-- Aula De Yoga da Manha, prof pefro, 60 min, max 20 pessoas, do tipo Yoga
-- ============================================================
CREATE TABLE IF NOT EXISTS class (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    name             VARCHAR NOT NULL,
    type_id          INTEGER,
    description      TEXT,
    duration_minutes INTEGER NOT NULL CHECK (duration_minutes > 0),
    intensity        INTEGER NOT NULL CHECK (intensity BETWEEN 1 AND 5),
    trainer_id       INTEGER,
    FOREIGN KEY (type_id)    REFERENCES class_type(id) ON DELETE RESTRICT,
    FOREIGN KEY (trainer_id) REFERENCES user(user_id)  ON DELETE RESTRICT
);

-- ============================================================
-- CLASS SESSION (scheduled occurrence of a class)
-- ============================================================
CREATE TABLE IF NOT EXISTS class_session (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    datetime DATETIME NOT NULL,
    room     VARCHAR NOT NULL,
    capacity INTEGER NOT NULL CHECK (capacity > 0),
    FOREIGN KEY (class_id) REFERENCES class(id) ON DELETE RESTRICT
);

-- ============================================================
-- ENROLLMENT
-- ============================================================
CREATE TABLE IF NOT EXISTS enrollment (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id   INTEGER NOT NULL,
    session_id  INTEGER NOT NULL,
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status      VARCHAR NOT NULL DEFAULT 'enrolled' CHECK (status IN ('enrolled', 'cancelled')),
    UNIQUE (member_id, session_id),
    FOREIGN KEY (member_id)  REFERENCES user(user_id)          ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES class_session(id)      ON DELETE CASCADE
);

-- ============================================================
-- WAITLIST
-- ============================================================
CREATE TABLE IF NOT EXISTS waitlist (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id INTEGER NOT NULL,
    member_id  INTEGER NOT NULL,
    joined_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    position   INTEGER NOT NULL CHECK (position > 0),
    UNIQUE (member_id, session_id),
    FOREIGN KEY (session_id) REFERENCES class_session(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id)  REFERENCES user(user_id)     ON DELETE CASCADE
);

-- ============================================================
-- REVIEW
-- ============================================================
CREATE TABLE IF NOT EXISTS review (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id   INTEGER NOT NULL,
    member_id  INTEGER NOT NULL,
    rating     INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment    TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (member_id, class_id),
    FOREIGN KEY (class_id)  REFERENCES class(id)      ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES user(user_id)  ON DELETE CASCADE
);

-- ============================================================
-- EQUIPMENT (category/type of machine)
-- ============================================================
CREATE TABLE IF NOT EXISTS equipment (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        VARCHAR NOT NULL,
    type        VARCHAR,
    description TEXT
);

-- ============================================================
-- EQUIPMENT UNIT (each physical machine)
-- ============================================================
CREATE TABLE IF NOT EXISTS equipment_unit (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    equipment_id INTEGER NOT NULL,
    identifier   VARCHAR,
    status       VARCHAR NOT NULL DEFAULT 'available' CHECK (status IN ('available', 'maintenance', 'retired')),
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE RESTRICT
);

-- ============================================================
-- EQUIPMENT RESERVATION (reserve a specific unit for a time slot)
-- ============================================================
CREATE TABLE IF NOT EXISTS equipment_reservation (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id      INTEGER NOT NULL,
    unit_id        INTEGER NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime   DATETIME NOT NULL,
    CHECK (end_datetime > start_datetime),
    FOREIGN KEY (member_id) REFERENCES user(user_id)       ON DELETE CASCADE,
    FOREIGN KEY (unit_id)   REFERENCES equipment_unit(id)  ON DELETE RESTRICT
);

-- ============================================================
-- PERSONAL TRAINING SESSION
-- ============================================================
CREATE TABLE IF NOT EXISTS personal_training_session (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id        INTEGER NOT NULL,
    trainer_id       INTEGER NOT NULL,
    datetime         DATETIME NOT NULL,
    duration_minutes INTEGER CHECK (duration_minutes > 0),
    status           VARCHAR NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'cancelled')),
    CHECK (member_id != trainer_id),
    FOREIGN KEY (member_id)  REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES user(user_id) ON DELETE RESTRICT
);

-- ============================================================
-- MEMBERSHIP PLAN
-- ============================================================
CREATE TABLE IF NOT EXISTS membership_plan (
    id                    INTEGER PRIMARY KEY AUTOINCREMENT,
    name                  VARCHAR NOT NULL UNIQUE,
    price                 REAL NOT NULL CHECK (price >= 0),
    description           TEXT,
    max_classes_per_month INTEGER CHECK (max_classes_per_month > 0)
);

-- ============================================================
-- MEMBER SUBSCRIPTION
-- ============================================================
CREATE TABLE IF NOT EXISTS member_subscription (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id  INTEGER NOT NULL,
    plan_id    INTEGER NOT NULL,
    start_date DATE NOT NULL,
    end_date   DATE NOT NULL,
    status     VARCHAR NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'expired', 'cancelled')),
    CHECK (end_date > start_date),
    FOREIGN KEY (member_id) REFERENCES user(user_id)       ON DELETE CASCADE,
    FOREIGN KEY (plan_id)   REFERENCES membership_plan(id) ON DELETE RESTRICT
);

-- ============================================================
-- INDEXES (for common query patterns)
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_user_email        ON user(email);
CREATE INDEX IF NOT EXISTS idx_user_role         ON user(role);
CREATE INDEX IF NOT EXISTS idx_session_datetime  ON class_session(datetime);
CREATE INDEX IF NOT EXISTS idx_session_class      ON class_session(class_id);
CREATE INDEX IF NOT EXISTS idx_class_type         ON class(type_id);
CREATE INDEX IF NOT EXISTS idx_enrollment_member ON enrollment(member_id);
CREATE INDEX IF NOT EXISTS idx_enrollment_session ON enrollment(session_id);
CREATE INDEX IF NOT EXISTS idx_waitlist_session  ON waitlist(session_id);
CREATE INDEX IF NOT EXISTS idx_review_class       ON review(class_id);
CREATE INDEX IF NOT EXISTS idx_unit_equipment     ON equipment_unit(equipment_id);
CREATE INDEX IF NOT EXISTS idx_reservation_unit   ON equipment_reservation(unit_id);
