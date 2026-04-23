<!DOCTYPE html>
    <html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>The Forge</title>
        <link rel="stylesheet" href="../style/base.css">
        <link rel="stylesheet" href="../style/layout.css">
        <link rel="stylesheet" href="../style/components/logo.css">
        <link rel="stylesheet" href="../style/components/footer.css">
        <link rel="stylesheet" href="../style/components/side-menu.css">
        <link rel="stylesheet" href="../style/my-account.css">
    </head>


    <body>
        <?php $activePage = 'account'; include '../components/side-menu.php'; ?>

        <main>
            <header>
                <h2>Welcome back, Tomás!</h2>
            </header>

            <section class="stats">
                <article class="card">
                    <h3 class="stat-number">12</h3>
                    <p class="stat-label">Classes this month</p>
                </article>

                <article class="card">
                    <h3 class="stat-number">4</h3>
                    <p class="stat-label">Upcoming Reservations</p>
                </article>

                <article class="card">
                    <h3 class="stat-label">Weekly Streak</h3>
                    <ul class="stat-streak">
                        <li>
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>

                        <li>
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>

                        <li>
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>

                        <li>
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>

                        <li>
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>

                        <li>
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>
                    </ul>
                </article>
            </section>

            <section class="next-class">
                <h3>Next Class</h3>
                <ul class="class-details">
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
                <h3>Recent Activity</h3>

                <ul class="activity-list">
                    <li class="activity-item">
                    <span class="activity-name">Yoga Flow</span>
                    <span class="activity-date">Mon 28 Mar</span>
                    <span class="status completed">Completed</span>
                    </li>

                    <li class="activity-item">
                    <span class="activity-name">HIIT Burn</span>
                    <span class="activity-date">Sat 26 Mar</span>
                    <span class="status completed">Completed</span>
                    </li>

                    <li class="activity-item">
                    <span class="activity-name">Mobility Reset</span>
                    <span class="activity-date">Thu 23 Mar</span>
                    <span class="status missed">Missed</span>
                    </li>
                </ul>
            </section>

        </main>

        <?php include '../components/footer.php'; ?>
    </body>
</html>
