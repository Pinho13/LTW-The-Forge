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
-- CLASS SESSIONS
-- Today: 2026-05-11 (Monday)
-- ============================================================
INSERT INTO class_session (class_id, datetime, room, capacity) VALUES
    -- === APRIL 2026 (all past) ===
    (1, '2026-04-22 08:00:00', 'Room A', 15),  -- id 1  Morning Yoga  Tue 22 Apr
    (2, '2026-04-23 18:00:00', 'Room B', 12),  -- id 2  HIIT Blast    Wed 23 Apr
    (2, '2026-04-24 18:00:00', 'Room B',  1),  -- id 3  HIIT Blast    Thu 24 Apr  capacity=1 (full)
    (3, '2026-04-25 10:00:00', 'Room C', 20),  -- id 4  Cardio Burn   Sat 25 Apr
    (1, '2026-04-28 08:00:00', 'Room A', 15),  -- id 5  Morning Yoga  Tue 28 Apr
    (2, '2026-04-29 18:00:00', 'Room B', 12),  -- id 6  HIIT Blast    Wed 29 Apr
    (3, '2026-04-30 10:00:00', 'Room C', 20),  -- id 7  Cardio Burn   Thu 30 Apr

    -- === MAY 2026 — PAST (before 2026-05-11) ===
    (3, '2026-05-02 10:00:00', 'Room C', 20),  -- id 8  Cardio Burn   Fri  2 May
    (1, '2026-05-05 08:00:00', 'Room A', 15),  -- id 9  Morning Yoga  Mon  5 May
    (2, '2026-05-06 18:00:00', 'Room B', 12),  -- id 10 HIIT Blast    Tue  6 May
    (3, '2026-05-07 10:00:00', 'Room C', 20),  -- id 11 Cardio Burn   Wed  7 May
    (1, '2026-05-08 08:00:00', 'Room A', 15),  -- id 12 Morning Yoga  Thu  8 May
    (2, '2026-05-09 18:00:00', 'Room B', 12),  -- id 13 HIIT Blast    Fri  9 May
    (3, '2026-05-10 10:00:00', 'Room C', 20),  -- id 14 Cardio Burn   Sat 10 May

    -- === MAY 2026 — FUTURE ===
    (2, '2026-05-13 18:00:00', 'Room B', 12),  -- id 15 HIIT Blast    Wed 13 May
    (3, '2026-05-15 10:00:00', 'Room C', 20),  -- id 16 Cardio Burn   Fri 15 May
    (1, '2026-05-18 08:00:00', 'Room A', 15),  -- id 17 Morning Yoga  Mon 18 May
    (2, '2026-05-20 18:00:00', 'Room B', 12),  -- id 18 HIIT Blast    Wed 20 May
    (3, '2026-05-22 10:00:00', 'Room C', 20),  -- id 19 Cardio Burn   Fri 22 May
    (1, '2026-05-25 08:00:00', 'Room A', 15),  -- id 20 Morning Yoga  Mon 25 May
    (2, '2026-05-27 18:00:00', 'Room B', 12),  -- id 21 HIIT Blast    Wed 27 May
    (3, '2026-05-29 10:00:00', 'Room C', 20),  -- id 22 Cardio Burn   Fri 29 May

    -- === JUNE 2026 — FUTURE ===
    (1, '2026-06-02 08:00:00', 'Room A', 15),  -- id 23 Morning Yoga  Tue  2 Jun
    (2, '2026-06-04 18:00:00', 'Room B', 12);  -- id 24 HIIT Blast    Thu  4 Jun


-- ============================================================
-- ENROLLMENT
-- normal@gmail.com (id 1):
--   Classes this month (May): ids 8-14 (past) + 15, 17, 19 (future) = 10
--   Upcoming (future enrolled):  15, 17, 19, 23                      =  4
-- ============================================================
INSERT INTO enrollment (member_id, session_id, status) VALUES
    -- normal — April history
    (1,  1, 'enrolled'),   -- Morning Yoga  Tue 22 Apr
    (1,  2, 'enrolled'),   -- HIIT Blast    Wed 23 Apr
    (1,  3, 'enrolled'),   -- HIIT Blast    Thu 24 Apr  (fills capacity=1)
    (4,  3, 'waitlisted'), -- member2 waitlisted — session full
    (1,  4, 'enrolled'),   -- Cardio Burn   Sat 25 Apr
    (1,  5, 'enrolled'),   -- Morning Yoga  Tue 28 Apr
    (1,  6, 'enrolled'),   -- HIIT Blast    Wed 29 Apr
    (1,  7, 'enrolled'),   -- Cardio Burn   Thu 30 Apr

    -- normal — May past  (+7 classes this month)
    (1,  8, 'enrolled'),   -- Cardio Burn   Fri  2 May
    (1,  9, 'enrolled'),   -- Morning Yoga  Mon  5 May
    (1, 10, 'enrolled'),   -- HIIT Blast    Tue  6 May
    (1, 11, 'enrolled'),   -- Cardio Burn   Wed  7 May
    (1, 12, 'enrolled'),   -- Morning Yoga  Thu  8 May
    (1, 13, 'enrolled'),   -- HIIT Blast    Fri  9 May
    (1, 14, 'enrolled'),   -- Cardio Burn   Sat 10 May

    -- normal — May future  (+3 classes this month, +3 upcoming)
    (1, 15, 'enrolled'),   -- HIIT Blast    Wed 13 May
    (1, 17, 'enrolled'),   -- Morning Yoga  Mon 18 May
    (1, 19, 'enrolled'),   -- Cardio Burn   Fri 22 May

    -- normal — June future  (+1 upcoming, not this month)
    (1, 23, 'enrolled'),   -- Morning Yoga  Tue  2 Jun

    -- member2 — a few future sessions
    (4, 16, 'enrolled'),   -- Cardio Burn   Fri 15 May
    (4, 18, 'enrolled'),   -- HIIT Blast    Wed 20 May
    (4, 20, 'enrolled'),   -- Morning Yoga  Mon 25 May
    (4, 24, 'enrolled');   -- HIIT Blast    Thu  4 Jun


