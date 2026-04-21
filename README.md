# [Gym Name] — Gym Management Platform

> Linguagens e Tecnologias Web (LTW) — 2025/2026
>
> Faculdade de Engenharia da Universidade do Porto
>
> Página do Curos: https://pages.up.pt/~up353972/page/courses/ltw/
>
> Descrição do Projeto: https://pages.up.pt/~up353972/page/courses/ltw/project/ 


## Team LTW12G01

| Name | Email |
|------|-------|
| Miguel Rocha | 202405484@up.pt |
| Pedro Teixeira | 202404987@up.pt |
| Rafael Silva | 202406334@up.pt |


## Description

## Implemented Features

### All Users
- To be implementes

### Members
- To be implemented

### Trainers
- To be implemented

### Admins
- To be implemented
## Technologies Used

- **HTML**
- **CSS**
- **SQLite**

## How to Run

```bash
git clone git@github.com:FEUP-LTW-2026/ltw-project-ltw12g01.git
```

**Set up the database** (only needed once):

```bash
sqlite3 database/database.db ".read database/schema.sql"
sqlite3 database/database.db ".read database/populate.sql"
```

**Start the development server:**

```bash
php -S localhost:9000
```

Then open [http://localhost:9000/src/pages/index.php](http://localhost:9000/src/pages/index.php) in your browser.

**Test accounts:**

| Email | Password | Role | Notes |
|-------|----------|------|-------|
| normal@gmail.com | NormalTest1! | Member | enrolled in sessions, has subscription |
| member2@gmail.com | Member2Test1! | Member | waitlisted in a full session |
| trainer@gmail.com | TrainerTest1! | Trainer | has trainer profile |
| admin@gmail.com | AdminTest1! | Admin | |
