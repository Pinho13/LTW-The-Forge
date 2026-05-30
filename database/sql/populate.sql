-- Safe to re-run: clears all data and resets AUTOINCREMENT counters first
PRAGMA foreign_keys = OFF;
DELETE FROM admin_log;
DELETE FROM announcement;
DELETE FROM member_subscription;
DELETE FROM personal_training_session;
DELETE FROM gym_visit;
DELETE FROM facility_reservation;
DELETE FROM facility;
DELETE FROM class_room;
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
    ('Normal User',   'normaluser',   'normal@gmail.com',   '$2y$12$5WlMPEie.uE7qRwWQDSWzeYLrlXWtfbpPviuh5jwa5gRJ7MMoAYSi', 'member'),
    ('Trainer User',  'traineruser',  'trainer@gmail.com',  '$2y$12$EaWvzlwLi4d3nl7JSy8ZFuiKIDUgUQZoOayYtcvCyHgXi0JyAiMES', 'trainer'),
    ('Admin User',    'adminuser',    'admin@gmail.com',    '$2y$12$M8bgiimX4JSAQ43Y7lcKSOi.3Vc7IqalZxWTPzWmhhhmshzW3HbHu', 'admin'),
    ('Member Two',    'membertwo',    'member2@gmail.com',  '$2y$12$TNGOaPId2WaZRqcgugHHaOqu9EErBKhiXoxTD7CXbl1NpHtyyk5RC', 'member'),
    ('Marcus Steel',  'marcus.steel', 'marcus@theforge.com','placeholder', 'trainer'),
    ('Elena Voss',    'elena.voss',   'elena@theforge.com', 'placeholder', 'trainer'),
    ('Daniel Cruz',   'daniel.cruz',  'daniel@theforge.com','placeholder', 'trainer'),
    ('Sofia Ramos',   'sofia.ramos',  'sofia@theforge.com', 'placeholder', 'trainer'),
    ('Jake Morris',   'jake.morris',  'jake@member.com',   'placeholder', 'member'),
    ('Priya Shah',    'priya.shah',   'priya@member.com',  'placeholder', 'member'),
    ('Leo Fernandes', 'leo.fernandes','leo@member.com',    'placeholder', 'member'),
    ('Mia Correia',   'mia.correia',  'mia@member.com',    'placeholder', 'member'),
    ('Test Basic',    'test.basic',   'basic@test.com',    '$2y$12$FQ.O17XF.vsRLFle3DYH8e89S.E3zFsJZod00t3mWFyLBAM8hcO/a', 'member'),
    ('Test Premium',  'test.premium', 'premium@test.com',  '$2y$12$NGENnzJodEY.YgVNAnWTS.mRS0vSLPClgozMxnD7d4RsiT6sh3kuG', 'member'),
    ('Banned Member',  'banned.user',   'banned@test.com',   'placeholder', 'member'),
    ('Expired Member', 'expired.user',  'expired@test.com',  'placeholder', 'member');

-- Banned member (id 15) — is_active defaults to 1, set to 0 after insert
UPDATE user SET is_active = 0 WHERE username = 'banned.user';


-- ============================================================
-- TRAINER PROFILES
-- ============================================================
INSERT INTO trainer_profile (user_id, bio, specializations, certifications, is_featured) VALUES
    (2, 'Certified fitness coach with 8 years of experience.', 'HIIT, Yoga, Cardio', 'ACE Personal Trainer, RYT-200', 1),
    (5, 'Former competitive powerlifter turned strength coach. 10 years on the platform, 5 years coaching.', 'Powerlifting, Strength & Conditioning, Olympic Lifting', 'NSCA-CSCS, USA Powerlifting Coach', 1),
    (6, 'Mobility specialist and yoga instructor passionate about injury prevention and functional movement.', 'Yoga, Mobility, Pilates, Flexibility', 'RYT-500, FMS Level 2, NASM-CPT', 0),
    (7, 'Boxing and kickboxing coach with a background in competitive martial arts. High-intensity is the only intensity.', 'Boxing, Kickboxing, HIIT, Functional Fitness', 'ACE-CPT, USA Boxing Coach Level 2', 0),
    (8, 'Nutrition-focused coach specialising in body recomposition and endurance training.', 'Endurance, Body Recomposition, Running, Cycling', 'ISSA-CPT, Precision Nutrition Level 1', 0);


-- ============================================================
-- CLASS ROOMS
-- ============================================================
INSERT INTO class_room (name) VALUES
    ('Room A'),
    ('Room B'),
    ('Room C'),
    ('Spin Room'),
    ('Studio A');

