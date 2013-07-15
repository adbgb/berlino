<?php global $structure ?>

<span class="sub-title" onClick="document.location.href='<?php echo get_site_url() ?>';">
    <span>
        <h2>Home Page &#8634;</h2>
    </span>
</span>

<span class="sep-tube"></span>

<?php

foreach ($structure as $key => $value) {
    if (is_array($value)) {
        ?>
        <span class="sub-title">
            <span>
                <h2><?php echo $key ?> &darr;</h2>
            </span>
        </span>
        <span class="sep-tube"></span>
        <div class="blockable" <?php if (get_the_title() != $key) echo 'style="display: none;"' ?>>
        <?php
        foreach ($value as $subvalue) {
            ?>
            <span class="sub-sub-title" onClick="document.location.href='<?php echo get_page_by_title($key)->guid ?>&sub=<?php echo urlencode($subvalue) ?>';">
                <span <?php if (!empty($_GET['sub']) && (stripslashes($_GET['sub']) == $subvalue)) echo 'class="selected"' ?>>
                    <h3><?php echo $subvalue ?> &rarr;</h3>
                </span>
            </span>
            <span class="sep-tube"></span>
            <?php
        }
        ?>
        </div>
        <?php
    }
    else {
        ?>
        <span class="sub-sub-title" onClick="document.location.href='<?php echo get_page_by_title($value)->guid ?>';" style="margin-left: 0;">
            <span <?php if (get_the_title() == $value) echo 'class="selected"' ?>>
                <h3><?php echo $value ?> &rarr;</h3>
            </span>
        </span>
        <span class="sep-tube"></span>
        <?php
    }
}

?>

<span class="sep-tube"></span>
