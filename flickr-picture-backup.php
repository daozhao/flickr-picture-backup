<?php
/**
 * Plugin Name: flickr picture backup
 * Plugin URI: http://daozhao.goflytoday.com/flickr-picture-backup-plugin-for-wordpress-plugin/
 * Description: Backup flickr's picture which in page/post External links to flickr's picture.you can change the external links of flickr's picture to internal links.
 * Version: 0.7
 * Author: daozhao chen 
 * Author URI: http://daozhao.goflytoday.com
 *
 * @copyright 2011
 * @version 0.7
 * @author daozhao chen
 * @link http://daozhao.goflytoday.com/
 * @license 
 *
 */

add_filter('the_content', 'wp_daozhao_flickr_picture_repalce');
add_filter('the_excerpt', 'wp_daozhao_flickr_picture_repalce');

function wp_daozhao_flickr_picture_repalce($content=""){
    if ( is_preview() ) {
        return $content;
    }
	$wp_option = get_option("wp_daozhao_flickr_picture_backup");
	$wp_ref = ($wp_option["ref"] == 'yes') ? 1 : 0;
	if ( $wp_ref )
    {
        $http_path = wp_daozhao_flickr_backup_urlpath();
        /*
         preg_match_all('/<img.+?src="(http:\/\/.+?\.static\.flickr\.com\/.+?\/.+?)".*?\/>/',$content,$rt);
         preg_match_all('/<a.+?href="(http:\/\/farm.+?\.static\.flickr\.com\/.+?\/.+?)".*?>/',$content,$rt);
         preg_match_all('/<a.+?rev=".+?href:\'(http:\/\/.+?\.static\.flickr\.com\/.+?\/.+?)\'.+?".*?>/',$content,$rt);
        */
		//macth this style : http://farm7.static.flickr.com/6118/6234551388_b7084b55c0.jpg
        $content = preg_replace(array('/<img(.+?)src="http:\/\/(.+?)\.static\.flickr\.com\/(.+?)\/(.+?)"(.*?)>/'
                                      ,'/<a(.+?)href="http:\/\/(farm.+?)\.static\.flickr\.com\/(.+?)\/(.+?)"(.*?)>/'
                                      ,'/<a(.+?)rev="(.*?)href:\'http:\/\/(.+?)\.static\.flickr\.com\/(.+?)\/(.+?)\'(.*?)"(.*?)>/'
                                     )
                             ,array('<img \\1 src="' . $http_path . '\\4"  \\5>'
                                    ,'<a \\1 href="' . $http_path . '\\4"  \\5>'
                                    ,'<a \\1 rev="\\2href:\'' . $http_path . '\\5\'\\6" \\7>'
                                   )
                             //,'<img \\1 src="/\\2_\\3_\\4"  \\5 />'
                             ,$content);
		
		//macth this style : http://farm8.staticflickr.com/7144/6386744333_e123761678.jpg
		
		$content = preg_replace(array('/<img(.+?)src="http:\/\/(.+?)\.staticflickr\.com\/(.+?)\/(.+?)"(.*?)>/'
                                      ,'/<a(.+?)href="http:\/\/(farm.+?)\.staticflickr\.com\/(.+?)\/(.+?)"(.*?)>/'
                                      ,'/<a(.+?)rev="(.*?)href:\'http:\/\/(.+?)\.staticflickr\.com\/(.+?)\/(.+?)\'(.*?)"(.*?)>/'
                                     )
                             ,array('<img \\1 src="' . $http_path . '\\4"  \\5>'
                                    ,'<a \\1 href="' . $http_path . '\\4"  \\5>'
                                    ,'<a \\1 rev="\\2href:\'' . $http_path . '\\5\'\\6" \\7>'
                                   )
                             //,'<img \\1 src="/\\2_\\3_\\4"  \\5 />'
                             ,$content);
		    }
    
	return $content;
}

add_action('admin_menu', 'wp_daozhao_flickr_picture_backup_menu');