-- ============================================================
-- CLASS TYPES
-- ============================================================
INSERT INTO class_type (name) VALUES
    ('Yoga'),
    ('HIIT'),
    ('Cardio'),
    ('Strength'),
    ('Boxing'),
    ('Pilates'),
    ('Cycling');


-- ============================================================
-- CLASSES
-- ============================================================
INSERT INTO class (name, type_id, description, duration_minutes, intensity, trainer_id, is_featured) VALUES
    ('Morning Yoga',    1, 'A calm morning flow to improve flexibility and mindfulness.', 60, 2, 2, 1),
    ('HIIT Blast',      2, 'High-intensity interval training to torch calories fast.',    60, 4, 2, 1),
    ('Cardio Burn',     3, 'Steady-state cardio session suitable for all fitness levels.', 60, 3, 2, 0),
    ('Power Hour',      4, 'Heavy compound lifts for total body strength.',               60, 5, 5, 1),
    ('Fight Club',      5, 'Bag work, combos and conditioning drills.',                   60, 5, 7, 0),
    ('Core & Flex',     6, 'Pilates-based core stability and flexibility work.',          60, 2, 6, 0),
    ('Spin Session',    7, 'High-cadence indoor cycling to upbeat music.',                60, 4, 8, 0),
    ('Evening Yoga',    1, 'Restorative yoga to wind down the day.',                      60, 1, 6, 0),
    ('Kettlebell Burn',    4, 'Functional strength with kettlebells.',      60, 4, 5, 0),
    ('Boxing Foundations', 5, 'Intro to boxing technique and bag work.',  60, 3, NULL, 0);


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
    (2, '2026-06-04 18:00:00', 'Room B', 12),  -- id 24 HIIT Blast    Thu  4 Jun

    -- === JAN–MAR 2026 (load-more test data, ids 25–60) ===
    (1, '2026-01-05 08:00:00', 'Room A', 15),  -- id 25
    (2, '2026-01-07 18:00:00', 'Room B', 15),  -- id 26
    (3, '2026-01-09 10:00:00', 'Room C', 15),  -- id 27
    (1, '2026-01-12 08:00:00', 'Room A', 15),  -- id 28
    (2, '2026-01-14 18:00:00', 'Room B', 15),  -- id 29
    (3, '2026-01-16 10:00:00', 'Room C', 15),  -- id 30
    (1, '2026-01-19 08:00:00', 'Room A', 15),  -- id 31
    (2, '2026-01-21 18:00:00', 'Room B', 15),  -- id 32
    (3, '2026-01-23 10:00:00', 'Room C', 15),  -- id 33
    (1, '2026-01-26 08:00:00', 'Room A', 15),  -- id 34
    (2, '2026-01-28 18:00:00', 'Room B', 15),  -- id 35
    (3, '2026-01-30 10:00:00', 'Room C', 15),  -- id 36
    (1, '2026-02-02 08:00:00', 'Room A', 15),  -- id 37
    (2, '2026-02-04 18:00:00', 'Room B', 15),  -- id 38
    (3, '2026-02-06 10:00:00', 'Room C', 15),  -- id 39
    (1, '2026-02-09 08:00:00', 'Room A', 15),  -- id 40
    (2, '2026-02-11 18:00:00', 'Room B', 15),  -- id 41
    (3, '2026-02-13 10:00:00', 'Room C', 15),  -- id 42
    (1, '2026-02-16 08:00:00', 'Room A', 15),  -- id 43
    (2, '2026-02-18 18:00:00', 'Room B', 15),  -- id 44
    (3, '2026-02-20 10:00:00', 'Room C', 15),  -- id 45
    (1, '2026-02-23 08:00:00', 'Room A', 15),  -- id 46
    (2, '2026-02-25 18:00:00', 'Room B', 15),  -- id 47
    (3, '2026-02-27 10:00:00', 'Room C', 15),  -- id 48
    (1, '2026-03-02 08:00:00', 'Room A', 15),  -- id 49
    (2, '2026-03-04 18:00:00', 'Room B', 15),  -- id 50
    (3, '2026-03-06 10:00:00', 'Room C', 15),  -- id 51
    (1, '2026-03-09 08:00:00', 'Room A', 15),  -- id 52
    (2, '2026-03-11 18:00:00', 'Room B', 15),  -- id 53
    (3, '2026-03-13 10:00:00', 'Room C', 15),  -- id 54
    (1, '2026-03-16 08:00:00', 'Room A', 15),  -- id 55
    (2, '2026-03-18 18:00:00', 'Room B', 15),  -- id 56
    (3, '2026-03-20 10:00:00', 'Room C', 15),  -- id 57
    (1, '2026-03-23 08:00:00', 'Room A', 15),  -- id 58
    (2, '2026-03-25 18:00:00', 'Room B', 15),  -- id 59
    (3, '2026-03-27 10:00:00', 'Room C', 15),  -- id 60

    -- === NEW CLASSES — current week (26 May – 1 Jun 2026) ===
    (4, '2026-05-26 08:00', 'Room B',    12),  -- id 61  Power Hour       Mon
    (4, '2026-05-28 08:00', 'Room B',    12),  -- id 62  Power Hour       Wed
    (4, '2026-05-30 08:00', 'Room B',    12),  -- id 63  Power Hour       Fri
    (5, '2026-05-27 08:00', 'Room C',    10),  -- id 64  Fight Club       Tue
    (5, '2026-05-29 08:00', 'Room C',    10),  -- id 65  Fight Club       Thu
    (6, '2026-05-26 09:00', 'Studio A',  15),  -- id 66  Core & Flex      Mon
    (6, '2026-05-28 09:00', 'Studio A',  15),  -- id 67  Core & Flex      Wed
    (7, '2026-05-27 09:00', 'Spin Room', 20),  -- id 68  Spin Session     Tue
    (7, '2026-05-29 09:00', 'Spin Room', 20),  -- id 69  Spin Session     Thu
    (8, '2026-05-26 09:00', 'Studio A',  20),  -- id 70  Evening Yoga     Mon
    (8, '2026-05-28 09:00', 'Studio A',  20),  -- id 71  Evening Yoga     Wed
    (8, '2026-05-30 09:00', 'Studio A',  20),  -- id 72  Evening Yoga     Fri
    (9, '2026-05-30 08:00', 'Room B',    12),  -- id 73  Kettlebell Burn  Sat

    -- === NEXT WEEK (2–6 Jun 2026) ===
    (4, '2026-06-02 08:00', 'Room B',    12),  -- id 74  Power Hour       Mon
    (4, '2026-06-04 08:00', 'Room B',    12),  -- id 75  Power Hour       Wed
    (5, '2026-06-02 08:00', 'Room C',    10),  -- id 76  Fight Club       Mon
    (6, '2026-06-03 09:00', 'Studio A',  15),  -- id 77  Core & Flex      Tue
    (7, '2026-06-02 09:00', 'Spin Room', 20),  -- id 78  Spin Session     Mon
    (8, '2026-06-01 09:00', 'Studio A',  20),  -- id 79  Evening Yoga     Sun
    (9, '2026-06-06 08:00', 'Room B',    12),  -- id 80  Kettlebell Burn  Sat
    (1, '2026-06-02 09:00', 'Studio A',   1); -- id 81  Morning Yoga at capacity (demo)


