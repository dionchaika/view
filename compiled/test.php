<?php if (1 === $page) { ?>

    <p><?php echo $user['name']; ?></p>

<?php } else if (2 === $page) { ?>

    <p><?php echo $user['email']; ?></p>

<?php } else { ?>

    <p><?php echo $user['phones']; ?></p>

<?php } ?>
