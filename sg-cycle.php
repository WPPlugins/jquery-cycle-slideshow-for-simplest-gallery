<?php
/*
Plugin Name: jQuery Cycle Slideshow for Simplest Gallery
Version: 1.5
Plugin URI: http://www.simplestgallery.com/add-ons/jquery-cycle-slideshow-gallery-style-plugin/
Description: Display your Wordpress galleries as a jQuery Slideshow. Requires the "Simplest Gallery" plugin (adds a new gallery style to it).
Author: Cristiano Leoni, JJ Coder
Author URI: http://www.linkedin.com/pub/cristiano-leoni/2/b53/34

# This file is UTF-8 - These are accented Italian letters àèìòù

*/

/*

    History
   + 1.5 2017-06-09	Added support for width,height and border attributes in shortcode
   + 1.4 2013-09-18	Fix on jQuery library version (gallery did not work on some themes)
   + 1.3 2013-09-12	Fixed rare bug in startup. Support for multiple galleries in the same page (with Simplest Gallery version 2.5 or higher)
   + 1.2 2013-09-01	Bug fix
   + 1.1 2013-09-01	Bug fixes for compatibility issues with WP 3.6
   + 1.0 2013-04-29	First working version
*/


add_action('init', 'sgac_init');

// Init tasks: adds a new gallery format to the Simplest Gallery plugin via an API call
function sgac_init() {
	$urlpath = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));

	// If Simplest Gallery Plugin is not installed & activated display a reminder line
	if (!function_exists('sga_register_gallery_type') && !($_REQUEST['plugin']=='simplest-gallery/simplest-gallery.php' && $_REQUEST['action']=='activate')) {
		echo "Please install and activate Simplest Gallery plugin!";
		return;
	} else {
		if (is_callable('sga_register_gallery_type')) {
   
		// Adds new gallery type to the Simplest Gallery Plugin
		$result = sga_register_gallery_type(
							'cycle', 		/* this is the gallery type's unique ID */
							'jQuery Cycle Slideshow', /* this is the gallery type name (what the user will see in the settings page) */
							'sgac_render',		/* Function to be called for the gallery rendering */
							'sgac_header',		/* Function to be called on header() */
							array(			/* Array of scripts to be included, possibly empty */
								'jquery'=>array('http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js', false, '1.8.3'),
								'jquery-jjcycle'=>array($urlpath . '/script/jquery.cycle.lite.1.0.min.js', array('jquery'), ''),
								// 'jquery-shuffle'=>array($urlpath . '/script/jquery.jj_ngg_shuffle.js', array('jquery'), ''), // Will be activated later
							      ),
							array()			/* Array of CSS to be included, possibly empty */
						);
		}
	}

}

// Sample header custom function. If we need to make special things in the header of pages for our gallery format, we will do so here
function sgac_header() {
	return "<!-- jQuery Cycle Slideshow module for Simplest Gallery -->\n";
}

// This is a the gallery-rendering function. We don't need to care about gathering images because the Simplest Gallery plugin does this for us.
// First parameter is an array of images data (images of the gallery to be rendered), second parameter is an array of thumbs data (unused here)
// data here means that each image/thumb is represented by an array. Each position holds a specific thing:
// 0=URL,1=width,2=height,3=unused,4=ID,5=Label
function sgac_render($images,$thumbs,$post_id=NULL,$gall_id=NULL,$attrs,$gallery_type='cycle') {
    $p_size=600;
	if (is_array($attrs)) { // Are parameters specified in the shortcode?

		if (isset($attrs['border'])) { 
			$border = $attrs['border'];
		}

		if (isset($attrs['width'])) {
			$width = $attrs['width'];
		}

		if (isset($attrs['height'])) {
			$height = $attrs['height'];
		}
	}    
    if ($post_id) {
	    if (!$width) { // Deprecated
	    	$width=get_post_meta($post_id, 'gall_width', true);
	    	if ($width) $width.='px';
	    }
	    if (!$height) { // Deprecated
	    	$height=get_post_meta($post_id, 'gall_height', true);
	    	if ($height) $height.='px';
	    }
    }
    if (!$width) { // Fallback
	$width='600px';
    }
    if (!$height) { // Fallback
	$height='600px';
    }
    
    $html_id='cycle_lite_'.$gall_id;
    
    $output = '';
    if($p_size > 0) {
	$style_outer .= "width:$width;height:$height;overflow:hidden;";
	$style_inner .= "width:$width;height:$height;overflow:hidden;";
      
	$style_outer .= 'margin-left:auto;margin-right:auto;';
      
	if($border) $style_outer .= "border:$border;";
      
	$style_outer_tag = " style=\"" . $style_outer . "\"";
	$style_inner_tag = " style=\"" . $style_inner . "\"";

      $output .= "\n<div id=\"" . $html_id . "_container\" class=\"cycle_lite_container\"" . $style_outer_tag . " >";
      $output .= "\n  <div id=\"" . $html_id . "\"" . $style_inner_tag . ">";
      $image_alt = null;
      $image_description = null;
      foreach($images as $image) {
        $image_alt = 'image';
        $image_description = $image[5];

        if($use_url != '')
        {
          $output .= "<a href=\"" . esc_attr($image_alt) . "\">";
        }

        if($image_description != '')
        {
          $image_description = "alt=\"" . esc_attr($image_description) . "\" title=\"" . esc_attr($image_description) . "\" ";
        }
        else
        {
          $image_description = '';
        }

        $output .= "<img src=\"" . $image[0] . "\" " . $image_description . " style=\"border:none;width:$width;position:relative;top:0px;left:0px;\" />";

        if($use_url != '')
        {
          $output .= "</a>";
        }
      }
      $output .= "\n  </div>";
      $output .= "\n</div>";
    }

    // Cycle Lite arguments
    $javascript_args = array();

    if($timeout != "") { $javascript_args[] = "timeout: " . $timeout; }
    if($speed != "") { $javascript_args[] = "speed: " . $speed; }
    //if($height != "") { $javascript_args[] = "height: " . $height; }
    if($sync != "") { $javascript_args[] = "sync: " . $sync; }
    if($fit != "") { $javascript_args[] = "fit: " . $fit; }
    if($pause != "") { $javascript_args[] = "pause: " . $pause; }
    if($delay != "") { $javascript_args[] = "delay: " . $delay; }

    // Add javascript
    $output .= "\n<script type=\"text/javascript\">";
    // Shuffle results on random order so even if page is cached the order will be different each time
    if(FALSE && $order == 'random' && $shuffle == 'true')
    {
      $output .= "\n  jQuery('#" . $html_id . "').jj_ngg_shuffle();";
    }
    $output .= "\n  jQuery('#" . $html_id . "').jjcycle(";
    if(count($javascript_args) > 0)
    {
      $output .= "{" . implode(",", $javascript_args) . "}";
    }
    $output .= ");";
    $output .= "\n</script>\n";

	return $output;


}


?>