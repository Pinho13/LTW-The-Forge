<!DOCTYPE html>
    <html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Account - The Forge</title>
        <link rel="stylesheet" href="../style/main.css">
        <link rel="stylesheet" href="../style/layout.css">
        <link rel="stylesheet" href="../style/my-account.css">
    </head>

    <body>
        <input type="checkbox" id="side-menu-toggle">

        <header class="mobile-top-bar">
            <label for="side-menu-toggle" class="logo">
                <img src="../assets/images/logo-no-bg.png" alt="The Forge Logo" class="logo__img">
                <span class="logo__text">THE FORGE</span>
            </label>
        </header>

        <label for="side-menu-toggle" class="side-menu-backdrop"></label>

        <?php $activePage = 'account'; include '../components/side-menu.php'; ?>

        <main>
            <header>
                <h1>Welcome back, Tomás!</h1>
            </header>

            <section class="stats">
                <article class="stat-card">
                    <h2 class="stat-card__value">12</h2>
                    <p class="stat-card__label">Classes This Month</p>
                </article>

                <article class="stat-card">
                    <h2 class="stat-card__value">4</h2>
                    <p class="stat-card__label">Upcoming Reservations</p>
                </article>

                <article class="stat-card">
                    <h2 class="stat-card__value">Weekly Streak</h2>
                    <ul class="streak">
                        <li class="streak__item streak__item--active">
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>

                        <li class="streak__item streak__item--active">
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>

                        <li class="streak__item">
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>

                        <li class="streak__item">
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>

                        <li class="streak__item">
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>

                        <li class="streak__item">
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>
                    </ul>
                </article>
            </section>

            <section class="class-preview">
                <h2 class="class-preview__title">NEXT CLASS</h2>
                <ul class="class-preview__details">
                    <li>CrossFit Advanced</li>
                    <li>
                        <img src="../assets/icons/separator.svg" alt="">
                        Thursday 09:30
                    </li>
                    <li>
                        <img src="../assets/icons/separator.svg" alt="">
                        Studio B
                    </li>
                    <li>
                        <img src="../assets/icons/separator.svg" alt="">
                        Trainer: Quim Barreiros
                    </li>
                </ul>
            </section>

            <section class="activity">
                <h2>RECENT ACTIVITY</h2>

                <ul class="activity-list">
                    <li class="activity-list__item">
                        <span class="activity-list__name">Yoga Flow</span>
                        <span class="activity-list__date">Mon 28 Mar</span>
                        <span class="activity-list__status status status--completed">Completed</span>
                    </li>

                    <li class="activity-list__item">
                        <span class="activity-list__name">HITT Burn</span>
                        <span class="activity-list__date">Sat 26 Mar</span>
                        <span class="activity-list__status status status--completed">Completed</span>
                    </li>

                    <li class="activity-list__item">
                        <span class="activity-list__name">Mobility Reset</span>
                        <span class="activity-list__date">Thu 23 Mar</span>
                        <span class="activity-list__status status status--missed">Missed</span>
                    </li>
                </ul>
            </section>

        </main>

        <?php include '../components/footer.php'; ?>

    </body>
</html>