-- ============================================================
-- REVIEWS
-- ============================================================
INSERT INTO review (class_id, member_id, rating, comment) VALUES
    (1, 1, 5, 'Amazing session, felt great afterwards!'),
    (2, 1, 4, 'Intense but worth it. Really pushed my limits.'),
    (3, 4, 3, 'Good session, but the room was a bit warm.');


-- ============================================================
-- GYM VISITS
-- normal@gmail.com — 6 consecutive weeks → streak = 6 (all fires lit)
-- member2@gmail.com — 3 consecutive weeks → streak = 3
-- ============================================================
INSERT INTO gym_visit (member_id, entered_at, left_at, status) VALUES
    -- normal — week of 2026-04-05 (Sun)
    (1, '2026-04-07 09:00:00', '2026-04-07 10:30:00', 'left'),

    -- normal — week of 2026-04-12 (Sun)
    (1, '2026-04-13 08:30:00', '2026-04-13 10:00:00', 'left'),
    (1, '2026-04-16 18:00:00', '2026-04-16 19:15:00', 'left'),

    -- normal — week of 2026-04-19 (Sun)
    (1, '2026-04-21 09:00:00', '2026-04-21 10:30:00', 'left'),
    (1, '2026-04-23 18:00:00', '2026-04-23 19:00:00', 'left'),

    -- normal — week of 2026-04-26 (Sun)
    (1, '2026-04-28 08:30:00', '2026-04-28 10:00:00', 'left'),
    (1, '2026-04-30 18:00:00', '2026-04-30 19:30:00', 'left'),

    -- normal — week of 2026-05-03 (Sun)
    (1, '2026-05-05 09:00:00', '2026-05-05 10:30:00', 'left'),
    (1, '2026-05-07 18:00:00', '2026-05-07 19:00:00', 'left'),

    -- normal — week of 2026-05-10 (Sun) — currently in gym
    (1, '2026-05-11 09:00:00', NULL, 'in_gym'),

    -- member2 — 3 consecutive weeks (streak = 3)
    (4, '2026-04-27 10:00:00', '2026-04-27 11:00:00', 'left'),  -- week of Apr 26
    (4, '2026-05-04 10:00:00', '2026-05-04 11:00:00', 'left'),  -- week of May 3
    (4, '2026-05-11 10:00:00', NULL,                  'in_gym'); -- week of May 10


-- ============================================================
-- EQUIPMENT
-- ============================================================
INSERT INTO equipment (name, type, description) VALUES
    ('Treadmill',    'Cardio',        'Electric treadmill with incline settings.'),
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
-- ========================================================================================================================


-- ============================================================
-- EQUIPMENT RESERVATION
-- ============================================================
INSERT INTO equipment_reservation (member_id, unit_id, start_datetime, end_datetime) VALUES
    (1, 1, '2026-05-12 09:00:00', '2026-05-12 09:30:00');


-- ============================================================
-- FACILITIES
-- ============================================================
INSERT INTO facility (name, description, max_occupancy) VALUES
    ('Jacuzzi',      'Heated jacuzzi pool on the second floor.', 6),
    ('Turkish Bath', 'Traditional steam bath for relaxation.',   8);


-- ============================================================
-- FACILITY RESERVATION
-- ============================================================
INSERT INTO facility_reservation (member_id, facility_id, start_datetime, end_datetime) VALUES
    (1, 1, '2026-05-13 10:00:00', '2026-05-13 10:30:00');


-- ============================================================
-- PERSONAL TRAINING SESSION
-- ============================================================
INSERT INTO personal_training_session (member_id, trainer_id, datetime, duration_minutes, status) VALUES
    (1, 2, '2026-05-19 11:00:00', 60, 'confirmed');


-- ============================================================
-- MEMBERSHIP PLANS
-- ============================================================
INSERT INTO membership_plan (name, price, description, max_classes_per_month) VALUES
    ('Basic',   19.99, 'Access to gym floor and cardio equipment.',          NULL),
    ('Premium', 39.99, 'Unlimited classes + equipment and facility access.', NULL);


-- ============================================================
-- MEMBER SUBSCRIPTIONS
-- ============================================================
INSERT INTO member_subscription (member_id, plan_id, start_date, end_date, status) VALUES
    (1, 2, '2026-05-01', '2026-06-01', 'active'),
    (4, 1, '2026-05-01', '2026-06-01', 'active');
