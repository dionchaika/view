<?php foreach ($users as $user) {
    echo $user['name'];
} ?>

<div class="user">
    <p><?php echo $user['name']; ?></p>
    <p><?php echo $user['email']; ?></p>
</div>

<?php echo $this->render('test.footer'); ?>