function wp_daozhao_flickr_picture_backup_menu()
{
  add_options_page('Flickr picture backup Option', 'Flickr picture backup Option', 8,basename(__FILE__), 'wp_daozhao_flickr_picture_backup_options');
}

function wp_daozhao_get_flickr_picture_list()
{
    global $wpdb;
    $where = '';
    $post_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts $where ORDER BY post_date_gmt desc");
    //$post_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts $where ORDER BY post_date_gmt ASC");
    $flickr_url_ary = array();
    if ($post_ids) {
            $i = 0;
            global $wp_query;
            $wp_query->in_the_loop = true;  // Fake being in the loop.
            // fetch 20 posts at a time rather than loading the entire table into memory
            while ( $next_posts = array_splice($post_ids, 0, 20) ) {
                $where = "WHERE ID IN (".join(',', $next_posts).")";
                $posts = $wpdb->get_results("SELECT * FROM $wpdb->posts $where ORDER BY post_date_gmt desc");
                //$posts = $wpdb->get_results("SELECT * FROM $wpdb->posts $where ORDER BY post_date_gmt ASC");
                    foreach ($posts as $post) {
                        // Don't export revisions.  They bloat the export.
                        if ( 'revision' == $post->post_type )
                            continue;
                        setup_postdata($post);
                        $title = apply_filters('the_title_rss', $post->post_title);
                        $content = apply_filters('the_content_export', $post->post_content);
                        $url_ary = array();
                        
                        /*
                        preg_match_all('/<img.+?src="(http:\/\/.+?\.static\.flickr\.com\/.+?\/.+?)".*?\/>/',$content,$rt);
                        */
						//macth this style : http://farm7.static.flickr.com/6118/6234551388_b7084b55c0.jpg
                        preg_match_all('/<img.+?src="(http:\/\/.+?\.static\.flickr\.com\/.+?\/.+?)".*?>/',$content,$rt);
                        $url_ary = array_unique(array_merge($url_ary,$rt[1]));
                        
                        preg_match_all('/<a.+?href="(http:\/\/farm.+?\.static\.flickr\.com\/.+?\/.+?)".*?>/',$content,$rt);
                        $url_ary = array_unique(array_merge($url_ary,$rt[1]));
                        
                        preg_match_all('/<a.+?rev=".*?href:\'(http:\/\/.+?\.static\.flickr\.com\/.+?\/.+?)\'.*?".*?>/',$content,$rt);
                        $url_ary = array_unique(array_merge($url_ary,$rt[1]));
						
						//macth this style : http://farm8.staticflickr.com/7144/6386744333_e123761678.jpg
                        preg_match_all('/<img.+?src="(http:\/\/.+?\.staticflickr\.com\/.+?\/.+?)".*?>/',$content,$rt);
                        $url_ary = array_unique(array_merge($url_ary,$rt[1]));
                        
                        preg_match_all('/<a.+?href="(http:\/\/farm.+?\.staticflickr\.com\/.+?\/.+?)".*?>/',$content,$rt);
                        $url_ary = array_unique(array_merge($url_ary,$rt[1]));
                        
                        preg_match_all('/<a.+?rev=".*?href:\'(http:\/\/.+?\.staticflickr\.com\/.+?\/.+?)\'.*?".*?>/',$content,$rt);
                        $url_ary = array_unique(array_merge($url_ary,$rt[1]));
						
						//print_r($rt);
                       // if ( count($rt[1]) > 0 )
                        if ( count($url_ary) > 0 )
                        {
                            //echo $title . "<br/>\r\n";
                            foreach ( $url_ary as $flickr_url )
                            {
                                //echo $i++ . " " . $flickr_url . "<br/>\r\n";
                                $flickr_url_ary[] = array( "url" => $flickr_url
                                                           ,"title" => $title
                                                           ,"id"   => $post->ID
                                                           ,"post_status" => $post->post_status
                                                         );
                            }
                        }
                        //print_r($rt);
                        //print_r($post);
                        ?>
                        <?php
                    }
            }
            //print_r($flickr_url_ary);
    }
    return $flickr_url_ary;
}

