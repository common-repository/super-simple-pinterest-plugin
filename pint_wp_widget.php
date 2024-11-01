<?php
/*
Plugin Name: Super-simple Pinterest Widget
Plugin URI: http://pinterest.com/
Description: This is a lightweight Wordpress plugin to display your recent Pinterest pins.
Version: 0.1

Installation:
1. Copy pinterest_widget.php to your plugins folder, /wp-content/plugins/
2. Activate it through the plugin management screen.
3. Go to Themes->Sidebar Widgets and drag and drop the widget to wherever you want to show it.

This plugin is heavily-inspired by the Flickr Widget by Donncha O Caoimh
http://donncha.wordpress.com/flickr-widget/

*/

function widget_pinterest($args) {
	if( file_exists( ABSPATH . WPINC . '/rss.php') ) {
		require_once(ABSPATH . WPINC . '/rss.php');
	} else {
		require_once(ABSPATH . WPINC . '/rss-functions.php');
	}
	extract($args);

	$options = get_option('widget_pinterest');
	if( $options == false ) {
		$options[ 'title' ] = 'Pinterest Pins';
		$options[ 'items' ] = 3;
	}
	$title = empty($options['title']) ? __('My Pinterest Pins') : $options['title'];
	$items = $options[ 'items' ];
	$pinterest_rss_url = empty($options['pinterest_rss_url']) ? __('http://pinterest.com/paulsciarra/feed.rss') : $options['pinterest_rss_url'];
	if ( empty($items) || $items < 1 || $items > 10 ) $items = 3;
	
	$rss = fetch_rss( $pinterest_rss_url );
	if( is_array( $rss->items ) ) {
		$out = '';
		$items = array_slice( $rss->items, 0, $items );
		while( list( $key, $pin ) = each( $items ) ) {
			preg_match_all("/<IMG.+?SRC=[\"']([^\"']+)/si",$pin[ 'description' ],$sub,PREG_SET_ORDER);
			$pin_url = str_replace( "_m.jpg", "_t.jpg", $sub[0][1] );
			$out .= "<a href='{$pin['link']}'><img alt='".wp_specialchars( $pin[ 'title' ], true )."' title='".wp_specialchars( $pin[ 'title' ], true )."' src='$pin_url' border='0'></a><br /><br />";
		}
		$pinterest_home = $rss->channel[ 'link' ];
		$pinterest_more_title = $rss->channel[ 'title' ];
	}
	?>
	<?php echo $before_widget; ?>
	<?php echo $before_title . $title . $after_title; ?>

<!-- Start of Pinterest Badge -->
<style type="text/css">
#pint_badge_source_txt {padding:0; font: 11px Arial, Helvetica, Sans serif; color:#666666;}
#pint_badge_icon {display:block !important; margin:0 !important; border: 1px solid rgb(0, 0, 0) !important;}
#pint_icon_td {padding:0 5px 0 0 !important;}
.pint_badge_image {text-align:center !important;}
.pint_badge_image img {border: 1px solid black !important;}
#pint_badge_uber_wrapper {width:150px;}
#pint_badge_uber_wrapper a img {width:100%;}
#pint_www {display:block; text-align:center; padding:0 0px 0 0px !important; font: 11px Arial, Helvetica, Sans serif !important; color:#3993ff !important;}
#pint_badge_uber_wrapper a:hover,
#pint_badge_uber_wrapper a:link,
#pint_badge_uber_wrapper a:active,
#pint_badge_uber_wrapper a:visited {text-decoration:none !important; background:inherit !important;color:#3993ff;}
#pint_badge_wrapper {background-color:#ffffff;border: solid 1px #000000}
#pint_badge_source {padding:0 !important; font: 11px Arial, Helvetica, Sans serif !important; color:#666666 !important;}
</style>
<table id="pint_badge_uber_wrapper" cellpadding="0" cellspacing="10" border="0"><tr><td><table cellpadding="0" cellspacing="10" border="0" id="pint_badge_wrapper">
<tr><td align='center'>
<?php echo $out ?>
<a href="<?php echo strip_tags( $pinterest_home ) ?>">More Pins</a>
</td></tr>
</table>
</td></tr></table>

<!-- End of Pinterest Badge -->

		<?php echo $after_widget; ?>
<?php
}

function widget_pinterest_control() {
	$options = $newoptions = get_option('widget_pinterest');
	if( $options == false ) {
		$newoptions[ 'title' ] = 'Pinterest Pins';
	}
	if ( $_POST["pinterest-submit"] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["pinterest-title"]));
		$newoptions['items'] = strip_tags(stripslashes($_POST["rss-items"]));
		$newoptions['pinterest_rss_url'] = strip_tags(stripslashes($_POST["pinterest-rss-url"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_pinterest', $options);
	}
	$title = wp_specialchars($options['title']);
	$items = wp_specialchars($options['items']);
	if ( empty($items) || $items < 1 ) $items = 3;
	$pinterest_rss_url = wp_specialchars($options['pinterest_rss_url']);

	?>
	<p><label for="pinterest-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="pinterest-title" name="pinterest-title" type="text" value="<?php echo $title; ?>" /></label></p>
	<p><label for="pinterest-rss-url"><?php _e('Pinterest RSS URL:'); ?> <input style="width: 250px;" id="pinterest-title" name="pinterest-rss-url" type="text" value="<?php echo $pinterest_rss_url; ?>" /></label></p>
	<p style="text-align:center; line-height: 30px;"><?php _e('How many pins would you like to display?'); ?> <select id="rss-items" name="rss-items">
	<?php for ( $i = 1; $i <= 10; ++$i ) echo "<option value='$i' ".($items==$i ? "selected='selected'" : '').">$i</option>"; ?>
	</select></p>
	<p align='left'>* Your RSS feed can be found on your Pinterest profile.<br/><br clear='all'></p>
	<p>Leave the Pinterest RSS URL blank to display <a href="http://pinterest.com/paulsciarra/">Paul Sciarra's</a> Pinterest pins.</p>
	<input type="hidden" id="pinterest-submit" name="pinterest-submit" value="1" />
	<?php
}


function pinterest_widgets_init() {
	register_widget_control('Pinterest', 'widget_pinterest_control', 500, 250);
	register_sidebar_widget('Pinterest', 'widget_pinterest');
}
add_action( "init", "pinterest_widgets_init" );

?>