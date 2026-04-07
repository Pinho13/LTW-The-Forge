<!DOCTYPE html>
    <html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>The Forge</title>
        <link rel="stylesheet" href="../style/base.css">
        <link rel="stylesheet" href="../style/index.css">
        <link rel="stylesheet" href="../style/login.css">
        <link rel="stylesheet" href="../style/components/footer.css">
    </head>


    <body>
        <header>
            <nav id="top-nav-bar">
                <a href = "index.php"> ABOUT US</a>
                <a href = "index.php"> FACILITIES</a>

                <a href = "index.php" class="logo">
                    <img src="../assets/images/logo-no-bg.png" alt="" class="logo-img">
                    <span class="logo-text">THE FORGE</span>
                </a>

                <a href = "index.php"> PLANS</a>
                <div class="login-wrapper">
                    <button id="login-btn">LOG IN</button>
                </div>
            </nav>
        </header>


        <main>
            <div class="hero-content">
                <section class = "hero">
                    <h1>FORGE YOUR LIMITS</h1>
                    <h2>
                        <span>Every workout. Every session. Every day.</span>
                    </h2>
                    <a href = "index.php"> EXPLORE PLANS</a>
                </section>
            </div>



            <section class="photos-container">
                <div class="image left">
                    <img src="../assets/images/main-page/left-image.png" alt="Gym Equipment">
                </div>

                <div class="splitter">
                    <div class="border-left"></div>
                    <div class="slant">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                    <div class="border-right"></div>
                </div>

                <div class="image right">
                    <img src="../assets/images/main-page/right-image.webp" alt="People Training">
                </div>
            </section>
        </main>


        <dialog id="login-modal" class="auth-modal">
            <button class="modal-close-btn">&times;</button>
            <h1>WELCOME BACK</h1>
            <h2>Sign in to your account</h2>
            <form method="dialog">
                <label for="email">EMAIL ADDRESS</label>
                <input type="email" id="email" name="email" placeholder="example@gmail.com">

                <label for="password">PASSWORD</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password">
                    <button type="button" class="toggle-password">&#128065;</button>
                </div>

                <a href="index.php" class="forgot-password">Forgot your password?</a>

                <button type="submit" class="auth-submit-btn">SIGN IN</button>
            </form>
            <p>New member? <a href="#" id="open-register-btn">Register for free</a></p>
        </dialog>

        <dialog id="register-modal" class="auth-modal">
            <button class="modal-close-btn">&times;</button>
            <h1>WELCOME</h1>
            <h2>Register your account</h2>
            <form method="dialog">
                <label for="register-name">NAME</label>
                <input type="text" id="register-name" name="name" placeholder="Full Name">

                <label for="register-email">EMAIL ADDRESS</label>
                <input type="email" id="register-email" name="email" placeholder="example@gmail.com">

                <label for="register-password">PASSWORD</label>
                <div class="password-wrapper">
                    <input type="password" id="register-password" name="password">
                    <button type="button" class="toggle-password">&#128065;</button>
                </div>

                <label for="register-confirm-password">CONFIRM PASSWORD</label>
                <div class="password-wrapper">
                    <input type="password" id="register-confirm-password" name="confirm-password">
                    <button type="button" class="toggle-password">&#128065;</button>
                </div>

                <button type="submit" class="auth-submit-btn">REGISTER</button>
            </form>
            <p>Already have an account? <a href="#" id="open-login-btn">Sign In</a></p>
        </dialog>


        <?php include '../components/footer.php'; ?>
        
        <script src="../scripts/index.js"></script>
    </body>
</html>