-- ============================================================
-- ENROLLMENT
-- normal@gmail.com (id 1):
--   Classes this month (May): ids 8-14 (past) + 15, 17, 19 (future) = 10
--   Upcoming (future enrolled):  15, 17, 19, 23                      =  4
-- ============================================================
INSERT INTO enrollment (member_id, session_id, status) VALUES
    -- normal — April history
    (1,  1, 'completed'),  -- Morning Yoga  Tue 22 Apr
    (1,  2, 'completed'),  -- HIIT Blast    Wed 23 Apr
    (1,  3, 'completed'),  -- HIIT Blast    Thu 24 Apr  (fills capacity=1)
    (4,  3, 'waitlisted'), -- member2 waitlisted — session full
    (1,  4, 'missed'),     -- Cardio Burn   Sat 25 Apr
    (1,  5, 'completed'),  -- Morning Yoga  Tue 28 Apr
    (1,  6, 'completed'),  -- HIIT Blast    Wed 29 Apr
    (1,  7, 'missed'),     -- Cardio Burn   Thu 30 Apr

    -- normal — May past  (+7 classes this month)
    (1,  8, 'completed'),  -- Cardio Burn   Fri  2 May
    (1,  9, 'completed'),  -- Morning Yoga  Mon  5 May
    (1, 10, 'completed'),  -- HIIT Blast    Tue  6 May
    (1, 11, 'missed'),     -- Cardio Burn   Wed  7 May
    (1, 12, 'completed'),  -- Morning Yoga  Thu  8 May
    (1, 13, 'completed'),  -- HIIT Blast    Fri  9 May
    (1, 14, 'completed'),  -- Cardio Burn   Sat 10 May

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
    (4, 24, 'enrolled'),   -- HIIT Blast    Thu  4 Jun

    -- current week — new classes (ids 61-80)
    -- user_ids: 1=Normal, 4=Member Two, 9=Jake, 10=Priya, 11=Leo, 12=Mia
    (9,  61, 'enrolled'), (10, 61, 'enrolled'), (4,  61, 'enrolled'),
    (12, 66, 'enrolled'), (10, 66, 'enrolled'), (1,  66, 'enrolled'),
    (11, 70, 'enrolled'), (12, 70, 'enrolled'), (1,  70, 'enrolled'),
    (9,  68, 'enrolled'), (11, 68, 'enrolled'), (4,  68, 'enrolled'), (10, 68, 'enrolled'),
    (1,  21, 'enrolled'), (9,  21, 'enrolled'),
    (11, 64, 'enrolled'), (4,  64, 'enrolled'),
    (9,  62, 'enrolled'), (10, 62, 'enrolled'),
    (12, 67, 'enrolled'), (1,  67, 'enrolled'),
    (11, 71, 'enrolled'), (12, 71, 'enrolled'), (9,  71, 'enrolled'),
    (10, 69, 'enrolled'), (4,  69, 'enrolled'), (1,  69, 'enrolled'),
    (9,  22, 'enrolled'), (12, 22, 'enrolled'),
    (11, 65, 'enrolled'), (9,  65, 'enrolled'),
    (10, 63, 'enrolled'), (4,  63, 'enrolled'), (11, 63, 'enrolled'),
    (9,  73, 'enrolled'), (1,  73, 'enrolled'), (12, 73, 'enrolled'), (10, 73, 'enrolled'),
    (1,  72, 'enrolled'), (11, 72, 'enrolled'),
    (9,  74, 'enrolled'), (10, 74, 'enrolled'),
    (1,  78, 'enrolled'), (4,  76, 'enrolled'),
    (12, 77, 'enrolled'), (11, 79, 'enrolled'),
    (9,  80, 'enrolled'), (10, 80, 'enrolled'),

    -- normal — Jan–Mar 2026 history (ids 25–60)
    (1, 25, 'completed'),
    (1, 26, 'completed'),
    (1, 27, 'missed'),
    (1, 28, 'completed'),
    (1, 29, 'completed'),
    (1, 30, 'completed'),
    (1, 31, 'missed'),
    (1, 32, 'completed'),
    (1, 33, 'completed'),
    (1, 34, 'completed'),
    (1, 35, 'missed'),
    (1, 36, 'completed'),
    (1, 37, 'completed'),
    (1, 38, 'completed'),
    (1, 39, 'missed'),
    (1, 40, 'completed'),
    (1, 41, 'completed'),
    (1, 42, 'completed'),
    (1, 43, 'missed'),
    (1, 44, 'completed'),
    (1, 45, 'completed'),
    (1, 46, 'completed'),
    (1, 47, 'missed'),
    (1, 48, 'completed'),
    (1, 49, 'completed'),
    (1, 50, 'completed'),
    (1, 51, 'missed'),
    (1, 52, 'completed'),
    (1, 53, 'completed'),
    (1, 54, 'completed'),
    (1, 55, 'missed'),
    (1, 56, 'completed'),
    (1, 57, 'completed'),
    (1, 58, 'completed'),
    (1, 59, 'missed'),
    (1, 60, 'completed'),
    (1, 81, 'enrolled');  -- fills session 81 to capacity (demo: at-capacity attention item)


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

    -- member2 — 6 consecutive weeks (streak = 6)
    (4, '2026-04-27 10:00:00', '2026-04-27 11:00:00', 'left'),  -- week of Apr 26
    (4, '2026-05-04 10:00:00', '2026-05-04 11:00:00', 'left'),  -- week of May 3
    (4, '2026-05-11 10:00:00', '2026-05-11 11:00:00', 'left'),  -- week of May 10
    (4, '2026-05-18 10:00:00', '2026-05-18 11:00:00', 'left'),  -- week of May 17
    (4, '2026-05-22 10:00:00', '2026-05-22 11:00:00', 'left'),  -- week of May 17 (extra)
    (4, '2026-05-26 09:00:00', NULL,                  'in_gym'); -- week of May 24 (this week)


