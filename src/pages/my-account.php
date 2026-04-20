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
            <h1>Welcome, User!</h1>
            <p>This is your dashboard where you can manage your account, view classes, trainers.</p>
        </main>


        <?php include '../components/footer.php'; ?>


    </body>
</html>