function wp_daozhao_flickr_backup_path()
{
    //global $wp_filesystem, $wp_filesystem;
    $dir = wp_upload_dir();
    $flick_backup_path = $dir['basedir'] . "/flickr_backup/";
    if ( !@file_exists($flick_backup_path))
    {
        @mkdir($flick_backup_path);
    }
    return $flick_backup_path;
}

function wp_daozhao_flickr_backup_urlpath()
{
    //global $wp_filesystem, $wp_filesystem;
    $dir = wp_upload_dir();
    return  $dir['baseurl'] . "/flickr_backup/";
}

function wp_daozhao_flickr_backupfile_exists($url,&$backupfile_url)
{
    $filename = basename($url);
    
    $flick_backup_path = wp_daozhao_flickr_backup_path();
    $backupfile_url = wp_daozhao_flickr_backup_urlpath() .  $filename;
    
    $flick_backup_file = $flick_backup_path . $filename;
    //echo $flick_backup_file . " <br/>\r\n";
    /*
    if ( !@file_exists($flick_backup_file) )
    {
        return false;
    }
    return true;
    */
    return @file_exists($flick_backup_file);
}

function wp_daozhao_download_flickr_picture( $url )
{
	//WARNING: The file is not automatically deleted, The script must unlink() the file.
	if ( ! $url )
		return new WP_Error('http_no_url', __('Invalid URL Provided'));

    //print_r($dir);
	//$tmpfname = wp_tempnam($url,"D:\\xampp\\htdocs\\wordpress271\\wp-content\\uploads\\flickr_bakcup\\");
    $flickr_backup_path = wp_daozhao_flickr_backup_path();
	$tmpfname = $flickr_backup_path .  basename($url)  ; //wp_tempnam($url,$flickr_backup_path);

	$response = wp_remote_get($url, array('timeout' => 300));
    //$response = "abc"

	if ( is_wp_error($response) ) {
        //echo "get url error<br/>\r\n";
		//fclose($handle);
		//unlink($tmpfname);
		return $response;
	}

	if ( $response['response']['code'] != '200' ){
		//fclose($handle);
		//unlink($tmpfname);
        //echo "http_404<br>\r\n";
		return new WP_Error('http_404', trim($response['response']['message']));
	}

    touch($tmpfname);
    
    //echo $tmpfname . "<br/>\r\n";
    //$tmpfname ="D:\xampp\htdocs\wordpress271\wp-content\uploads\flickr\abc.jpg"; 
	if ( ! $tmpfname )
    {
        //echo "Could not create file<br>\r\n";
		return new WP_Error('http_no_file', __('Could not create Temporary file') . $tmpfname);
    }

	$handle = @fopen($tmpfname, 'wb');
	if ( ! $handle )
    {
        //echo "Could not create file2<br>\r\n";
		return new WP_Error('http_no_file', __('Could not create Temporary file'));
    }


	fwrite($handle, $response['body']);
	fclose($handle);

	return $tmpfname;
}