-- ============================================================
-- EQUIPMENT
-- IDs map to SVG positions in equipment-map.php:
--   1  = Treadmill          (top-left zone, left column row 1)
--   2  = Cable Machine      (top-left zone, right of treadmills)
--   3  = Lat Pulldown       (top-center row)
--   4  = Stationary Bike    (center-left cluster)
--   5  = Cable Crossover    (center)
--   6  = Chest Press        (center-right row)
--   7  = Smith Machine      (top-right zone)
--   8  = Free Weight Rack   (top-left, far left column)
-- ============================================================
INSERT INTO equipment (name, type, description, photo, default_w, default_h) VALUES
    ('Treadmill',       'Cardio',        'Electric treadmill with speed and incline settings.',       'treadmill.png',       55, 24),
    ('Cable Machine',   'Strength',      'Dual-stack cable machine for upper/lower body exercises.',  'cable_machine.png',   55, 38),
    ('Lat Pulldown',    'Strength',      'Lat pulldown and seated row station.',                      'lat_pulldown.png',    53, 55),
    ('Stationary Bike', 'Cardio',        'Upright exercise bike with adjustable resistance.',         'stationary_bike.png', 40, 55),
    ('Chest Press',     'Strength',      'Plate-loaded chest press machine.',                         'chest_press.png',     37, 55),
    ('Free Weight Rack','Weightlifting', 'Dumbbell rack with full weight range 2–40 kg.',             'free_weight_rack.png',  9, 55);


