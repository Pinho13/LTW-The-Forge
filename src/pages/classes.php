<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/session.php');

$session = new Session();
$session->requireLogin('/src/pages/index.php?open=login');
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/classes.css">
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

    <?php $activePage = 'classes'; include '../components/side-menu.php'; ?>

    <main>
        <section class="calendar">

            <nav class="calendar-nav" aria-label="Week navigation">
                <a href="#">&#8592; Prev</a>
                <h2>Apr 19 &ndash; Apr 25</h2>
                <a href="#">Next &#8594;</a>
            </nav>

            <div class="calendar-grid">

                <div class="calendar-time-spacer"></div>

                <div class="calendar-day-header">
                    <span class="day-name">SUN</span>
                    <span class="day-date">19</span>
                </div>
                <div class="calendar-day-header">
                    <span class="day-name">MON</span>
                    <span class="day-date">20</span>
                </div>
                <div class="calendar-day-header">
                    <span class="day-name">TUE</span>
                    <span class="day-date">21</span>
                </div>
                <div class="calendar-day-header today">
                    <span class="day-name">WED</span>
                    <span class="day-date">22</span>
                </div>
                <div class="calendar-day-header">
                    <span class="day-name">THU</span>
                    <span class="day-date">23</span>
                </div>
                <div class="calendar-day-header">
                    <span class="day-name">FRI</span>
                    <span class="day-date">24</span>
                </div>
                <div class="calendar-day-header">
                    <span class="day-name">SAT</span>
                    <span class="day-date">25</span>
                </div>

                <div class="calendar-time-gutter">
                    <div class="calendar-time-label">8 AM</div>
                    <div class="calendar-time-label">9 AM</div>
                    <div class="calendar-time-label">10 AM</div>
                    <div class="calendar-time-label">11 AM</div>
                    <div class="calendar-time-label">12 PM</div>
                    <div class="calendar-time-label">1 PM</div>
                    <div class="calendar-time-label">2 PM</div>
                    <div class="calendar-time-label">3 PM</div>
                    <div class="calendar-time-label">4 PM</div>
                    <div class="calendar-time-label">5 PM</div>
                    <div class="calendar-time-label">6 PM</div>
                    <div class="calendar-time-label">7 PM</div>
                    <div class="calendar-time-label">8 PM</div>
                </div>

                <div class="calendar-day-column"></div>
                <div class="calendar-day-column"></div>
                <div class="calendar-day-column"></div>

                <div class="calendar-day-column">
                    <article class="class-card" style="grid-row: 1 / span 4">
                        <header>
                            <h3>Yoga</h3>
                            <p>8:00 &ndash; 9:00 AM</p>
                        </header>
                        <footer>
                            <div class="intensity-dots">
                                <span class="intensity-dot filled"></span>
                                <span class="intensity-dot filled"></span>
                                <span class="intensity-dot"></span>
                                <span class="intensity-dot"></span>
                                <span class="intensity-dot"></span>
                            </div>
                            <span>14 spots left</span>
                        </footer>
                    </article>
                </div>

                <div class="calendar-day-column">
                    <article class="class-card" style="grid-row: 41 / span 3">
                        <header>
                            <h3>HIIT Blast</h3>
                            <p>6:00 &ndash; 6:45 PM</p>
                        </header>
                        <footer>
                            <div class="intensity-dots">
                                <span class="intensity-dot filled"></span>
                                <span class="intensity-dot filled"></span>
                                <span class="intensity-dot filled"></span>
                                <span class="intensity-dot filled"></span>
                                <span class="intensity-dot"></span>
                            </div>
                            <span>11 spots left</span>
                        </footer>
                    </article>
                </div>

                <div class="calendar-day-column">
                    <article class="class-card class-card--full" style="grid-row: 41 / span 3">
                        <header>
                            <h3>HIIT Blast</h3>
                            <p>6:00 &ndash; 6:45 PM</p>
                        </header>
                        <footer>
                            <div class="intensity-dots">
                                <span class="intensity-dot filled"></span>
                                <span class="intensity-dot filled"></span>
                                <span class="intensity-dot filled"></span>
                                <span class="intensity-dot filled"></span>
                                <span class="intensity-dot"></span>
                            </div>
                            <span>Full</span>
                        </footer>
                    </article>
                </div>

                <div class="calendar-day-column"></div>

            </div>
        </section>
    </main>

    <?php include '../components/footer.php'; ?>

</body>
</html>
