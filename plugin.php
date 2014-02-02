<?php
/**
 * Plugin Name: YouTube Playlist Shortcode
 * Plugin URI: http://github.com/chrismccoy
 * Description: Use this plugin to show a youtube playlist
 * Version: 1.0
 * Author: Chris McCoy
 * Author URI: http://github.com/chrismccoy

 * @copyright 2014
 * @author Chris McCoy
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package YouTube_Playlist_Shortcode
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Initiate Youtube Playlist Class on plugins_loaded
 *
 * @since 1.0
 */

if ( !function_exists( 'youtube_playlist_shortcode' ) ) {

	function youtube_playlist_shortcode() {
		$youtube_playlist_shortcode = new YouTube_Playlist_Shortcode();
	}

	add_action( 'plugins_loaded', 'youtube_playlist_shortcode' );
}

/**
 * helper tab function
 *
 * @since 1.0
 */

function tabify($amount = 1) {
	return str_repeat("\t", $amount);
}

/**
 * Youtube Playlist Class for scripts, styles, oembed removal and shortcode
 *
 * @since 1.0
 */

if( !class_exists( 'YouTube_Playlist_Shortcode' ) ) {

	class YouTube_Playlist_Shortcode {

		/**
 		* Hook into hooks for Register styles, scripts, and shortcode 
 		*
 		* @since 1.0
 		*/

		function __construct() {
            		add_filter( 'oembed_providers', array( $this, 'remove_youtube_oembed' ) , 10, 1);
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );
			add_shortcode( 'youtubeplaylist', array( $this, 'shortcode' ) );
		}

		/**
		 * remove automatic embedded of youtube videos
		 *
		 * @since 1.0
		 */

        	function remove_youtube_oembed( $providers ) {
            		unset($providers['#https?://(www\.)?youtube\.com/watch.*#i']);
            		return $providers;
        	}

		/**
		 * enqueue youtube playlist and ie respond javascript
		 *
		 * @since 1.0
		 */

		function scripts() {
			wp_enqueue_script( 'youtube_playlist', plugins_url( 'js/jquery.youtube.js', __FILE__ ), array( 'jquery' ), '1.0', false );
			wp_enqueue_script( 'respond', 'https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js');
		}

		/**
		 * enqueue youtube playlist styles
		 *
		 * @since 1.0
		 */

		function styles() {
			wp_enqueue_style( 'youtube_playlist', plugins_url( 'css/youtubePlaylist.css', __FILE__ ), false, '1.0', 'screen' );
		}

		/**
		 * shortcode function
		 *
		 * @since 1.0
		 * @use [youtubeplaylist]
		 */

		function shortcode( $attr, $content ) {
			global $post;

			extract( shortcode_atts( array(
				'username' => '', // youtube username
				'playlist' => '', // youtube playlist id
				'autoplay' => '', // autoplay videos (default is false)
				'fullscreen' => '', // allow full screen (default is true)
				'theme' => '', // 'light' = standard theme, 'dark' = dark theme (dark is default)
				'color' => '', // 'red' = red progress bar, 'white' = white progress bar. default is white
				'related' => '', // show related videos (true is default)
				'showinfo' => '', // show title and info (true is default)
				'autohide' => '', // title = autohude title, everything - auto hide everything, all = show all (title is default)
				'quality' => '', // 'small' = 240p, 'medium' = 360p, 'large' = 480p, 'hd720' = 720p, 'hd1080' = 1080p (medium is default)

			), $attr ) );

			$output = '';

            		$content = trim(strip_tags($content));
            		$content_lines = explode("\n", $content);

			$pid = $post->ID;
			$playlist_id = 'rp_playlist';
			$video_id = 'rp_video';

			$jquery_code = '<script type="text/javascript">' . PHP_EOL;
			$jquery_code .= tabify(4) . 'jQuery(function($) {' . PHP_EOL;
			$jquery_code .= tabify(5) . '$("ul#'. $playlist_id.'.post-'. $post->ID.'").responsiveplaylist({' . PHP_EOL;

			if(!empty($autoplay) && $autoplay == 'true') $jquery_code .= tabify(6) . 'autoPlay: true,' . PHP_EOL;
			if(!empty($fullscreen) && $fullscreen == 'false') $jquery_code .= tabify(6) . 'allowFullScreen: false,' . PHP_EOL;
			if(!empty($autohide) || !empty($related) || !empty($theme) || !empty($color) || !empty($quality) || !empty($showinfo) ) $jquery_code .= tabify(6) . 'youtube: {' . PHP_EOL;
       			if(!empty($autohide) && $autohide == "hide") $jquery_code .= tabify(7) . 'autohide: "1",' . PHP_EOL;
      			if(!empty($autohide) && $autohide == "showall") $jquery_code .= tabify(7) . 'autohide: "0",' . PHP_EOL;
       			if(!empty($related) && $related == 'false') $jquery_code .= tabify(7) . 'rel: "0",' . PHP_EOL;
       			if(!empty($theme) && $theme == 'light') $jquery_code .= tabify(7) . 'theme: "'. $theme. '",' . PHP_EOL;
       			if(!empty($color) && $color == 'red') $jquery_code .= tabify(7) . 'color: "'. $color .'",' . PHP_EOL;
       			if(!empty($showinfo) && $showinfo =='false') $jquery_code .= tabify(7) . 'showinfo: "0",' . PHP_EOL;
       			if(!empty($quality)) $jquery_code .= tabify(7) . 'vq: "'. $quality.'",' . PHP_EOL;
			if(!empty($autohide) || !empty($related) || !empty($theme) || !empty($color) || !empty($quality) || !empty($showinfo) ) $jquery_code .= tabify(6) . '},' . PHP_EOL;
			if(!empty($username)) $jquery_code .= tabify(6) . 'youtubeUsername: "'. $username .'",' . PHP_EOL;
			if(!empty($playlist)) $jquery_code .= tabify(6) . 'youtubePlaylist: "'. $playlist .'",' . PHP_EOL;

       			$jquery_code .= tabify(6) . 'holderId: "'. $video_id.'.post-'.$post->ID.'",' . PHP_EOL . tabify(5) . '});' . PHP_EOL . tabify(4) . '});' . PHP_EOL . tabify(3) . '</script>' . PHP_EOL . PHP_EOL;

			$output = tabify(3) . '<div id="rp_plugin">' . PHP_EOL;
			$output .= tabify(4) . '<div id="rp_videoContainer">' . PHP_EOL;
        		$output .= tabify(5) . '<div id="'. $video_id . '" class="post-'. $post->ID .'">' . PHP_EOL;
        		$output .= tabify(5) . '</div>' . PHP_EOL;
    			$output .= tabify(4) . '</div>' . PHP_EOL;
    			$output .= tabify(4) . '<div id="rp_playlistContainer">' . PHP_EOL;
        		$output .= tabify(5) . '<ul id="'. $playlist_id . '" class="post-'. $post->ID .'">' . PHP_EOL;

			if(!empty($content)) {
        			foreach($content_lines as $link) {
               				$output .= tabify(6) . '<li><a href="' . $link . '"></a></li>' . PHP_EOL;
        			}
			}

			$output .= tabify(5) . '</ul>' . PHP_EOL . tabify(4) . '</div>' . PHP_EOL . tabify(3) . '</div>';
			return $jquery_code . $output;
		}

	}

}