-- ============================================================
-- EQUIPMENT UNITS
-- ============================================================
INSERT INTO equipment_unit (equipment_id, identifier, status, map_x, map_y, map_w, map_h) VALUES
    -- Treadmills (eq 1)
    (1, 'TRD-01', 'available',   140,  47, 59, 27),
    (1, 'TRD-02', 'available',   140,  79, 59, 27),
    (1, 'TRD-03', 'maintenance', 140, 111, 59, 27),
    (1, 'TRD-04', 'available',   140, 144, 59, 27),
    (1, 'TRD-05', 'available',   140, 202, 59, 27),
    (1, 'TRD-06', 'maintenance', 140, 234, 59, 27),
    (1, 'TRD-07', 'available',   140, 266, 59, 27),

    -- Cable Machines (eq 2)
    (2, 'CAB-01', 'maintenance',  72,  46, 48, 29),
    (2, 'CAB-02', 'available',    72,  78, 48, 29),
    (2, 'CAB-03', 'available',    72, 110, 48, 29),
    (2, 'CAB-04', 'available',    72, 143, 48, 29),
    (2, 'CAB-05', 'available',    72, 201, 48, 29),
    (2, 'CAB-06', 'available',    72, 233, 48, 29),
    (2, 'CAB-07', 'available',    72, 265, 48, 29),

    -- Lat Pulldown (eq 3)
    (3, 'LAT-01', 'available',   226,  46, 51, 55),
    (3, 'LAT-02', 'available',   287,  46, 52, 55),
    (3, 'LAT-03', 'maintenance',   347,  46, 52, 55),
    (3, 'LAT-04', 'available',   409,  46, 52, 55),
    (3, 'LAT-05', 'available',   470,  46, 52, 55),

    -- Stationary Bikes (eq 4)
    (4, 'BIK-01', 'available',   231, 162, 30, 48),
    (4, 'BIK-02', 'available',   268, 162, 31, 48),
    (4, 'BIK-03', 'available',   305, 162, 31, 48),

    -- Chest Press (eq 5)
    (5, 'CPR-01', 'available',   356, 164, 37, 53),
    (5, 'CPR-02', 'available',   397, 164, 36, 53),
    (5, 'CPR-03', 'available',   437, 164, 37, 53),
    (5, 'CPR-04', 'available',   478, 164, 36, 53),
    (5, 'CPR-05', 'available',   518, 164, 37, 53),
    (5, 'CPR-06', 'available',   559, 164, 36, 53),

    -- Free Weight Rack (eq 6)
    (6, 'FWR-01', 'available', 50, 212, 14, 66),
    (6, 'FWR-02', 'available', 50,  74, 14, 66);


-- ========================================================================================================================
-- EXTRA FEATURES PLANNED / PARTIALLY INTEGRATED
-- ========================================================================================================================


