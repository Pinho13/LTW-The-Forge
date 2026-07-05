# The Forge - Gym Website

## Overview

The Forge is a gym management web application developed for the LTW course.

The platform supports three core user roles: members, trainers, and administrators.
Members can browse and enroll in classes, reserve equipment, and track their activity; trainers have public profiles and upcoming classes; and administrators oversee users, classes, and equipment.

The system is being developed following the requirements of the course, including structured CSS, PHP with PDO, and role-based functionality.

## Features

**All users:**
- [x] Register a new account.
- [x] Log in and out.
- [x] Edit their profile, including name, username, password, and profile photo.

**Members:**

- [x] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [x] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [x] View trainer profiles, including their specializations and the classes they teach.
- [x] Check the current availability of equipment in the main training area.
- [x] Reserve equipment and manage upcoming reservations.
- [x] Leave ratings and reviews for classes they have attended.

**Trainers:**
- [x] Manage their public profile, including bio, specializations, and certifications.
- [x] View the roster of members enrolled in their classes.
- [x] Track and manage their assigned class schedule.

**Admins:**

- [x] Manage members and trainers (update details, roles, status, and subscriptions).
- [x] Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- [x] Manage equipment in the main training area (add, update availability status, and remove items).
- [x] Create, pin, and delete announcements/news posts.
- [x] Elevate a user to admin status.
- [x] Oversee and ensure the smooth operation of the entire system.

**Extra:**

- [x] Membership plans with upgrade, downgrade, and pause/freeze support.
- [x] Waitlist for fully booked classes with automatic promotion.
- [x] News and announcements system with pinned posts and full article pages.
- [x] Equipment reservations with time-slot booking.
- [x] Trainer public profiles with bio, specializations, and certifications.
- [x] Admin analytics dashboard with key stats and attention alerts.
- [x] Admin activity log and user data CSV export.
- [x] CSRF token protection on all state-changing forms.
- [x] REST API exposing class schedules, trainer profiles, and equipment availability.


## Running

Make sure you are in the root directory of the project.

```bash
sqlite3 database/sql/database.db ".read database/sql/schema.sql"
sqlite3 database/sql/database.db ".read database/sql/populate.sql"
php -S localhost:9000
```

Then open [The Forge](http://localhost:9000/src/pages/index.php).


## Credentials

### Member
- normal@gmail.com / NormalTest1!
- basic@test.com / basic123
- premium@test.com / premium123

### Trainer
- trainer@gmail.com / TrainerTest1!

### Admin
- admin@gmail.com / AdminTest1!


## Project Structure

- **src/pages/**  
Contains the main application pages (entry points) that are directly accessed via the browser.

- **src/templates/**  
Holds reusable UI components and layout templates shared across multiple pages.

- **src/style/**  
Includes all CSS and styling files that define the visual appearance of the platform.

- **src/actions/**  
Contains server-side logic and request handlers (e.g., form submissions, database operations).

- **src/scripts/**  
Stores client-side JavaScript for interactivity and dynamic behavior.

- **database/**  
Includes database-related files such as schema definitions and population scripts.

- **src/utils/**  
Provides helper functions and utilities used across different parts of the project.

- **docs/**  
Contains project documentation, reports, and supporting written materials.

- **design/**  
Holds design assets, mockups, and visual references for the application.

## Design and Mockups

The project includes design assets and mockups used to guide the visual development of the platform.  
These resources are available in the [`design/`](design/) folder, which is organized into subfolders such as [`ui/`](design/ui/), [`colors/`](design/colors/), and [`fonts/`](design/fonts/).

## Technologies Used

- HTML5
- CSS3
- PHP
- JavaScript
- AJAX / JSON
- SQLite
- PDO

## Security Measures

The project includes the following security practices:

- Use of prepared statements with PDO
- Session-based authentication
- Password hashing
- Output escaping to mitigate XSS
- Role-based access control

## Extra project details
> Faculdade de Engenharia da Universidade do Porto
>
> Course Page: https://pages.up.pt/~up353972/page/courses/ltw/
>
> Project Description: https://pages.up.pt/~up353972/page/courses/ltw/project/ 


## Team LTW12G01

| Name | Email |
|------|-------|
| Miguel Rocha | 202405484@up.pt |
| Pedro Teixeira | 202404987@up.pt |
| Rafael Silva | 202406334@up.pt |

<p align = "center" >
  <h2 align = "center" >
    Grade: 18.8/20
  </h2>
</p>
