
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

Table class {
  id integer [pk, increment]
  name varchar [not null]
  type_id integer [ref: > class_type.id]
  description text
  duration_minutes integer [not null]
  intensity integer [not null]
  trainer_id integer [ref: > user.user_id]
  
}
  
Table class_type {                                                                                                               
  id integer [pk, increment]                                                                                                     
  name varchar [not null, unique]                                                                                                
}                                                                                                                                
  
Table class_session {
  id integer [pk, increment]
  class_id integer [ref: > class.id]
  datetime datetime [not null]
  capacity integer [not null]
  room varchar [not null]

}

Table enrollment {
  id integer [pk, increment]
  member_id integer [ref: > user.user_id]
  session_id integer [ref: > class_session.id]
  enrolled_at datetime [default: `CURRENT_TIMESTAMP`]
  status varchar [note: 'enrolled | cancelled']
}

Table waitlist {
  id integer [pk, increment]
  session_id integer [ref: > class_session.id]
  member_id integer [ref: > user.user_id]
  joined_at datetime [default: `CURRENT_TIMESTAMP`]
  position integer
}

Table review {
  id integer [pk, increment]
  class_id integer [ref: > class.id]
  member_id integer [ref: > user.user_id]
  rating integer [note: '1 to 5']
  comment text
  created_at datetime [default: `CURRENT_TIMESTAMP`]
}

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
  status varchar [not null, note: 'available | maintenance | retired', default: 'available']
}                                                                                                                                
  
Table equipment_reservation {                                                                                                    
  id integer [pk, increment]                                                                                                   
  member_id integer [ref: > user.user_id]
  unit_id integer [ref: > equipment_unit.id]                                                                                     
  start_datetime datetime
  end_datetime datetime                                                                                                              
}                                                                                                                                
  

Table personal_training_session {
  id integer [pk, increment]
  member_id integer [ref: > user.user_id]
  trainer_id integer [ref: > user.user_id]
  datetime datetime
  duration_minutes integer
  status varchar [note: 'pending | confirmed | cancelled']
}

Table membership_plan {
  id integer [pk, increment]
  name varchar [note: 'basic | premium | ...']
  price float
  description text
  max_classes_per_month integer
}

Table member_subscription {
  id integer [pk, increment]
  member_id integer [ref: > user.user_id]
  plan_id integer [ref: > membership_plan.id]
  start_date date
  end_date date
  status varchar [note: 'active | expired | cancelled']
}