-- ============================================================
-- EQUIPMENT RESERVATION
-- ============================================================
INSERT INTO equipment_reservation (member_id, unit_id, start_datetime, end_datetime) VALUES
    (1, 1, '2026-05-12 09:00:00', '2026-05-12 09:30:00'),
    (1, 6, '2026-05-14 10:00:00', '2026-05-14 11:00:00'),
    -- Today's reservations for visual testing (all units)
    (4,  1, '2026-05-26 08:00:00', '2026-05-26 08:50:00'),
    (9,  1, '2026-05-26 10:20:00', '2026-05-26 11:10:00'),
    (10, 1, '2026-05-26 13:00:00', '2026-05-26 14:20:00'),
    (4,  2, '2026-05-26 09:10:00', '2026-05-26 10:00:00'),
    (9,  2, '2026-05-26 12:30:00', '2026-05-26 13:10:00'),
    (10, 2, '2026-05-26 15:00:00', '2026-05-26 16:10:00'),
    (4,  3, '2026-05-26 08:40:00', '2026-05-26 09:30:00'),
    (9,  3, '2026-05-26 11:00:00', '2026-05-26 12:00:00'),
    (10, 3, '2026-05-26 14:10:00', '2026-05-26 15:20:00'),
    (4,  4, '2026-05-26 09:00:00', '2026-05-26 09:50:00'),
    (9,  4, '2026-05-26 12:00:00', '2026-05-26 13:00:00'),
    (10, 4, '2026-05-26 16:00:00', '2026-05-26 16:50:00'),
    (4,  5, '2026-05-26 08:20:00', '2026-05-26 09:10:00'),
    (9,  5, '2026-05-26 11:30:00', '2026-05-26 12:20:00'),
    (10, 5, '2026-05-26 14:40:00', '2026-05-26 15:30:00'),
    (4,  6, '2026-05-26 09:30:00', '2026-05-26 10:20:00'),
    (9,  6, '2026-05-26 13:10:00', '2026-05-26 14:00:00'),
    (10, 6, '2026-05-26 15:50:00', '2026-05-26 16:40:00'),
    (4,  7, '2026-05-26 08:10:00', '2026-05-26 09:00:00'),
    (9,  7, '2026-05-26 11:20:00', '2026-05-26 12:10:00'),
    (10, 7, '2026-05-26 14:00:00', '2026-05-26 15:10:00'),
    (4,  8, '2026-05-26 09:40:00', '2026-05-26 10:30:00'),
    (9,  8, '2026-05-26 12:50:00', '2026-05-26 13:40:00'),
    (10, 8, '2026-05-26 16:10:00', '2026-05-26 17:00:00'),
    (4,  9, '2026-05-26 08:30:00', '2026-05-26 09:20:00'),
    (9,  9, '2026-05-26 11:10:00', '2026-05-26 12:00:00'),
    (10, 9, '2026-05-26 14:30:00', '2026-05-26 15:20:00'),
    (4,  10, '2026-05-26 09:50:00', '2026-05-26 10:40:00'),
    (9,  10, '2026-05-26 13:20:00', '2026-05-26 14:10:00'),
    (4,  11, '2026-05-26 08:50:00', '2026-05-26 09:40:00'),
    (9,  11, '2026-05-26 12:10:00', '2026-05-26 13:00:00'),
    (10, 11, '2026-05-26 15:20:00', '2026-05-26 16:10:00'),
    (4,  12, '2026-05-26 09:20:00', '2026-05-26 10:10:00'),
    (9,  12, '2026-05-26 13:40:00', '2026-05-26 14:30:00'),
    (4,  13, '2026-05-26 08:00:00', '2026-05-26 09:00:00'),
    (9,  13, '2026-05-26 11:40:00', '2026-05-26 12:30:00'),
    (10, 13, '2026-05-26 15:10:00', '2026-05-26 16:00:00'),
    (4,  14, '2026-05-26 10:00:00', '2026-05-26 10:50:00'),
    (9,  14, '2026-05-26 13:30:00', '2026-05-26 14:20:00'),
    (10, 14, '2026-05-26 17:00:00', '2026-05-26 17:50:00'),
    (4,  14, '2026-05-26 19:00:00', '2026-05-26 19:40:00'),
    (4,  15, '2026-05-26 08:20:00', '2026-05-26 09:10:00'),
    (9,  15, '2026-05-26 12:20:00', '2026-05-26 13:10:00'),
    (10, 15, '2026-05-26 16:20:00', '2026-05-26 17:10:00'),
    (4,  16, '2026-05-26 09:00:00', '2026-05-26 09:50:00'),
    (9,  16, '2026-05-26 13:50:00', '2026-05-26 14:40:00'),
    (4,  18, '2026-05-26 08:10:00', '2026-05-26 09:00:00'),
    (9,  18, '2026-05-26 11:50:00', '2026-05-26 12:40:00'),
    (10, 18, '2026-05-26 15:40:00', '2026-05-26 16:30:00'),
    (4,  19, '2026-05-26 09:30:00', '2026-05-26 10:20:00'),
    (9,  19, '2026-05-26 13:00:00', '2026-05-26 13:50:00'),
    (4,  20, '2026-05-26 10:10:00', '2026-05-26 11:00:00'),
    (9,  20, '2026-05-26 14:20:00', '2026-05-26 15:10:00'),
    (4,  21, '2026-05-26 08:40:00', '2026-05-26 09:30:00'),
    (9,  21, '2026-05-26 12:00:00', '2026-05-26 12:50:00'),
    (10, 21, '2026-05-26 15:30:00', '2026-05-26 16:20:00'),
    (4,  22, '2026-05-26 09:10:00', '2026-05-26 10:00:00'),
    (9,  22, '2026-05-26 13:10:00', '2026-05-26 14:00:00'),
    (4,  23, '2026-05-26 08:00:00', '2026-05-26 08:50:00'),
    (9,  23, '2026-05-26 11:30:00', '2026-05-26 12:20:00'),
    (10, 23, '2026-05-26 14:50:00', '2026-05-26 15:40:00'),
    (4,  24, '2026-05-26 10:20:00', '2026-05-26 11:10:00'),
    (9,  24, '2026-05-26 13:40:00', '2026-05-26 14:30:00'),
    (4,  25, '2026-05-26 09:00:00', '2026-05-26 09:50:00'),
    (9,  25, '2026-05-26 12:30:00', '2026-05-26 13:20:00'),
    (4,  26, '2026-05-26 08:30:00', '2026-05-26 09:20:00'),
    (9,  26, '2026-05-26 14:00:00', '2026-05-26 14:50:00'),
    (10, 26, '2026-05-26 16:30:00', '2026-05-26 17:20:00'),
    (4,  27, '2026-05-26 09:40:00', '2026-05-26 10:30:00'),
    (9,  27, '2026-05-26 13:20:00', '2026-05-26 14:10:00'),
    (4,  28, '2026-05-26 08:10:00', '2026-05-26 09:00:00'),
    (9,  28, '2026-05-26 11:50:00', '2026-05-26 12:40:00'),
    (10, 28, '2026-05-26 15:00:00', '2026-05-26 15:50:00'),
    (4,  29, '2026-05-26 10:00:00', '2026-05-26 10:50:00'),
    (9,  29, '2026-05-26 14:10:00', '2026-05-26 15:00:00'),
    (4,  30, '2026-05-26 08:50:00', '2026-05-26 09:40:00'),
    (9,  30, '2026-05-26 12:10:00', '2026-05-26 13:00:00'),
    (10, 30, '2026-05-26 15:20:00', '2026-05-26 16:10:00'),
    (4,  30, '2026-05-26 09:20:00', '2026-05-26 10:10:00'),
    (9,  30, '2026-05-26 13:50:00', '2026-05-26 14:40:00'),
    (10, 30, '2026-05-26 16:40:00', '2026-05-26 17:30:00');


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
-- plan_id 1 = Basic, plan_id 2 = Premium
INSERT INTO member_subscription (member_id, plan_id, start_date, end_date, status) VALUES
    (1,  2, '2026-05-01', '2027-05-01', 'active'),  -- Normal User   — Premium
    (4,  1, '2026-05-01', '2027-05-01', 'active'),  -- Member Two    — Basic
    (9,  2, '2026-05-01', '2027-05-01', 'active'),  -- Jake Morris   — Premium
    (10, 2, '2026-05-01', '2027-05-01', 'active'),  -- Priya Shah    — Premium
    (11, 1, '2026-05-01', '2027-05-01', 'active'),  -- Leo Fernandes — Basic
    (12, 2, '2026-05-01', '2027-05-01', 'active'),  -- Mia Correia   — Premium
    (13, 1, '2026-05-01', '2027-05-01', 'active'),  -- Test Basic    — Basic
    (14, 2, '2026-05-01', '2027-05-01', 'active'),  -- Test Premium  — Premium
    (16, 2, '2026-01-01', '2026-04-30', 'active');  -- Expired Member — expired Premium (end_date in past, status not updated)


