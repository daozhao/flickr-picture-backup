=== Plugin Name ===
Contributors: daozhao chen
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5855418
Tags: page, flickr, images, picture, backup,
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: 0.6

Backup flickr's picture which in page/post External links to flickr's picture.you can change the external links of flickr's picture to internal links.

== Description ==

Backup flickr's picture which in page/post External links to flickr's picture. when flickr change the privacy or stop the service,you can change the external links of flickr's picture to internal links.for example:
 &lt;img src="http://farm4.static.flickr.com/3456/3404575364_aea19a9ab3_s.jpg" /&gt; 
 change to 
 &lt;img src="http://yourdomain/wp-content/uploads/flickr_backup/3404575364_aea19a9ab3_s.jpg" /&gt;
 0.6 version update:
 1. add support &lt;a&gt; link href change,easy to support some lightbox plugins.
 &lt;a href="http://farm4.static.flickr.com/3456/3404575364_aea19a9ab3_s.jpg" &gt; 
 change to 
 &lt;a href="http://yourdomain/wp-content/uploads/flickr_backup/3404575364_aea19a9ab3_s.jpg" &gt;

== Installation ==

1. Upload `flickr-picture-backup.php`,`flickr-picture-download.php` to the `/wp-content/plugins/flickr-picture-backup` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to setting pages "Flickr picture backup options",and click "download all picture to host".
4. when you need to change the external links of flickr's picture to internal links, checked "Change flickr's picture external links to internal links" and click "save changes".



== Screenshots ==

Steps

1. setting page
2. uncheck "Change flickr's picture external links to internal links"
3. checked "Change flickr's picture external links to internal links"


