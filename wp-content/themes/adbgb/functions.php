<?php

global $structure;
$structure = array(
    'Rassegna stampa' => array(
        'MoVimento5Stelle',
        'Politica Italiana',
        'Dove va l\'Europa?',
    ),
    'Temi importanti' => array(
        'Mafia in Europa',
        'Download / Links',
    ),
    'Attivitá AdBGB',
);

function update_rss_contents() {
    if ($result = mysql_query("SELECT * FROM adbgb_zeitungen WHERE last_check < DATE_SUB(NOW(), INTERVAL 1 HOUR) ORDER BY amount, UKEY"))
    {
        while ($row = mysql_fetch_assoc($result))
            $zeitungen[] = $row;
        mysql_free_result($result);
    }
    else
        echo mysql_error();

    if ($result = mysql_query("SELECT * FROM adbgb_themen WHERE is_active")) {
        while ($row = mysql_fetch_assoc($result))
            $themen[$row['UKEY']] = explode(',', $row['keywords']);
        mysql_free_result($result);
    }
    else
        echo mysql_error();

    //if (!ini_set("allow_url_include", '1'))
      //  var_dump(ini_get("allow_url_include"));
    //if (!ini_set("allow_url_fopen", '1'))
      //  var_dump(ini_get("allow_url_fopen"));

    $rsss = array();
    if (!empty($zeitungen)) {
        foreach ($zeitungen as $zeitung) {
            $rsss[$zeitung['ID']] = array();
            switch ($zeitung['parser']) {
                case 'xml' : {
                    //$xml = simplexml_load_string(file_get_contents($zeitung['address']));
                    $ch = curl_init($zeitung['address']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $xml = simplexml_load_string(curl_exec($ch));
                    foreach ($xml->channel->item as $obj)
                        $rsss[$zeitung['ID']][] = $obj;
                    mysql_query("UPDATE adbgb_zeitungen SET last_check = NOW() WHERE ID = " . $zeitung['ID']);
                    break;
                }
            }
        }
    }

    $artikel = array();
    foreach ($rsss as $zkey => $rssz) {
        foreach ($rssz as $rss) {
            $title = strtolower((string)$rss->title);
            $description = strtolower((string)$rss->description);
            $guid = strtolower((string)$rss->guid);
            foreach ($themen as $key => $keywords) {
                foreach ($keywords as $keyword) {
                    if (substr_count($title, $keyword)
                        || substr_count($description, $keyword)
                        || substr_count($guid, $keyword)) {
                        $artikel[] = array(
                            'title' => $rss->title,
                            'description' => $rss->description,
                            'link' => $rss->link,
                            'guid' => $rss->guid,
                            'zkey' => $zkey,
                        );
                    }
                }
            }
        }
    }

    if (!empty($artikel)) {
        foreach ($artikel as $entry) {
            if (($result = mysql_query("SELECT * FROM adbgb_artikel WHERE UKEY = '" . $entry['zkey'] . "-" . $entry['guid'] . "'"))
                && (!mysql_num_rows($result))) {
                if (!mysql_query("INSERT INTO adbgb_artikel SET
                                  id_zeitungen = " . $entry['zkey'] . ",
                                  UKEY = '" . $entry['zkey'] . "-" . $entry['guid'] . "',
                                  abstract = '" . addslashes(strip_tags($entry['title'] . "\n" . $entry['description'])) . "',
                                  link = '" . $entry['link'] . "'
                                  "))
                    echo mysql_error();
            }
        }
    }

    if ($result = mysql_query('SELECT * FROM adbgb_artikel WHERE visible ORDER BY timestamp DESC'))
        while ($row = mysql_fetch_assoc($result)) {
            ?>
            <div>
                <p>
                    <?php echo nl2br($row['abstract']) ?>
                </p>
                [<a href="<?php echo $row['link'] ?>" target="_blank">LINK</a>]
                |
                [<a href="<?php echo bloginfo('template_url') ?>/edits.php?action=artikel_del&id=<?php echo $row['ID'] ?>">DEL</a>]
            </div>
            <?php
        }
    mysql_free_result($result);

    var_dump("<pre>", $artikel , "</pre>");
}
