<?php
/*
Plugin Name: Buddy Love
Plugin URI: http://www.dyers.org/blog/buddy-love-wordpress-widget/
Description: Creates a news feed consisting of the latest headlines pulled from a random sampling of the feeds listed in your WordPress Blogroll.
Author: Jon Dyer
Version: 1.13
Author URI: http://www.dyers.org/blog/
*/
require_once(ABSPATH . WPINC . '/rss-functions.php');
function widget_buddylove_init() {

if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') ) 
		return;
		
		function widget_buddylove($args) {
		
			// "$args is an array of strings that help widgets to conform to
			// the active theme: before_widget, before_title, after_widget,
			// and after_title are the array keys." - These are set up by the theme
			extract($args);

			// These are our own options
			$options = get_option('widget_buddylove');
			$title = $options['title'];  // Title in sidebar for widget
			$number = $options['show'];  // # of Posts we are showing
			$use_nofollow = $options['use_nofollow'] ? '1': '0';//If links are not trusted (paid links), they can be set to nofollow.
			if (!$number || $number<1) $number = 5;
			if (!$title) $title = 'Buddy Love';
			
		// Output
			
			global $wpdb;
			$querystr = "SELECT link_url, link_name, link_target, link_image, link_rss FROM $wpdb->links  WHERE $wpdb->links.link_visible = 'Y' ORDER BY rand() LIMIT $number";	

			
			$blwlinks = $wpdb->get_results($querystr, OBJECT);
			
			echo $before_widget . $before_title . $title . $after_title;

			echo '<ul>';
			if (!empty($blwlinks)) {
				foreach ($blwlinks as $blwlink) {
					$site_name = $blwlink->link_name;
					$link_image = $blwlink->link_image;
					if (!$link_image){$link_image=get_bloginfo('wpurl').'/wp-content/plugins/buddy-love/bldefault.png';}
					$link_target = $blwlink->link_target;
					$link_rss = $blwlink->link_rss;
					#try {
					#	$feedfile = new SimpleXMLElement($link_rss, null, true);
					#	$story_title = $feedfile->channel->item->title;
					#	$story_link = $feedfile->channel->item->link;
					#} catch (Exception $e) {
					#	// handle the error
					#}
					// Get RSS Feed(s)

					$feedfile = fetch_rss($link_rss);
					if (!empty($feedfile->items)){
						$story_title = $feedfile->items[0]['title'];
						$story_link = $feedfile->items[0]['link'];							
						echo '<li><p>'.$site_name.'<br/><img align="left" height="40px" width="40px" src="'.$link_image.'" alt="" title="'.$link_name.'" /> <a';
						if ($use_nofollow){
							echo ' rel="nofollow"';
						}
						echo ' target="'.$link_target.'" href="'.$story_link.'" title="Click to read '.$story_title.' at'.$site_name.'">'.$story_title.'</a></p></li><div style="clear:both"></div>';
					}		
				}
			} else echo "<li>No Blogroll Links</li>";
			echo '</ul>';
			echo $after_widget;
		}

		// Settings form
	function widget_buddylove_control() {

		// Get options
		$options = get_option('widget_buddylove');
		// options exist? if not set defaults
		if ( !is_array($options) )
			$options = array('title'=>'Number of Posts', 'show'=>5);
		
		// form posted?
		if ( $_POST['buddylove-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['buddylove-title']));
			$options['show'] = strip_tags(stripslashes($_POST['buddylove-show']));
			$options['use_nofollow'] = isset($_POST['buddylove-use_nofollow']);
			update_option('widget_buddylove', $options);
		}

		// Get options for form fields to show
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$number = htmlspecialchars($options['show'], ENT_QUOTES);
		$use_nofollow = $options['use_nofollow'] ? 'checked="checked"' : '';
		// The form fields
		echo '<p style="text-align:right;">
				<label for="buddylove-title">' . __('Title:') . ' 
				<input style="width: 200px;" id="buddylove-title" name="buddylove-title" type="text" value="'.$title.'" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="buddylove-show">' . __('Number of Posts to Show:') . ' 
				<input style="width: 25px;" id="buddylove-show" name="buddylove-show" type="text" value="'.$number.'" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="buddylove-use_nofollow">' . __('Set Links To Nofollow?:') . ' 
				<input class="checkbox" type="checkbox" '.$use_nofollow.' id="buddylove-use_nofollow" name="buddylove-use_nofollow" />
				</label></p>';
		
		echo '<input type="hidden" id="buddylove-submit" name="buddylove-submit" value="1" />';
	}
	
	// Register widget for use
	register_sidebar_widget(array('Buddy Love', 'widgets'), 'widget_buddylove');

	// Register settings for use, 300x500 pixel form
	register_widget_control(array('Buddy Love', 'widgets'), 'widget_buddylove_control', 250, 200);
}

// Run code and init
add_action('widgets_init', 'widget_buddylove_init');

?>