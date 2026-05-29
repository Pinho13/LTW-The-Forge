PRAGMA journal_mode = WAL;
PRAGMA foreign_keys = ON;


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
    phone         VARCHAR,
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
    is_featured     BOOLEAN NOT NULL DEFAULT 0 CHECK (is_featured IN (0, 1)),
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);


-- ============================================================
-- CLASS TYPE
-- ============================================================
CREATE TABLE IF NOT EXISTS class_type (
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR NOT NULL UNIQUE
);

-- ============================================================
-- CLASS
-- ============================================================
CREATE TABLE IF NOT EXISTS class (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    name             VARCHAR NOT NULL,
    type_id          INTEGER,
    description      TEXT,
    duration_minutes INTEGER NOT NULL CHECK (duration_minutes > 0),
    intensity        INTEGER NOT NULL CHECK (intensity BETWEEN 1 AND 5),
    trainer_id       INTEGER,
    is_featured      BOOLEAN NOT NULL DEFAULT 0 CHECK (is_featured IN (0, 1)),
    FOREIGN KEY (type_id)    REFERENCES class_type(id) ON DELETE RESTRICT,
    FOREIGN KEY (trainer_id) REFERENCES user(user_id)  ON DELETE RESTRICT
);

-- ============================================================
-- CLASS SESSION
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
-- Waitlist is PHP-managed via enrolled_at ordering.
-- PHP promotes 'waitlisted' -> 'enrolled' when a spot opens.
-- ============================================================
CREATE TABLE IF NOT EXISTS enrollment (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id   INTEGER NOT NULL,
    session_id  INTEGER NOT NULL,
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status      VARCHAR NOT NULL DEFAULT 'enrolled'
                    CHECK (status IN ('enrolled', 'cancelled', 'waitlisted', 'completed', 'missed')),
    UNIQUE (member_id, session_id),
    FOREIGN KEY (member_id)  REFERENCES user(user_id)     ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES class_session(id) ON DELETE CASCADE
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
    FOREIGN KEY (class_id)  REFERENCES class(id)     ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES user(user_id) ON DELETE CASCADE
);

-- ============================================================
-- EQUIPMENT (category/type of machine in the main gym area)
-- ============================================================
CREATE TABLE IF NOT EXISTS equipment (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        VARCHAR NOT NULL,
    type        VARCHAR,
    description TEXT,
    photo       VARCHAR,
    default_w   INTEGER NOT NULL DEFAULT 55,
    default_h   INTEGER NOT NULL DEFAULT 40
);

-- ============================================================
-- EQUIPMENT UNIT (each physical machine; implicit capacity = 1)
-- ============================================================
CREATE TABLE IF NOT EXISTS equipment_unit (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    equipment_id INTEGER NOT NULL,
    identifier   VARCHAR,
    status       VARCHAR NOT NULL DEFAULT 'available'
                     CHECK (status IN ('available', 'maintenance', 'retired')),
    map_x        INTEGER,
    map_y        INTEGER,
    map_w        INTEGER,
    map_h        INTEGER,
    rotation     INTEGER NOT NULL DEFAULT 0 CHECK (rotation IN (0, 90, 180, 270)),
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE RESTRICT
);


-- ============================================================
-- EQUIPMENT RESERVATION
-- ============================================================
CREATE TABLE IF NOT EXISTS equipment_reservation (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id      INTEGER NOT NULL,
    unit_id        INTEGER NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime   DATETIME NOT NULL,
    CHECK (end_datetime > start_datetime),
    FOREIGN KEY (member_id) REFERENCES user(user_id)      ON DELETE CASCADE,
    FOREIGN KEY (unit_id)   REFERENCES equipment_unit(id) ON DELETE RESTRICT
);

-- ============================================================
-- CLASS ROOMS
-- ============================================================
CREATE TABLE IF NOT EXISTS class_room (
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR NOT NULL UNIQUE
);

-- ============================================================
-- FACILITY (rooms/amenities with shared capacity)
-- e.g. jacuzzi, turkish bath, bike room
-- PHP enforces concurrent active reservations <= max_occupancy.
-- ============================================================
CREATE TABLE IF NOT EXISTS facility (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          VARCHAR NOT NULL,
    description   TEXT,
    max_occupancy INTEGER NOT NULL CHECK (max_occupancy > 0)
);

