<?php
/*
Plugin Name: Lightweight YouTube Channel Widget
Plugin URI: https://github.com/MaTachi/lightweight-youtube-channel-widget
Description: <a href="widgets.php">Widget</a> that displays video thumbnails
from a YouTube channel or playlist.
Author: Daniel Jonsson
Version: 10.0
Author URI: https://github.com/MaTachi/lightweight-youtube-channel-widget
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'LYCW' ) ):
/**
 * Lightweight YouTube Channel Widget class.
 *
 * Contains all logic relevant to the plugin.
 */
class LYCW {

	public $plugin_slug          = 'lightweight-youtube-channel-widget';
	private $plugin_version      = '10.0';
	private $default_channel_id  = 'misesmedia';
	private $default_playlist_id = 'PLALopHfWkFlFTj__lkebZfUw5s-CWVuIt';

	function __construct() {
		// Load plugin translations.
		load_plugin_textdomain(
			$this->plugin_slug,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		// Load widget definition.
		require_once( 'inc/widget.php' );

		add_action(
			'wp_enqueue_scripts',
			array( $this, 'enqueue_scripts' )
		);
	}

	/**
	 * Load scripts.
	 */
	function enqueue_scripts() {
		wp_enqueue_style(
			$this->plugin_slug,
			plugins_url( 'assets/css/youtube-channel.min.css', __FILE__ ),
			array(),
			$this->plugin_version
		);
	}

	/**
	 * Print list of videos.
	 *
	 * @param array $instance A setup of variables for this widget.
	 * @return array An array of strings making up the HTML to display the
	 *     video list.
	 */
	public function output( $instance ) {
		// Get channel name.
		if ( '' != $instance['channel'] ) {
			$channel = $instance['channel'];
		} else {
			$channel = $this->default_channel_id;
		}

		// Get playlist ID.
		if ( '' != $instance['playlist'] ) {
			$playlist = $instance['playlist'];
		} else {
			$playlist = $this->default_playlist_id;
		}

		// The type of resource to display.
		$type_of_resource = $instance['type_of_resource'];

		if ( !empty( $instance['responsive'] ) ) {
			$responsive = ' responsive';
		} else {
			$responsive = '';
		}

		$fetch_videos = $instance['fetch_videos'];
		$show_videos = $instance['show_videos'];
		if ( !isset( $fetch_videos ) ) {
			$fetch_videos = 5;
		}

		$randomize_videos = $instance['randomize_videos'];

		$feed_url = $this->get_feed_url(
			$instance['type_of_resource'], $channel, $playlist,
			$fetch_videos, $instance['fix_no_items']
		);

		$output = array();

		$output[] = '<div class="youtube_channel' . $responsive . '">';

		// Do we need cache?
		if ( $instance['cache_time'] > 0 ) {
			// Generate feed cache key for caching time
			$cache_key = 'lycw_' . md5( $feed_url ) . '_' .
				$instance['cache_time'];

			$json = get_transient( $cache_key );

			if ( false === $json ) {
				// No cached JSON, get new.
				$wprga = array(
					'timeout' => 2 // two seconds only
				);
				$response = wp_remote_get( $feed_url, $wprga );
				$json = wp_remote_retrieve_body( $response );

				// set decoded JSON to transient cache_key
				set_transient(
					$cache_key,
					base64_encode( $json ),
					$instance['cache_time']
				);
			} else {
				// we already have cached feed JSON, get it encoded
				$json = base64_decode( $json );
			}
		} else {
			// just get fresh feed if cache disabled
			$wprga = array(
				'timeout' => 2 // two seconds only
			);
			$response = wp_remote_get( $feed_url, $wprga );
			$json = wp_remote_retrieve_body( $response );
		}

		// Decode JSON data.
		$json_output = json_decode( $json );

		$error_found = false;
		$entries_found = false;

		if (
			!is_wp_error( $json_output ) &&
			is_object( $json_output ) &&
			!empty( $json_output->feed->entry )
		) {
			// Sorted by date uploaded.
			$videos = $json_output->feed->entry;

			if ( sizeof( $videos ) <= 0 ) {
				$entries_found = false;
			} else {
				$entries_found = true;
				$fetch_videos = sizeof( $videos );
			}
		} else {
			$error_found = true;
		}

		if ( $error_found or !$entries_found ) {
			$output[] = __( 'No items', $this->plugin_slug ) .
				' [<a href="' .  $feed_url .  '" target="_blank">' .
				__( 'Check here why' , $this->plugin_slug ) . '</a>]';
			return $output;
		}

		if ( $randomize_videos ) {
			shuffle( $videos );
		}

		// Only show at max `show_videos` number of videos.
		$videos = array_slice( $videos, 0, $show_videos );

		// http://stackoverflow.com/a/3561009/595990
		foreach ( array_values( $videos ) as $i => $video ) {
			// Render a single video block.
			$output = array_merge(
				$output,
				$this->render_video_block( $video, $instance, $i + 1 )
			);
		}

		$output[] = '</div><!-- .youtube_channel -->';

		return $output;
	}

	/**
	 * Generate a feed URL that will fetch the desired videos from YouTube's
	 * JSON API.
	 *
	 * @param string $type_of_resource `channel`, `favorites` or 'playlist'.
	 * @param string $channel Name of the channel.
	 * @param string $playlist Name of the playlist.
	 * @param integer $fetch_videos The number of videos to fetch.
	 * @param bool $fix_no_items Try to fix it if the user experiences the
	 *     "no items found" error.
	 * @return string A YouTube API URL.
	 */
	private function get_feed_url(
		$type_of_resource, $channel, $playlist, $fetch_videos, $fix_no_items
	) {
		$feed_attr = '?alt=json';

		// Select fields.
		$feed_attr .= '&fields=entry(published,title,link,content)';

		if ( !$fix_no_items && 'favorites' != $type_of_resource ) {
			$feed_attr .= '&orderby=published';
		}

		$feed_attr .= '&max-results=' . $fetch_videos;

		$feed_attr .= '&rel=0';

		switch ( $type_of_resource ) {
			case 'channel':
			default:
				$feed_url = 'http://gdata.youtube.com/feeds/api/users/' .
					$channel . '/uploads';
				break;
			case 'favorites':
				$feed_url = 'http://gdata.youtube.com/feeds/api/users/' .
					$channel . '/favorites';
				break;
			case 'playlist':
				$playlist = $this->extract_playlist_id( $playlist );
				$feed_url = 'http://gdata.youtube.com/feeds/api/playlists/' .
					$playlist;
		}

		return $feed_url . $feed_attr;
	}

	/**
	 * Calculate the height when there's a given width and width/height ratio.
	 *
	 * @param integer $width The width of the thumbnail.
	 * @param string $ratio Which ratio it will use between `ar4_3`, `ar16_10`,
	 *   and `ar16_9`.
	 * $return integer The height of the thumbnail.
	 */
	private function height_ratio( $width = 306, $ratio ) {
		switch ( $ratio ) {
			case 'ar4_3':
				return round( ( $width / 4 ) * 3 );
			case 'ar16_10':
				return round( ( $width / 16 ) * 10 );
			case 'ar16_9':
			default:
				return round( ( $width / 16 ) * 9 );
		}
	}

	/**
	 * Render a single video block.
	 *
	 * @param object $video A single video from YouTube's JSON API as a PHP
	 *     object.
	 * @param array $instance A setup of variables for this widget.
	 * @param integer $i The placement of the video block, starting from 1.
	 * @return array An array of strings making up the HTML to display the
	 *     video block.
	 */
	private function render_video_block( $video, $instance, $i) {

		// Set width and height
		$width  = ( empty($instance['width']) ) ? 306 : $instance['width'];
		$height = $this->height_ratio( $width, $instance['ratio'] );

		$yt_id    = $video->link[0]->href;
		$yt_id    = preg_replace( '/^.*=(.*)&.*$/', '${1}', $yt_id );
		$yt_url   = "v/$yt_id";

		$yt_thumb = "//img.youtube.com/vi/$yt_id/0.jpg"; // zero for HD thumb
		$yt_video = $video->link[0]->href;
		$yt_video = preg_replace('/\&.*$/','',$yt_video);

		$yt_title = $video->title->{'$t'};
		$yt_date  = $video->published->{'$t'};

		switch ( $i ) {
			case 1:
				$vnumclass = 'first';
				break;
			case $instance['show_videos']:
				$vnumclass = 'last';
				break;
			default:
				$vnumclass = 'mid';
		}

		$output[] = sprintf(
			'<div class="ytc_video_container ytc_video_%d ytc_video_%s" style="width: %dpx">',
			$i, $vnumclass, $width
		);

		// Show video title?
		if ( $instance['show_title'] ) {
			$output[] = sprintf( '<h3 class="ytc_title">%s</h3>', $yt_title );
		}

		// Define object ID.
		$ytc_vid = 'ytc_' . $yt_id;

		// Set proper aspect ratio class.
		$arclass = $instance['ratio'];

		$title = sprintf(
			__('Watch video %1$s published on %2$s', $this->plugin_slug ),
			$yt_title, $yt_date
		);
		$output[] = sprintf(
			'<a href="%s" title="%s" class="ytc_thumb ytc-lightbox %s">' .
				'<span style="background-image: url(%s);" title="%s" id="%s">' .
				'</span></a>',
			$yt_video, $yt_title, $arclass, $yt_thumb, $title, $ytc_vid
		);

		// Do we need to show video description?
		if ( $instance['show_desc'] ) {
			$description = $video->content->{'$t'};

			// Remove HTML tags
			$description = strip_tags( $description );

			if (
				$instance['desc_length'] > 0 and
				strlen( $description ) > $instance['desc_length']
			) {
				$description = substr(
					$description, 0, $instance['desc_length']
				);
				if ( $instance['desc_append'] ) {
					$etcetera = $instance['desc_append'];
				} else {
					$etcetera = '&hellip;';
				}
			} else {
				$etcetera = '';
			}

			if ( !empty( $description ) ) {
				$output[] = sprintf(
					'<p class="ytc_description">%s%s</p>',
					$description, $etcetera
				);
			}
		}

		$output[] = '</div><!-- .ytc_video_container -->';

		return $output;
	}

	/**
	 * Extract the playlist ID if the provided string is a URL to a YouTube 
	 * playlist.
	 *
	 * @param string $playlist A playlist ID or a URL to a playlist.
	 * @return string A playlist ID.
	 */
	private function extract_playlist_id( $playlist ) {
		if ( substr( $playlist, 0, 4 ) == 'http' ) {
			// If URL provided, extract playlist ID.
			$playlist = preg_replace(
				'/.*list=(PL[A-Za-z0-9\-\_]*).*/', '$1', $playlist
			);
		}
		return $playlist;
	}

}
endif;

global $LYCW;
$LYCW = new LYCW();
