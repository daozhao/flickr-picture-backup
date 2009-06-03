<?php
define('WP_ADMIN', TRUE);
//echo realpath("./");
require_once('../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');

//require_once("./flickr-picture-backup.php");
//echo "flickr-picture-download.php";
$urldata =file_get_contents(dirname(__FILE__) . "/flickr-url-list.txt");
$urllist = split("\n",$urldata);
$i = 1;
echo "<table>";
foreach( $urllist as $url)
{
    if ( strlen(trim($url)) > 0 )
    {
        
        echo "<tr>";
        echo "<td>$i</td><td> </td><td>$url</td><td>......</td>";
        /*
        $fl = wp_daozhao_download_flickr_picture($url);
        if ( is_wp_error($fl) )
        {
            echo "<td>FALSE</td><td>" . " " . $fl->get_error_message() . "</td>";
        }
        else
        {
            echo "<td>OK</td><td></td>";
        }
        */
        echo "<td>OK</td><td></td>";
        echo '</tr>';
        $i++;
        //echo wp_daozhao_flickr_backup_urlpath();
        //echo $url . "  OK\r";
        sleep(1);
    }
}
echo "</table>";
echo "<p>Done</p>";

?>