function wp_daozhao_download_flickr_picture_list_bg($urllist)
{
    set_time_limit(180);
    $out = "";
    foreach ( $urllist as $item )
    {
        $out .= $item["url"] . "\n";
    }
    file_put_contents(dirname(__FILE__) . "/flickr-url-list.txt",$out);
    $command = "php " . dirname(__FILE__) . "/flickr-picture-download-bg.php > " . dirname(__FILE__) . "/backup-bg.log &";
    //$command = "php " . "flickr-picture-download-bg.php > " . dirname(__FILE__) .  "/backup-bg2.log &";
    //passthru($command,$return_value);
    //passthru($command);
    exec($command);
    //system($command);
    
    //echo $return_value;
    
}
function wp_daozhao_download_flickr_picture_list( $urllist ,$downloadagain = false )
{
    $i = 1;
    foreach ( $urllist as $item )
    {
        set_time_limit(180);
        if ( is_array($item) )
        {
            $url = $item["url"];
        }
        else
        {
            $url = $item;
        }
        echo "<tr>";
        echo "<td>$i</td><td> </td><td>$url</td><td>......</td>";
        if ( $downloadagain || !wp_daozhao_flickr_backupfile_exists($url,$backupfile_url) )
        {
            $fl = wp_daozhao_download_flickr_picture($url);
            if ( is_wp_error($fl) )
            {
                echo "<td>FALSE</td><td>" . " " . $fl->get_error_message() . "</td>";
            }
            else
            {
                echo "<td>OK</td><td></td>";
            }
        }
        else
        {
            echo "<td>Exists</td><td></td>";
        }
        echo '</tr>';
        $i++;
        //echo $url . "......OK<br/>\r\n";
        flush();
        //sleep(1);
    }
    set_time_limit(180);
}

function wp_daozhao_flickr_picture_list_head($content)
{
		return array(
				'cb' => '<input type="checkbox" />'
				,'url' => __('URL')
				,'title' => _c('Post|noun')
				//,'load' => __('Load')
				,'posts' => __('Exists')
				//,'comments' => __('Exists')
                );
    
}

add_filter('manage_flickr_picture_backup_columns',wp_daozhao_flickr_picture_list_head);