-- ============================================================
-- FACILITY RESERVATION
-- ============================================================
CREATE TABLE IF NOT EXISTS facility_reservation (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id      INTEGER NOT NULL,
    facility_id    INTEGER NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime   DATETIME NOT NULL,
    CHECK (end_datetime > start_datetime),
    FOREIGN KEY (member_id)  REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (facility_id) REFERENCES facility(id) ON DELETE RESTRICT
);

-- ============================================================
-- GYM VISIT
-- left_at NULL + status = 'in_gym' -> member is currently inside.
-- PHP derives weekly streak and session counts from this table.
-- ============================================================
CREATE TABLE IF NOT EXISTS gym_visit (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id  INTEGER NOT NULL,
    entered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    left_at    DATETIME,
    status     VARCHAR NOT NULL DEFAULT 'in_gym'
                   CHECK (status IN ('in_gym', 'left')),
    CHECK (left_at IS NULL OR left_at > entered_at),
    FOREIGN KEY (member_id) REFERENCES user(user_id) ON DELETE CASCADE
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
    status           VARCHAR NOT NULL DEFAULT 'pending'
                         CHECK (status IN ('pending', 'confirmed', 'cancelled')),
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
    status       VARCHAR NOT NULL DEFAULT 'active'
                     CHECK (status IN ('active', 'expired', 'cancelled', 'frozen')),
    frozen_until DATE,
    CHECK (end_date > start_date),
    FOREIGN KEY (member_id) REFERENCES user(user_id)       ON DELETE CASCADE,
    FOREIGN KEY (plan_id)   REFERENCES membership_plan(id) ON DELETE RESTRICT
);

-- ============================================================
-- ANNOUNCEMENT
-- ============================================================
CREATE TABLE IF NOT EXISTS announcement (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    title      VARCHAR NOT NULL,
    body       TEXT NOT NULL,
    author_id  INTEGER NOT NULL,
    pinned     BOOLEAN NOT NULL DEFAULT 0 CHECK (pinned IN (0, 1)),
    type       VARCHAR NOT NULL DEFAULT 'Gym News',
    read_time  INTEGER NOT NULL DEFAULT 1,
    image      VARCHAR,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES user(user_id) ON DELETE RESTRICT
);

-- ============================================================
-- ADMIN LOG
-- ============================================================
CREATE TABLE IF NOT EXISTS admin_log (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id    INTEGER NOT NULL,
    action_type VARCHAR NOT NULL CHECK (action_type IN ('CREATE','UPDATE','DELETE','LOGIN','ELEVATE','ASSIGN')),
    description TEXT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES user(user_id) ON DELETE CASCADE
);

-- ============================================================
-- INDEXES
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_user_email           ON user(email);
CREATE INDEX IF NOT EXISTS idx_user_role            ON user(role);
CREATE INDEX IF NOT EXISTS idx_session_datetime     ON class_session(datetime);
CREATE INDEX IF NOT EXISTS idx_session_class        ON class_session(class_id);
CREATE INDEX IF NOT EXISTS idx_class_type           ON class(type_id);
CREATE INDEX IF NOT EXISTS idx_enrollment_member    ON enrollment(member_id);
CREATE INDEX IF NOT EXISTS idx_enrollment_session   ON enrollment(session_id);
CREATE INDEX IF NOT EXISTS idx_review_class         ON review(class_id);
CREATE INDEX IF NOT EXISTS idx_unit_equipment       ON equipment_unit(equipment_id);
CREATE INDEX IF NOT EXISTS idx_eq_reservation_unit  ON equipment_reservation(unit_id);
CREATE INDEX IF NOT EXISTS idx_facility_reservation ON facility_reservation(facility_id);
CREATE INDEX IF NOT EXISTS idx_gym_visit_member     ON gym_visit(member_id);
CREATE INDEX IF NOT EXISTS idx_gym_visit_status     ON gym_visit(status);
CREATE INDEX IF NOT EXISTS idx_admin_log_admin      ON admin_log(admin_id);
CREATE INDEX IF NOT EXISTS idx_admin_log_created    ON admin_log(created_at);
