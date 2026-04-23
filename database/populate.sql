-- Safe to re-run: clears all data and resets AUTOINCREMENT counters first
PRAGMA foreign_keys = OFF;
DELETE FROM member_subscription;
DELETE FROM personal_training_session;
DELETE FROM gym_visit;
DELETE FROM facility_reservation;
DELETE FROM facility;
DELETE FROM equipment_reservation;
DELETE FROM equipment_unit;
DELETE FROM equipment;
DELETE FROM review;
DELETE FROM enrollment;
DELETE FROM class_session;
DELETE FROM class;
DELETE FROM class_type;
DELETE FROM trainer_profile;
DELETE FROM membership_plan;
DELETE FROM user;
DELETE FROM sqlite_sequence;
PRAGMA foreign_keys = ON;


-- ========================================================================================================================
-- CORE FEATURES CURRENTLY USED IN THE APP
-- ========================================================================================================================


-- ============================================================
-- USERS
-- Passwords: NormalTest1! / TrainerTest1! / AdminTest1! / Member2Test1!
-- ============================================================
INSERT INTO user (name, username, email, password_hash, role) VALUES
    ('Normal User',   'normaluser',  'normal@gmail.com',  '$2y$12$5WlMPEie.uE7qRwWQDSWzeYLrlXWtfbpPviuh5jwa5gRJ7MMoAYSi', 'member'),
    ('Trainer User',  'traineruser', 'trainer@gmail.com', '$2y$12$EaWvzlwLi4d3nl7JSy8ZFuiKIDUgUQZoOayYtcvCyHgXi0JyAiMES', 'trainer'),
    ('Admin User',    'adminuser',   'admin@gmail.com',   '$2y$12$M8bgiimX4JSAQ43Y7lcKSOi.3Vc7IqalZxWTPzWmhhhmshzW3HbHu', 'admin'),
    ('Member Two',    'membertwo',   'member2@gmail.com', '$2y$12$TNGOaPId2WaZRqcgugHHaOqu9EErBKhiXoxTD7CXbl1NpHtyyk5RC', 'member');


-- ============================================================
-- TRAINER PROFILE  (user_id 2 = trainer@gmail.com)
-- ============================================================
INSERT INTO trainer_profile (user_id, bio, specializations, certifications) VALUES
    (2, 'Certified fitness coach with 8 years of experience.', 'HIIT, Yoga, Cardio', 'ACE Personal Trainer, RYT-200');


-- ============================================================
-- CLASS TYPES
-- ============================================================
INSERT INTO class_type (name) VALUES
    ('Yoga'),
    ('HIIT'),
    ('Cardio');


-- ============================================================
-- CLASSES  (trainer_id references user_id 2)
-- ============================================================
INSERT INTO class (name, type_id, description, duration_minutes, intensity, trainer_id) VALUES
    ('Morning Yoga',  1, 'A calm morning flow to improve flexibility and mindfulness.', 60, 2, 2),
    ('HIIT Blast',    2, 'High-intensity interval training to torch calories fast.',    45, 4, 2),
    ('Cardio Burn',   3, 'Steady-state cardio session suitable for all fitness levels.', 30, 3, 2);


-- ============================================================
-- CLASS SESSIONS  (spread across 2026-04-21 to 2026-05-02)
-- ============================================================
INSERT INTO class_session (class_id, datetime, room, capacity) VALUES
    (1, '2026-04-22 08:00:00', 'Room A', 15),  -- id 1
    (1, '2026-04-29 08:00:00', 'Room A', 15),  -- id 2

    (2, '2026-04-23 18:00:00', 'Room B', 12),  -- id 3
    (2, '2026-04-30 18:00:00', 'Room B', 12),  -- id 4

    (3, '2026-04-25 10:00:00', 'Room C', 20),  -- id 5
    (3, '2026-05-02 10:00:00', 'Room C', 20),  -- id 6

    -- capacity=1: normal@gmail.com fills it; member2@gmail.com is waitlisted
    (2, '2026-04-24 18:00:00', 'Room B', 1);   -- id 7