function _flickr_picture_backup_row( $tag, $class = '' ) {
		//$count = number_format_i18n( $tag->count );
		//$count = ( $count > 0 ) ? "<a href='edit.php?tag=$tag->slug'>$count</a>" : $count;

		$picture_url = $tag['url']; // apply_filters( 'term_name', $tag->name );
        $picture_id  = str_replace(".","",basename($picture_url));
        $picture_exists = wp_daozhao_flickr_backupfile_exists($picture_url,$picture_host_url);
		//$qe_data = get_term($tag->term_id, 'post_tag', object, 'edit');
		//$edit_link = "edit-tags.php?action=edit&amp;tag_ID=$tag->term_id";
        
		$out = '';
		$out .= '<tr id="tag-' . $tag->term_id . '"' . $class . '>';
		$columns = get_column_headers('flickr_picture_backup');
		$hidden  = get_hidden_columns('flickr_picture_backup');
		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class=\"$column_name column-$column_name\"";

			$style = '';
			if ( in_array($column_name, $hidden) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			switch ($column_name) {
				case 'cb':
					$out .= '<th scope="row" class="check-column"> <input type="checkbox" name="download_tags[]" value="' . $picture_url . '" /></th>';
					break;
				case 'url':
					$out .= '<td ' . $attributes . '><strong><a name="' . $picture_id . '">' . $picture_url . '</a></strong><br />';
                    //$out .= $picture_host_url . "<br/>";
					$actions = array();
					$actions['download'] = '<a style="cursor:pointer"  class="download_picutre" picture_url="' . $picture_url . '" picture_id="' . $picture_id . '" >'
                                            . __('Download to host ') . ($picture_exists ? "again" : " and view") . '</a>';
                    if ( $picture_exists )
                    {
    					$actions['view'] = '<a style="cursor:pointer" class="view_picutre" picture_url="' . $picture_host_url . '" picture_id="' . $picture_id . '" >'
                                            . __('View') . '</a>';
                    }
					//$actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url("edit-tags.php?action=delete&amp;tag_ID=$tag->term_id", 'delete-tag_' . $tag->term_id) . "' onclick=\"if ( confirm('" . js_escape(sprintf(__("You are about to delete this tag '%s'\n 'Cancel' to stop, 'OK' to delete."), $name )) . "') ) { return true;}return false;\">" . __('Delete') . "</a>";
					$action_count = count($actions);
					$i = 0;
					$out .= '<div class="row-actions">';
					foreach ( $actions as $action => $link ) {
						++$i;
						( $i == $action_count ) ? $sep = '' : $sep = ' | ';
						$out .= "<span class='$action'>$link$sep</span>";
					}
					$out .= '</div>';
                    $out   .= '</td>';
					break;
				case 'title':
                    $postID = $tag["id"]; 
                	$edit_link = get_edit_post_link( $postID );
                    if ( empty($tag['title']) )
                        $title = __('(no title)');
                    else
                    	$title = $tag['title'];
                    $attributes = 'class="post-title column-title"' . $style;
                    $out .= "<td $attributes ><strong>";
                    if ( current_user_can( 'edit_post', $postID ) )
                    {
                        $out .= "<a class=\"row-title\" href=\"$edit_link\" title=\""
                                . attribute_escape(sprintf(__('Edit "%s"'), $title))
                                . "\">$title</a>";
                    }
                    else
                    {
                        $out .= $title;
                    }
                     //_post_states($post); 
                    $out .= "</strong>";
                    //if ( 'excerpt' == $mode )
                    //    the_excerpt();
        
                    $actions = array();
                    if ( current_user_can('edit_post', $post->ID) ) {
                        $actions['edit'] = '<a href="' . get_edit_post_link($postID, true) . '" title="' . attribute_escape(__('Edit this post')) . '">' . __('Edit') . '</a>';
                        //$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . attribute_escape(__('Edit this post inline')) . '">' . __('Quick&nbsp;Edit') . '</a>';
                        //$actions['delete'] = "<a class='submitdelete' title='" . attribute_escape(__('Delete this post')) . "' href='" . wp_nonce_url("post.php?action=delete&amp;post=$post->ID", 'delete-post_' . $post->ID) . "' onclick=\"if ( confirm('" . js_escape(sprintf( ('draft' == $post->post_status) ? __("You are about to delete this draft '%s'\n 'Cancel' to stop, 'OK' to delete.") : __("You are about to delete this post '%s'\n 'Cancel' to stop, 'OK' to delete."), $post->post_title )) . "') ) { return true;}return false;\">" . __('Delete') . "</a>";
                    }
                    if ( in_array($tag["post_status"], array('pending', 'draft')) ) {
                        if ( current_user_can('edit_post', $postID) )
                            $actions['view'] = '<a href="' . get_permalink($postID) . '" title="' . attribute_escape(sprintf(__('Preview "%s"'), $title)) . '" rel="permalink">' . __('Preview') . '</a>';
                    } else {
                        $actions['view'] = '<a href="' . get_permalink($postID) . '" title="' . attribute_escape(sprintf(__('View "%s"'), $title)) . '" rel="permalink">' . __('View') . '</a>';
                    }
                    $action_count = count($actions);
                    $i = 0;
                    //<div class=\"alignleft actions\">
                    $out .= '<div class="row-actions">';
                    foreach ( $actions as $action => $link ) {
                        ++$i;
                        ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
                        $out .= "<span class='$action'>$link$sep</span>";
                    }
                    $out .= '</div>';
                    $out .= '</td>';
					break;
				case 'posts':
				//case 'comments':
				//case 'load':
					$attributes = 'class="comments column-comments"' . $style;
					//$attributes = 'class="post-title column-title"' . $style;
					$out .= "<td $attributes><img id=\"sts_img_" . $picture_id . '" src="'
                            . site_url('') .   '/wp-admin/images/'
                            . ($picture_exists ? "yes.png" : "no.png") . '" /></td>';
					break;
			}
		}

		$out .= '</tr>';
        // style="display:none;"
        $out .= '<tr id="tr_' . $picture_id . '" ' . $class . ' style="display:none;" ><th></th><td id="td_' . $picture_id . '" colspan="2"><td></tr>';
		return $out;
}

