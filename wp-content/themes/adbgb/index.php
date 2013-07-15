<?php require_once 'functions.php' ?>

<html>
    <head><?php get_template_part('head') ?></head>
    <body style="<?php if (is_admin()) echo 'margin-top: 20px; ' ?>background-image: url('<?php echo bloginfo('template_url') ?>/backgrounds/<?php echo rand (0, 2) ?>.jpg');">
        <div id="wrapper">
            <div id="header"><?php get_template_part('header') ?></div>
            <div id="nav"></div>
            <div id="content"><?php get_template_part('content') ?></div>
            <div id="sidebar"><?php get_template_part('sidebar') ?></div>
            <div id="footer"><?php get_template_part('footer') ?></div>
        </div>
        <script type="text/javascript" src="<?php echo bloginfo('template_url') ?>/javascript.js"></script>
    </body>
</html>
