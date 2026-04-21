# The Forge - Gym Website 
# ltw12g01

## Features

**All users:**
- [x] Register a new account.
- [ ] Log in and out.
- [ ] Edit their profile, including name, username, password, and profile photo.

**Members:**
- [ ] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
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
- [ ] Something extra (e.g., personal training bookings, membership plans, waitlist, ...).

## Running
'''
    sqlite3 database/database.db ".read database/schema.sql"
    sqlite3 database/database.db ".read database/populate.sql"
    php -S localhost:9000
'''
Then open [Website](http://localhost:9000/src/pages/index.php) in your browser.


## Credentials

- normal@gmail.com / NormalTest1!
- trainer@gmail.com / TrainerTest1!
- admin@gmail.com / AdminTest1! 


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