function flickr_picture_backup_rows($url_list, $page = 1, $pagesize = 20, $searchterms = '' ) {

	// Get a page worth of tags
	$start = ($page - 1) * $pagesize;

	//$args = array('offset' => $start, 'number' => $pagesize, 'hide_empty' => 0);

	if ( !empty( $searchterms ) ) {
		$args['search'] = $searchterms;
	}

	//$tags = get_terms( 'post_tag', $args );
    //$url_list = wp_daozhao_get_flickr_picture_list(); 

	// convert it to table rows
	$out = '';
	$count = 0;
	//foreach( $url_list as $tag )
    for ( $i = $start ; $i < $start+$pagesize ; $i++)
    {
        if ( isset($url_list[$i]) )
        {
            $tag = $url_list[$i];
            $class = ++$count % 2 ? ' class="iedit alternate"' : ' class="iedit"';
            $out .= _flickr_picture_backup_row( $tag, $class );
        }
    }

	// filter and send to screen
	echo $out;
	return $count;
}

function wp_daozhao_flickr_picture_backup_warning()
{
    
    return  '<div id="div_warning"><p><strong>' .
				__('DO NOT DO THE FOLLOWING AS IT WILL CAUSE YOUR BACKUP TO FAIL:').
			'</strong></p>
			<ol>
				<li>'.__('Close this browser').'</li>
				<li>'.__('Reload this page').'</li>
				<li>'.__('Click the Stop or Back buttons in your browser').'</li>
			</ol>
            <p>please wait...<img src="' . site_url('') .   '/wp-admin/images/loading-publish.gif" /></P>
            </div>';    
    
}

function wp_daozhao_flickr_picture_backup_download($message,$url_list,$downloadagain=false)
{
    //$message = "Download some flickr's picture to host.";
    echo '<div id="message" class="updated fade"><p>'.$message.'.</p>';
    echo wp_daozhao_flickr_picture_backup_warning();
    echo "<table>";
    flush();
    wp_daozhao_download_flickr_picture_list($url_list,$downloadagain);
    echo '</table>';
    echo '</div>';
}

