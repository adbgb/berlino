<?php

if (is_home()) {
    if (current_user_can('edit_pages'))
        update_rss_contents();
    else
        echo "Spazio per contenuti di spicco, selezioni dalle sottopagine e altro (immagini, banners, avvisi..)";
}
elseif (!empty($_GET['sub'])) {
    $post = get_post();
    if (($post->post_title == 'Rassegna stampa')
        || ($post->post_title == 'Temi importanti')) {
        ?>
        <h2>
            <?php echo $post->post_title ?> &raquo; <?php echo stripslashes($_GET['sub']) ?>
        </h2>
        <br />
        <?php echo $post->post_content ?>
        <br />
        <?php
        // contenuto in base al sub
    }
}
else {
    $post = get_post();
    ?>
    <h2>
        <a href="<?php echo get_site_url() ?>">Home</a> &raquo; <?php echo $post->post_title ?>
    </h2>
    <br />
    <?php echo nl2br($post->post_content) ?>
    <?php
}

//echo "<br /><br /><br />"; var_dump("<pre>", get_post(), "</pre>");

?>
