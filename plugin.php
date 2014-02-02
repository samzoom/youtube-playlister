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

			// get youtube links 1 per line if entered

            		$content = trim(strip_tags($content));
            		$content_lines = explode("\n", $content);

			// playlist and video container ids

			$playlist_id = 'rp_playlist';
			$video_id = 'rp_video';

			// unique div for each playlist

       			$holder = 'holderId: "'. $video_id.'.post-'.$post->ID.'",';

			// jquery values for each shortcode attrib

			$autoplay = empty($autoplay) ? $autoplay : 'autoPlay: true,';
			$fullscreen = empty($fullscreen) ? $fullscreen : 'allowFullScreen: false,';
			$autohide = ($autohide == 'showall' ? 'autohide: "0",' : ($autohide == 'hide' ? 'autohide: "1",' : 'autohide: "2",'));
			$related = empty($related) ? $related : 'rel: "0",';
			$theme = empty($theme) ? $theme : 'theme: "'. $theme. '",';
			$color = empty($color) ? $color : 'color: "'. $color .'",';
			$showinfo = empty($showinfo) ? $showinfo : 'showinfo: "0",';
			$quality = empty($quality) ? $quality : 'vq: "'. $quality.'",';
			$username = empty($username) ? $username : 'youtubeUsername: "'. $username .'",';
			$playlist = empty($playlist) ? $playlist : 'youtubePlaylist: "'. $playlist .'",';

			// jquery code with attributes

			$jquery_code = '<script type="text/javascript">jQuery(function($) { $("ul#'. $playlist_id.'.post-'. $post->ID.'").responsiveplaylist({ '.$autoplay . $fullscreen.' youtube: { '.$autohide . $related . $theme . $color . $showinfo . $quality .'}, '.$holder .  $username . $playlist.' }); }); </script>' . PHP_EOL . PHP_EOL;

			// html container for playlist

			$html = '<div id="rp_plugin"><div id="rp_videoContainer"><div id="'. $video_id . '" class="post-'. $post->ID .'"></div></div><div id="rp_playlistContainer"><ul id="'. $playlist_id . '" class="post-'. $post->ID .'">';

			// add youtube links to playlist if entered

			if(!empty($content)) {
        			foreach($content_lines as $link) {
               				$html .= '<li><a href="' . $link . '"></a></li>';
        			}
			}

			// closing html for playlist

			$html .= '</ul></div></div>';

			// return jquery + html code

			return $jquery_code . $html;
		}

	}

}


