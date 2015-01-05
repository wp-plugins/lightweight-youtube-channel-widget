<?php
/**
 * The widget that WordPress will register.
 */
class LYCW_Widget extends WP_Widget {

	public function __construct() {
		global $LYCW;
		// Initialize Widget
		parent::__construct(
			$LYCW->plugin_slug,
			__( 'Lightweight Youtube Channel Widget',  $LYCW->plugin_slug ),
			array(
				'description' => __(
					'Show YouTube video thumbnails from a channel or playlist',
 					$LYCW->plugin_slug
				)
			)
		);
	}

	public function widget( $args, $instance ) {
		global $LYCW;
		// outputs the content of the widget
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );

		$output = array();
		$output[] = $before_widget;
		if ( $title ) {
			$output[] = $before_title . $title . $after_title;
		}
		$output[] = implode( $LYCW->output( $instance ) );
		$output[] = $after_widget;

		echo implode( '', array_values( $output ) );
	}

	public function form( $instance ) {
		global $LYCW;
		// outputs the options form for widget settings
		// General Options
		$title         = (!empty($instance['title'])) ? esc_attr($instance['title']) : '';
		$channel       = (!empty($instance['channel'])) ? esc_attr($instance['channel']) : '';
		$playlist      = (!empty($instance['playlist'])) ? esc_attr($instance['playlist']) : '';

		$type_of_resource = (!empty($instance['type_of_resource'])) ? esc_attr($instance['type_of_resource']) : 'channel'; // resource to use: channel, favorites, playlist

		$cache_time    = (!empty($instance['cache_time'])) ? esc_attr($instance['cache_time']) : 0;

		$fetch_videos  = (!empty($instance['fetch_videos'])) ? esc_attr($instance['fetch_videos']) : 5; // items to fetch
		$show_videos   = (!empty($instance['show_videos'])) ? esc_attr($instance['show_videos']) : 1; // number of items to show

		$fix_no_items  = (!empty($instance['fix_no_items'])) ? esc_attr($instance['fix_no_items']) : false;
		$randomize_videos = (!empty($instance['randomize_videos'])) ? esc_attr($instance['randomize_videos']) : false;

		// Thumbnail Settings
		$ratio         = (!empty($instance['ratio'])) ? esc_attr($instance['ratio']) : 'ar16_9'; // ar4_3, ar16_9, ar16_10
		$width         = (!empty($instance['width'])) ? esc_attr($instance['width']) : 306;
		$responsive    = (!empty($instance['responsive'])) ? esc_attr($instance['responsive']) : false;

		// Content Layout
		$show_title   = (!empty($instance['show_title'])) ? esc_attr($instance['show_title']) : false;
		$show_desc    = (!empty($instance['show_desc'])) ? esc_attr($instance['show_desc']) : false;
		$desc_length  = (!empty($instance['desc_length'])) ? esc_attr($instance['desc_length']) : 0;
		$desc_append  = (!empty($instance['desc_append'])) ? esc_attr($instance['desc_append']) : '&hellip;';
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title');	?>"><?php _e('Widget Title', $LYCW->plugin_slug); ?>:<input type="text" class="widefat" id="<?php echo $this->get_field_id('title');		?>" name="<?php echo $this->get_field_name('title');	?>" value="<?php echo $title;		?>" title="<?php _e('Title for widget', $LYCW->plugin_slug); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('channel');	?>"><?php _e('Channel ID', $LYCW->plugin_slug); ?>:<input type="text" class="widefat" id="<?php echo $this->get_field_id('channel');		?>" name="<?php echo $this->get_field_name('channel');	?>" value="<?php echo $channel;		?>" title="<?php _e('YouTube Channel name (not URL to channel)', $LYCW->plugin_slug); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('playlist');	?>"><?php _e('Playlist ID', $LYCW->plugin_slug); ?>:<input type="text" class="widefat" id="<?php echo $this->get_field_id('playlist');	?>" name="<?php echo $this->get_field_name('playlist'); ?>" value="<?php echo $playlist;	?>" title="<?php _e('YouTube Playlist ID (not playlist name)', $LYCW->plugin_slug); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('type_of_resource'); ?>"><?php _e('Resource to use', $LYCW->plugin_slug); ?>:</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'type_of_resource' ); ?>" name="<?php echo $this->get_field_name( 'type_of_resource' ); ?>">
				<option value="channel"<?php selected( $type_of_resource, 'channel' ); ?>><?php _e('Channel', $LYCW->plugin_slug); ?></option>
				<option value="favorites"<?php selected( $type_of_resource, 'favorites' ); ?>><?php _e('Favorites', $LYCW->plugin_slug); ?></option>
				<option value="playlist"<?php selected( $type_of_resource, 'playlist' ); ?>><?php _e('Playlist', $LYCW->plugin_slug); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cache_time');	?>"><?php _e('Cache feed', $LYCW->plugin_slug); ?>:</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'cache_time' ); ?>" name="<?php echo $this->get_field_name( 'cache_time' ); ?>">
				<option value="0"<?php selected( $cache_time, 0 ); ?>><?php _e('Do not cache', $LYCW->plugin_slug); ?></option>
				<?php echo $this->cache_time( $cache_time ); ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('fetch_videos'); ?>"><?php _e('Fetch', $LYCW->plugin_slug); ?>: <input class="small-text" id="<?php echo $this->get_field_id('fetch_videos'); ?>" name="<?php echo $this->get_field_name('fetch_videos'); ?>" type="number" min="2" value="<?php echo $fetch_videos; ?>" title="<?php _e('Number of videos that will be used for random pick (min 2, max 50, default 25)', $LYCW->plugin_slug); ?>" /> <?php _e('video(s)', $LYCW->plugin_slug); ?></label>
			<br />
			<label for="<?php echo $this->get_field_id('show_videos'); ?>"><?php _e('Show', $LYCW->plugin_slug); ?>:</label> <input class="small-text" id="<?php echo $this->get_field_id('show_videos'); ?>" name="<?php echo $this->get_field_name('show_videos'); ?>" type="number" min="1" value="<?php echo ( $show_videos ) ? $show_videos : '1'; ?>" title="<?php _e('Number of videos to display', $LYCW->plugin_slug); ?>" /> <?php _e('video(s)', $LYCW->plugin_slug); ?>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( (bool) $fix_no_items, true ); ?> id="<?php echo $this->get_field_id( 'fix_no_items' ); ?>" name="<?php echo $this->get_field_name( 'fix_no_items' ); ?>" title="<?php _e('Enable this option if you get error No Item', $LYCW->plugin_slug); ?>" /> <label for="<?php echo $this->get_field_id( 'fix_no_items' ); ?>"><?php _e('Fix <em>No items</em> error/Respect playlist order', $LYCW->plugin_slug); ?></label>
			<br />
			<input class="checkbox" type="checkbox" <?php checked( (bool) $randomize_videos, true ); ?> id="<?php echo $this->get_field_id( 'randomize_videos' ); ?>" name="<?php echo $this->get_field_name( 'randomize_videos' ); ?>" title="<?php _e('Get random videos of all fetched from channel or playlist', $LYCW->plugin_slug); ?>" /> <label for="<?php echo $this->get_field_id( 'randomize_videos' ); ?>"><?php _e('Show random video', $LYCW->plugin_slug); ?></label>
		</p>
		
		<h4><?php _e('Thumbnail Settings', $LYCW->plugin_slug); ?></h4>
		<p><label for="<?php echo $this->get_field_id('ratio'); ?>"><?php _e('Aspect ratio', $LYCW->plugin_slug); ?>:</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'ratio' ); ?>" name="<?php echo $this->get_field_name( 'ratio' ); ?>">
				<option value="ar16_9"<?php selected( $ratio, 3 ); ?>>16:9</option>
				<option value="ar16_10"<?php selected( $ratio, 2 ); ?>>16:10</option>
				<option value="ar4_3"<?php selected( $ratio, 1 ); ?>>4:3</option>
			</select><br />
			<input class="checkbox" type="checkbox" <?php checked( (bool) $responsive, true ); ?> id="<?php echo $this->get_field_id( 'responsive' ); ?>" name="<?php echo $this->get_field_name( 'responsive' ); ?>" /> <label for="<?php echo $this->get_field_id( 'responsive' ); ?>"><?php _e('Responsive thumbnail (distribute one full width video per row)', $LYCW->plugin_slug); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width', $LYCW->plugin_slug); ?>:</label> <input class="small-text" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="number" min="32" value="<?php echo $width; ?>" title="<?php _e('Set video thumbnail in pixels', $LYCW->plugin_slug); ?>" /> px (<?php _e('default', $LYCW->plugin_slug); ?> 306)
		</p>

		<h4><?php _e('Content Layout', $LYCW->plugin_slug); ?></h4>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( (bool) $show_title, true ); ?> id="<?php echo $this->get_field_id( 'show_title' ); ?>" name="<?php echo $this->get_field_name( 'show_title' ); ?>" /> <label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php _e('Show video title', $LYCW->plugin_slug); ?></label><br />
			<input class="checkbox" type="checkbox" <?php checked( (bool) $show_desc, true ); ?> id="<?php echo $this->get_field_id( 'show_desc' ); ?>" name="<?php echo $this->get_field_name( 'show_desc' ); ?>" /> <label for="<?php echo $this->get_field_id( 'show_desc' ); ?>"><?php _e('Show video description', $LYCW->plugin_slug); ?></label><br />
			<label for="<?php echo $this->get_field_id('desc_length'); ?>"><?php _e('Description length', $LYCW->plugin_slug); ?>: <input class="small-text" id="<?php echo $this->get_field_id('desc_length'); ?>" name="<?php echo $this->get_field_name('desc_length'); ?>" type="number" value="<?php echo $desc_length; ?>" title="<?php _e('Set number of characters to cut down video description to (0 means full length)', $LYCW->plugin_slug);?>" /> (0 = full)</label><br />
			<label for="<?php echo $this->get_field_id('desc_append'); ?>"><?php _e('Et cetera string', $LYCW->plugin_slug); ?> <input class="small-text" id="<?php echo $this->get_field_id('desc_append'); ?>" name="<?php echo $this->get_field_name('desc_append'); ?>" type="text" value="<?php echo $desc_append; ?>" title="<?php _e('Default: &amp;hellip;', $LYCW->plugin_slug); ?>"/></label><br />
		</p>

