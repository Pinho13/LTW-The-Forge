
## Paste text below to dbdiagram.io to load visually the schema

https://dbdiagram.io/d/Base-de-Dados-Ginasio-69ccfb9cfb2db18e3b56c5ec

// Gym Management Platform — DBML Schema
// Paste this into https://dbdiagram.io

Table user {
  user_id integer [pk, increment]
  name varchar
  username varchar
  email varchar [unique, not null]
  password_hash varchar [not null]
  profile_photo varchar
  is_active bool [default: true]
  role varchar [not null, note: 'member | trainer | admin']
  created_at datetime [default: `CURRENT_TIMESTAMP`]
}

Table trainer_profile {
  trainer_id integer [pk, increment]
  user_id integer [ref: - user.user_id, not null]
  bio text
  specializations varchar
  certifications varchar
}

Table class_type {
  id integer [pk, increment]
  name varchar [not null, unique]
}

Table class {
  id integer [pk, increment]
  name varchar [not null]
  type_id integer [ref: > class_type.id]
  description text
  duration_minutes integer [not null]
  intensity integer [not null, note: '1 to 5']
  trainer_id integer [ref: > user.user_id]
}

Table class_session {
  id integer [pk, increment]
  class_id integer [ref: > class.id]
  datetime datetime [not null]
  capacity integer [not null]
  room varchar [not null]
}

// Waitlist is PHP-managed via enrolled_at ordering.
// PHP promotes 'waitlisted' → 'enrolled' when a spot opens.
Table enrollment {
  id integer [pk, increment]
  member_id integer [ref: > user.user_id]
  session_id integer [ref: > class_session.id]
  enrolled_at datetime [default: `CURRENT_TIMESTAMP`]
  status varchar [note: 'enrolled | cancelled | waitlisted']
}

Table review {
  id integer [pk, increment]
  class_id integer [ref: > class.id]
  member_id integer [ref: > user.user_id]
  rating integer [note: '1 to 5']
  comment text
  created_at datetime [default: `CURRENT_TIMESTAMP`]
}

// Main gym floor machines. Each unit has implicit capacity = 1.
Table equipment {                                                                    
  id integer [pk, increment]                                                         
  name varchar [not null]                                                            
  type varchar                                                                       
  description text
}                                                                                    
               
Table equipment_unit {
  id integer [pk, increment]
  equipment_id integer [ref: > equipment.id]
  identifier varchar
  status varchar [not null, default: 'available', note: 'available | maintenance | retired']
}

Table equipment_reservation {
  id integer [pk, increment]
  member_id integer [ref: > user.user_id]
  unit_id integer [ref: > equipment_unit.id]
  start_datetime datetime [not null]
  end_datetime datetime [not null]
}

// Rooms/amenities with shared capacity (jacuzzi, turkish bath, bike room, etc.)
// PHP enforces that concurrent active reservations <= max_occupancy.
Table facility {
  id integer [pk, increment]
  name varchar [not null]
  description text
  max_occupancy integer [not null]
}

Table facility_reservation {
  id integer [pk, increment]
  member_id integer [ref: > user.user_id]
  facility_id integer [ref: > facility.id]
  start_datetime datetime [not null]
  end_datetime datetime [not null]
}

// left_at NULL + status = 'in_gym' → member is currently inside.
// PHP derives weekly streak and session counts from this table.
Table gym_visit {
  id integer [pk, increment]
  member_id integer [ref: > user.user_id]
  entered_at datetime [not null, default: `CURRENT_TIMESTAMP`]
  left_at datetime [note: 'NULL while in_gym']
  status varchar [not null, default: 'in_gym', note: 'in_gym | left']
}

Table personal_training_session {
  id integer [pk, increment]
  member_id integer [ref: > user.user_id]
  trainer_id integer [ref: > user.user_id]
  datetime datetime [not null]
  duration_minutes integer
  status varchar [not null, default: 'pending', note: 'pending | confirmed | cancelled']
}

Table membership_plan {
  id integer [pk, increment]
  name varchar [not null, unique, note: 'basic | premium | ...']
  price float [not null]
  description text
  max_classes_per_month integer
}

Table member_subscription {
  id integer [pk, increment]
  member_id integer [ref: > user.user_id]
  plan_id integer [ref: > membership_plan.id]
  start_date date [not null]
  end_date date [not null]
  status varchar [not null, default: 'active', note: 'active | expired | cancelled']
}

