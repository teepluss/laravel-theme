<!DOCTYPE html>
<html>
    <head>
        <title><?php echo Theme::get('title'); ?></title>
        <meta charset="utf-8">
        <meta name="keywords" content="<?php echo Theme::get('keywords'); ?>">
        <meta name="description" content="<?php echo Theme::get('description'); ?>">
        <?php echo Theme::asset()->styles(); ?>
        <?php echo Theme::asset()->scripts(); ?>
    </head>
    <body>
        <?php echo Theme::partial('header'); ?>

        <div class="container">
            <?php echo Theme::content(); ?>
        </div>

        <?php echo Theme::partial('footer'); ?>

        <?php echo Theme::asset()->container('footer')->scripts(); ?>
    </body>
</html>