<?php
	}

	public function update($new_instance, $old_instance) {
		// processes widget options to be saved
		$instance                  = $old_instance;
		$instance['title']         = strip_tags($new_instance['title']);
		$instance['channel']       = strip_tags($new_instance['channel']);
		$instance['show_videos']   = $new_instance['show_videos'];
		$instance['playlist']      = strip_tags($new_instance['playlist']);
		$instance['type_of_resource'] = $new_instance['type_of_resource'];
		$instance['cache_time']    = $new_instance['cache_time'];
		$instance['randomize_videos'] = (isset($new_instance['randomize_videos'])) ? $new_instance['randomize_videos'] : false;
		if (
			isset( $new_instance['fetch_videos'] ) &&
			is_numeric( $new_instance['fetch_videos'] )
		) {
			if ( $new_instance['fetch_videos'] > 50 ) {
				$instance['fetch_videos'] = 50;
			} else if ( $new_instance['fetch_videos'] < 1 ) {
				$instance['fetch_videos'] = 1;
			} else {
				$instance['fetch_videos'] = $new_instance['fetch_videos'];
			}
		} else {
			$instance['fetch_videos'] = 5;
		}

		$instance['show_title']   = (isset($new_instance['show_title'])) ? $new_instance['show_title'] : false;
		$instance['show_desc']    = (isset($new_instance['show_desc'])) ? $new_instance['show_desc'] : false;
		$instance['desc_append']  = strip_tags($new_instance['desc_append']);
		$instance['desc_length']  = strip_tags($new_instance['desc_length']);
		$instance['width']        = strip_tags($new_instance['width']);
		$instance['responsive']   = (isset($new_instance['responsive'])) ? $new_instance['responsive'] : false;

		$instance['fix_no_items'] = (isset($new_instance['fix_no_items'])) ? $new_instance['fix_no_items'] : false;
		$instance['ratio']        = strip_tags($new_instance['ratio']);

		return $instance;
	}

	/**
	 * Get string of cache time dropdown menu alternatives.
	 *
	 * @param int $cache_time The selected cache length in seconds.
	 * @return string HTML string with all selectable cache length options.
	 */
	private function cache_time( $cache_time ) {
		$times = array(
			'minute' => array(
				1  => __('1 minute', $LYCW->plugin_slug),
				5  => __('5 minutes', $LYCW->plugin_slug),
				15 => __('15 minutes', $LYCW->plugin_slug),
				30 => __('30 minutes', $LYCW->plugin_slug)
			),
			'hour' => array(
				1  => __('1 hour', $LYCW->plugin_slug),
				2  => __('2 hours', $LYCW->plugin_slug),
				5  => __('5 hours', $LYCW->plugin_slug),
				10 => __('10 hours', $LYCW->plugin_slug),
				12 => __('12 hours', $LYCW->plugin_slug),
				18 => __('18 hours', $LYCW->plugin_slug)
			),
			'day' => array(
				1 => __('1 day', $LYCW->plugin_slug),
				2 => __('2 days', $LYCW->plugin_slug),
				3 => __('3 days', $LYCW->plugin_slug),
				4 => __('4 days', $LYCW->plugin_slug),
				5 => __('5 days', $LYCW->plugin_slug),
				6 => __('6 days', $LYCW->plugin_slug)
			),
			'week' => array(
				1 => __('1 week', $LYCW->plugin_slug),
				2 => __('2 weeks', $LYCW->plugin_slug),
				3 => __('3 weeks', $LYCW->plugin_slug),
				4 => __('1 month', $LYCW->plugin_slug)
			)
		);

		$out = '';
		foreach ( $times as $period => $timeset ) {
			switch ($period) {
				case 'minute':
					$sc = MINUTE_IN_SECONDS;
					break;
				case 'hour':
					$sc = HOUR_IN_SECONDS;
					break;
				case 'day':
					$sc = DAY_IN_SECONDS;
					break;
				case 'week':
					$sc = WEEK_IN_SECONDS;
			}

			foreach ( $timeset as $n => $s ) {
				$sec = $sc * $n;
				$out .= sprintf(
					'<option value="%d" %s>%s</option>',
					$sec,
					selected( $cache_time, $sec, 0 ),
					__( $s, $this->plugin_slug )
				);
				unset($sec);
			}
		}
		return $out;
	}
}

// Register widget.
function lycw_register_widget() {
    register_widget( 'LYCW_Widget' );
}
add_action( 'widgets_init', 'lycw_register_widget' );
