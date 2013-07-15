<meta charset="UTF-8" />
<meta name="viewport" content="user-scalable=no,width=device-width" />

<title>
    <?php echo get_bloginfo('name') ?> | <?php echo (is_home() ? "Home Page" : get_the_title()) ?>
</title>

<link href="<?php echo get_bloginfo('template_url') ?>/logos/logo_64x64.png" rel="icon" type="image/png" />
<link href="<?php echo get_bloginfo('template_url') ?>/logos/logo_64x64.png" rel="shortcut icon" type="image/png" />
<link href="<?php echo get_bloginfo('template_url') ?>/style.css" type="text/css" rel="stylesheet" />

<link href='http://fonts.googleapis.com/css?family=Spinnaker' rel='stylesheet' type='text/css' />

<script src="<?php echo get_bloginfo('template_url') ?>/jquery-1.9.1.min.js" type="text/javascript"></script>

<?php wp_head() ?>