function wp_daozhao_flickr_picture_backup_options()
{
    global $wpdb, $post_ids, $post;
    
    $url_list = wp_daozhao_get_flickr_picture_list();
    
    if( isset($_POST["savechanges"]) )
    {
        $wp_option = array();
        $wp_option["ref"] = $_POST["autoref"];
        update_option('wp_daozhao_flickr_picture_backup',$wp_option);
        $message = "Flickr picture backup option had update" ;
      	echo '<div id="message" class="updated fade"><p>'.$message. '.</p>';
        //print_r($wp_option);
        echo '</div>';
    }
    else if( isset($_POST["doaction"]) || isset($_POST["doaction2"])  )
    {
        //echo "in action or action1";
        if ( 'download' == $_POST["action"] || 'download' == $_POST["action2"] )
        {
            $message = "Download some flickr's picture to host.";
            wp_daozhao_flickr_picture_backup_download($message,$_POST["download_tags"],false);
            /*
            $message = "Download some flickr's picture to host.";
        	echo '<div id="message" class="updated fade"><p>'.$message.'.</p>';
            echo wp_daozhao_flickr_picture_backup_warning();
            echo "<table>";
            flush();
            wp_daozhao_download_flickr_picture_list($_POST["download_tags"]);
            echo '</table>';
            //print_r($_POST["delete_tags"]);
            echo '</div>';
            */
        }
        else if ( 'downloadagain' == $_POST["action"] || 'downloadagiain' == $_POST["action2"] )
        {
            $message = "Download some flickr's picture to host again.";
            wp_daozhao_flickr_picture_backup_download($message,$_POST["download_tags"],true);
            /*
            $message = "Download some flickr's picture to host.";
        	echo '<div id="message" class="updated fade"><p>'.$message.'.</p>';
            echo wp_daozhao_flickr_picture_backup_warning();
            echo "<table>";
            flush();
            wp_daozhao_download_flickr_picture_list($_POST["download_tags"],true);
            echo '</table>';
            //print_r($_POST["delete_tags"]);
            echo '</div>';
            */
        }
    }    
    else if( isset($_POST["downloadallpicture"]) )
    {
        $message = "Download all flickr's picture to host.";
        wp_daozhao_flickr_picture_backup_download($message,$url_list,false);
        /*
        echo '<div id="message" class="updated fade"><p>'.$message.'.</p>';
        echo wp_daozhao_flickr_picture_backup_warning();
        echo "<table>";
        flush();
        wp_daozhao_download_flickr_picture_list($url_list);
        echo "</table>";
        //print_r($_POST["delete_tags"]);
        echo '</div>';
        */
    }
    else if( isset($_POST["downloadallpicturebg"]) )
    {
        $message = "Download all flickr's picture to host at background,please wait...";
        echo '<div id="message" class="updated fade"><p>'.$message.'.</p>';
        echo '<div id="bg-info"></div>';
        echo '<a id="get-bg-info" href="#">Get down log information</a>';
        flush();
        wp_daozhao_download_flickr_picture_list_bg($url_list);
        echo '</div>';
    }
    else if($_POST["url"]){
        //echo "OK";
        //return true;
    }
    else
    {
		//$wp_daozhao_DTD_saved = get_option("wp_daozhao_DTD");
    }
    
?>

<h2>Flickr picture backup options</h2>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="5855418">
<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/zh_XC/i/scr/pixel.gif" width="1" height="1">
</form>

    <form method="post" action="<?php echo site_url('') . '/wp-admin/options-general.php' ?><?php //echo $_SERVER['PHP_SELF']; ?>?page=<?php echo basename(__FILE__); ?>&pagenum=<?php echo (isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1); ?>">
    <input type="checkbox" <?php $wp_option = get_option("wp_daozhao_flickr_picture_backup"); echo $wp_option["ref"]=="yes" ? 'checked="ture"' : ''; ?> name="autoref" value="yes" />
    <?php _e("Change flickr's picture external links to internal links"); ?>
    <input type="submit" value="<?php _e('Save Changes'); ?>" name="savechanges" id="savechanges" class="button-secondary action" />
    <hr/>
    <div class="alignright actions">
    <input type="submit" value="<?php _e('Download all flickr picture to host'); ?>" name="downloadallpicture" id="downloadallpicture" class="button-secondary action" />
    <!--
    <input type="submit" value="<?php _e('Download all flickr picture to host @ background'); ?>" name="downloadallpicturebg" id="downloadallpicturebg" class="button-secondary action" />
    -->
    </div>
    <h3>flickr picture list:</h3>
    <div class="clear"></div>
        <div class="tablenav">
<?php
        $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 0;
        if ( empty($pagenum) )
            $pagenum = 1;
        
        $tagsperpage = 20 ; //apply_filters("tagsperpage",20);
        //$url_list = wp_daozhao_get_flickr_picture_list(); 
        $page_links = paginate_links( array(
            'base' => add_query_arg( 'pagenum', '%#%' ),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => ceil( count($url_list) / $tagsperpage),
            'current' => $pagenum
        ));
        
        if ( $page_links )
            echo "<div class='tablenav-pages'>$page_links</div>";
?>
                    
            <div class="alignleft actions">
            <select name="action">
                <option value="" selected="selected"><?php _e('Bulk Actions'); ?></option>
                <option value="download"><?php _e('Download'); ?></option>
                <option value="downloadagain"><?php _e('Download again'); ?></option>
            </select>
                <input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
            </div>
        </div>
        
        <table class="widefat tags fixed" cellspacing="0">
            <thead>
            <tr>
        <?php print_column_headers('flickr_picture_backup'); ?>
            </tr>
            </thead>
        
            <tfoot>
            <tr>
        <?php print_column_headers('flickr_picture_backup', false); ?>
            </tr>
            </tfoot>
        
            <tbody id="the-list" class="list:tag">
        <?php
            //$searchterms = isset( $_GET['s'] ) ? trim( $_GET['s'] ) : '';
            //$count = tag_rows( $pagenum, $tagsperpage, $searchterms );
            flickr_picture_backup_rows($url_list,$pagenum,$tagsperpage);
        ?>
            </tbody>
        </table>

<div class="tablenav">
<?php
if ( $page_links )
	echo "<div class='tablenav-pages'>$page_links</div>";
?>
        
    <select name="action2">
        <option value="" selected="selected"><?php _e('Bulk Actions'); ?></option>
        <option value="download"><?php _e('Download'); ?></option>
    </select>
    <input type="submit" value="<?php _e('Apply'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />        
</div>


</form>
        
<script type="text/javascript">
/* <![CDATA[ */
(function($){
    
$(document).ready(function(){
		$('.view_picutre').click(function(){
                                    //alert("editinlon");
                                    url = $(this).attr("picture_url");
                                    tr_id = $(this).attr("picture_id");
                                    //$("#tr_" + tr_id).show();
                                    //alert(url);
                                    $("#tr_" + tr_id).toggle();
                                    if ( $("#tr_" + tr_id).css("display") == "none" )
                                    {
                                        //alert("close");
                                        $(this).text(" <?php echo __('View') ?>");
                                    }
                                    else
                                    {
                                        $(this).text(" <?php echo __('Close') ?>");
                                        //alert("open");
                                    }
                                    imgcontent = '<div class="alignleft actions"><img src="' + url + '" /></div>';
                                    $("#td_" + tr_id ).html(imgcontent);
                                    //$(this).focus();
                                    
                               });
        
		$('.download_picutre').click(function(){
                                    //alert("editinlon");
                                    url = $(this).attr("picture_url");
                                    tr_id = $(this).attr("picture_id");
                                    //alert($(this).html());
                                    if ( $(this).html() != "Loading..." )
                                    {
                                    //$("#tr_" + tr_id).show();
                                        $("#tr_" + tr_id).show();
                                        $("#sts_img_" + tr_id).attr("src","<?php echo site_url('') .   '/wp-admin/images/loading-publish.gif' ?>");
                                        alink = $(this);
                                        alink_str = $(this).html();
                                        $(this).html("Loading...");
                                        $.post("<?php echo site_url('') .'/wp-content/plugins/flickr-picture-backup/flickr-picture-download.php'; ?>"
                                               ,{"url": url}
                                               ,function(data)
                                                {
                                                    alink.html(alink_str);
                                                    if ( data.substr(0,2) == "OK" )
                                                    {
                                                        $("#sts_img_" + tr_id).attr("src","<?php echo site_url('')  .   '/wp-admin/images/yes.png' ?>");
                                                        imgcontent = '<div class="alignleft actions"><img src="' + data.substr(3) + '" /></div>';
                                                        //imgcontent = '<img src="' + data.substr(3) + '" />';
                                                        $("#td_" + tr_id ).html(imgcontent);
                                                    }
                                                    else
                                                    {
                                                        $("#sts_img_" + tr_id).attr("src","<?php echo site_url('') .   '/wp-admin/images/no.png' ?>");
                                                        alert("Download error.\r\n" + data);
                                                    }
                                                    //alert(tr_id + " \r\n" + data);
                                                }
                                              )
                                    }
                                    else
                                    {
                                        $("#tr_" + tr_id).toggle();
                                        //alert("Download");
                                    }
                                    
                               });
        $('#get-bg-info').click(function(){
                                    $.get("<?php echo site_url('') .'/wp-content/plugins/flickr-picture-backup/backup-bg.log'; ?>"
                                          ,function(data)
                                            {
                                                $('#bg-info').html(data);
                                                $('#get-bg-info').focus();
                                            }
                                         );
                                });
        $('#div_warning').hide();
        })
})(jQuery);

function download_click(url,tr_id)
{
    //alert("dwnload on click:" + url + "\r\n tr id:" + tr_id);
    /*
    jQuery("#tr_" + tr_id ).show();
    imgcontent = '<img src="' + url + '" />';
    jQuery("#td_" + tr_id ).html(imgcontent);
    */
//    jQuery("#td_" + tr_id ).focus();
}
    
/* ]]> */
</script>

<?php
}

?>