-- ============================================================
-- ENROLLMENT
-- ============================================================
INSERT INTO enrollment (member_id, session_id, status) VALUES
    (1, 1, 'enrolled'),   -- normal enrolled in Morning Yoga (Wed 22 Apr)
    (1, 3, 'enrolled'),   -- normal enrolled in HIIT Blast (Thu 23 Apr)
    (1, 7, 'enrolled'),   -- normal fills the capacity-1 HIIT session (Thu 24 Apr)
    (4, 7, 'waitlisted'); -- member2 is waitlisted — session is full


-- ============================================================
-- REVIEW  (normal@gmail.com reviewed class 1 - Morning Yoga)
-- ============================================================
INSERT INTO review (class_id, member_id, rating, comment) VALUES
    (1, 1, 5, 'Amazing session, felt great afterwards!');


-- ============================================================
-- EQUIPMENT
-- ============================================================
INSERT INTO equipment (name, type, description) VALUES
    ('Treadmill',    'Cardio',    'Electric treadmill with incline settings.'),
    ('Barbell Set',  'Weightlifting', '20 kg Olympic barbell with plate rack.');


-- ============================================================
-- EQUIPMENT UNITS
-- ============================================================
INSERT INTO equipment_unit (equipment_id, identifier, status) VALUES
    (1, 'TRD-01', 'available'),
    (1, 'TRD-02', 'maintenance'),
    (2, 'BAR-01', 'available');


-- ========================================================================================================================
-- EXTRA FEATURES PLANNED / PARTIALLY INTEGRATED
-- Seeded now so the data model is ready for later development
-- ========================================================================================================================


-- ============================================================
-- EQUIPMENT RESERVATION  (normal@gmail.com reserves TRD-01)
-- ============================================================
INSERT INTO equipment_reservation (member_id, unit_id, start_datetime, end_datetime) VALUES
    (1, 1, '2026-04-22 09:00:00', '2026-04-22 09:30:00');


-- ============================================================
-- FACILITIES
-- ============================================================
INSERT INTO facility (name, description, max_occupancy) VALUES
    ('Jacuzzi',      'Heated jacuzzi pool on the second floor.', 6),
    ('Turkish Bath', 'Traditional steam bath for relaxation.',   8);


-- ============================================================
-- FACILITY RESERVATION  (normal@gmail.com reserves Jacuzzi)
-- ============================================================
INSERT INTO facility_reservation (member_id, facility_id, start_datetime, end_datetime) VALUES
    (1, 1, '2026-04-22 10:00:00', '2026-04-22 10:30:00');


-- ============================================================
-- GYM VISIT  (normal@gmail.com — completed visit)
-- ============================================================
INSERT INTO gym_visit (member_id, entered_at, left_at, status) VALUES
    (1, '2026-04-21 09:00:00', '2026-04-21 10:30:00', 'left');


-- ============================================================
-- PERSONAL TRAINING SESSION  (normal with trainer, next Tuesday)
-- ============================================================
INSERT INTO personal_training_session (member_id, trainer_id, datetime, duration_minutes, status) VALUES
    (1, 2, '2026-04-28 11:00:00', 60, 'confirmed');


-- ============================================================
-- MEMBERSHIP PLANS
-- ============================================================
INSERT INTO membership_plan (name, price, description, max_classes_per_month) VALUES
    ('Basic',   19.99, 'Access to gym floor and cardio equipment.',         NULL),
    ('Premium', 39.99, 'Unlimited classes + equipment and facility access.', NULL);


-- ============================================================
-- MEMBER SUBSCRIPTION  (normal@gmail.com on Premium, active)
-- ============================================================
INSERT INTO member_subscription (member_id, plan_id, start_date, end_date, status) VALUES
    (1, 2, '2026-04-01', '2026-05-01', 'active');
