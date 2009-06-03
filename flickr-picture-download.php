<?php
define('WP_ADMIN', TRUE);
require_once('../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');
//require_once("./flickr-picture-backup.php");
//echo "flickr-picture-download.php";
if($_POST["url"])
{
    $url = $_POST["url"];
    $fl = wp_daozhao_download_flickr_picture($url);
    if ( is_wp_error($fl) )
    {
		echo  "FALSE:" . $fl->get_error_message();
    }
    else
    {
        wp_daozhao_flickr_backupfile_exists($url,$returl);
        echo "OK:" . $returl ;
    }
    //echo wp_daozhao_flickr_backup_urlpath();
    //echo "OK";
}

?>