-- ============================================================
-- ANNOUNCEMENTS  (author_id 3 = admin@gmail.com)
-- ============================================================
INSERT INTO announcement (title, body, author_id, pinned, type, read_time, image, created_at) VALUES
    ('Welcome to The Forge!',
     'We are excited to have you here. Browse our class schedule, book equipment, and connect with our trainers. Have a great workout!',
     3, 1, 'Gym News', 2, NULL, '2026-05-01 09:00:00'),
    ('New HIIT Classes Added',
     'We have added extra HIIT Blast sessions every Tuesday and Thursday evening. Check the Classes page for the full schedule.',
     3, 0, 'Event', 1, NULL, '2026-05-10 10:00:00'),
    ('TRD-02 Under Maintenance',
     'Treadmill TRD-02 is currently undergoing routine maintenance. We apologise for the inconvenience — TRD-01 and TRD-03 remain available.',
     3, 0, 'Maintenance', 1, NULL, '2026-05-20 08:00:00'),
    ('Open-Mat Weekend — Apr 27',
     'Two days. Free for members. Bring a guest. No class structure, just open floor time to work on what you want. Both studios will be available from 8 AM to 6 PM.',
     3, 0, 'Event', 1, NULL, '2026-04-22 09:00:00'),
    ('Programming Update — Q2 Block',
     'We are moving to a 4-week conjugate cycle for the strength track. Mobility track stays as is. Expect heavier accessory work and shorter met-cons on alternating days.',
     3, 0, 'Coach Note', 4, NULL, '2026-04-19 10:00:00'),
    ('Studio C Closed Fri 18 Apr',
     'Floor refit. Mobility classes relocate to Studio A. Schedule unchanged — check the app for updated room assignments.',
     3, 0, 'Maintenance', 1, NULL, '2026-04-15 08:00:00'),
    ('Extended Hours Starting May 1',
     'Floor opens 5:30 AM weekdays. Studios stay on the 6:00 AM kickoff. Evening close moves to 10 PM Monday through Thursday.',
     3, 0, 'Gym News', 1, NULL, '2026-04-04 09:00:00'),
    ('New Squat Racks Installed',
     'Three new power racks added to Zone A. All have integrated weight storage and laser-cut j-hooks. Available now, no booking required.',
     3, 0, 'Gym News', 2, NULL, '2026-03-28 11:00:00'),
    ('New Equipment Arriving This Month',
     'We are thrilled to announce the arrival of state-of-the-art equipment coming to The Forge this month. From advanced cable machines to updated cardio stations, we have invested heavily in upgrading your training experience. Installation will take place over the next two weeks with minimal disruption to your routine. Stay tuned for a full reveal!',
     3, 1, 'Gym News', 2, 'news1.png', '2026-05-10 09:00:00'),
    ('Summer Training Program Kicks Off',
     'Our highly anticipated Summer Training Program is officially open for registration. Whether you are looking to build strength, improve endurance, or simply stay active during the warmer months, we have a program tailored for you. Sessions run Monday through Saturday at various times to fit your schedule. Spots are limited — sign up at the front desk or through your member portal.',
     3, 0, 'Programs', 3, 'news2.png', '2026-05-18 10:30:00'),
    ('Gym Floor Plan Renovation Complete',
     'After weeks of planning and construction, the gym floor renovation is complete. We have reorganised the entire floor plan to improve flow, reduce congestion during peak hours, and create dedicated zones for stretching, free weights, and functional training. Come in and explore the new layout — we think you will love the difference.',
     3, 0, 'Gym News', 2, 'news3.png', '2026-05-24 14:00:00');

-- ============================================================
-- ADMIN LOG
-- ============================================================
INSERT INTO admin_log (admin_id, action_type, description, created_at) VALUES
(3, 'LOGIN',   'Admin Admin User signed in',                     '2026-05-25 09:30:00'),
(3, 'CREATE',  'Created class Pilates Reformer',                 '2026-05-24 10:38:00'),
(3, 'UPDATE',  'Updated class HIIT Blast',                       '2026-05-24 10:42:00'),
(3, 'UPDATE',  'Marked Treadmill TRD-02 maintenance',            '2026-05-23 10:11:00'),
(3, 'ELEVATE', 'Changed Jane Smith role from member to trainer', '2026-05-23 09:54:00'),
(3, 'CREATE',  'Created class Boxing Foundations',               '2026-05-22 14:20:00'),
(3, 'DELETE',  'Deleted class Yoga Flow',                        '2026-05-21 11:05:00'),
(3, 'UPDATE',  'Updated details for John Doe',                   '2026-05-20 16:33:00');
