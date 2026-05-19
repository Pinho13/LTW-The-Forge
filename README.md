# The Forge - Gym Website

# ltw12g01

## Overview

The Forge is a gym management web application developed for the LTW course.

The platform supports three core user roles: members, trainers, and administrators.
Members can browse and enroll in classes and track their activity, trainers manage sessions and profiles, and administrators oversee users, classes, and equipment.

The system is being developed following the requirements of the course, including structured CSS, PHP with PDO, and role-based functionality.

## First Delivery 
HTML files to be considered are [index](index.html); [my-account](my-account.html) and [classes](classes-static.html).

After opening index, click the LogIn button to open the LogIn/Register forms. In any one of the two, if the Sign In/Register is pressed then you will be redirected to my-account.

Styling for these pages can be found at src/style/

## Features

**All users:**
- [x] Register a new account.
- [~] Log in and out.
- [ ] Edit their profile, including name, username, password, and profile photo.

**Members:**
- [~] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [ ] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [ ] View trainer profiles, including their specializations and the classes they teach.
- [ ] Check the current availability of equipment in the main training area.
- [ ] Leave ratings and reviews for classes they have attended.

**Trainers:**
- [ ] Manage their public profile, including bio, specializations, and certifications.
- [ ] View the roster of members enrolled in their classes.
- [ ] Track and manage their assigned class schedule.

**Admins:**
- [ ] Manage members and trainers (create, update, and deactivate accounts).
- [ ] Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- [ ] Manage equipment in the main training area (add, update availability status, and remove items).
- [ ] Elevate a user to admin status.
- [ ] Oversee and ensure the smooth operation of the entire system.

**Extra:**
- [ ] Membership plans.
- [ ] Personal logging of material.

## Running

Make sure you are in the root directory of the project.

```bash
sqlite3 database/database.db ".read database/sql/schema.sql"
sqlite3 database/database.db ".read database/sql/populate.sql"
php -S localhost:9000
```

Then open [The Forge](http://localhost:9000/src/pages/index.php).


## Credentials

### Member
- normal@gmail.com / NormalTest1!

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

- **utils/**  
Provides helper functions and utilities used across different parts of the project.

- **docs/**  
Contains project documentation, reports, and supporting written materials.

- **design/**  
Holds design assets, mockups, and visual references for the application.

## Design and Mockups

The project includes design assets and mockups used to guide the visual development of the platform.  
These resources are available in the `design/` folder, which is organized into subfolders such as [`ui/`](design/ui/), [`colors/`](design/colors/), and [`fonts/`](design/fonts/).

## Technologies Used

- HTML5
- CSS3
- PHP
- JavaScript
- Ajax / JSON
- SQLite
- PDO

## Security Measures

The project includes the following security practices:

- Use of prepared statements with PDO
- Session-based authentication
- Password hashing
- Output escaping to mitigate XSS
- Role-based access control

(Some of these features are still being refined.)

## Extra project details
> Faculdade de Engenharia da Universidade do Porto
>
> Página do Curso: https://pages.up.pt/~up353972/page/courses/ltw/
>
> Descrição do Projeto: https://pages.up.pt/~up353972/page/courses/ltw/project/ 


## Team LTW12G01

| Name | Email |
|------|-------|
| Miguel Rocha | 202405484@up.pt |
| Pedro Teixeira | 202404987@up.pt |
| Rafael Silva | 202406334@up.pt |

