<!doctype html>
<html lang="<?php echo $lang; ?>">
    <head>
        <meta charset="utf-8">
        <title>The PHP View Engine</title>
    </head>
    <body>
        <div class="container">
            <header>
                <?php echo $this->render('layouts.header'); ?>
            </header>
            <main>
                <div class="time">
                    <?php date_default_timezone_set('UTC');
                        echo date('d.M.Y H:i:s'); ?>
                </div>
                <?php if ($error) { ?>
                    <div class="alert alert-danger">
                        <p><?php echo $errorMessage; ?></p>
                    </div>
                <?php } else if ($success) { ?>
                    <div class="alert alert-success">
                        <p><?php echo $successMessage; ?></p>
                    </div>
                <?php } ?>

                <?php /* User list */ ?>
                <ul class="users">
                    <?php foreach ($users as $user) { ?>
                        <li class="user"><?php echo $user['name']; ?>, <?php echo $user['email']; ?></li>
                    <?php } ?>
                </ul>
            </main>
            <footer>
                <?php echo $this->render('layouts.footer'); ?>
            </footer>
        </div>
    </body>
</html>
