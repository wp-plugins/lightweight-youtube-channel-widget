=== Lightweight YouTube Channel Widget ===
Contributors: MaTachi
Donate Link: https://github.com/404
Tags: youtube, channel, playlist, favorites, widget, video, thumbnail, sidebar
Requires at least: 3.9.0
Tested up to: 4.1
Stable tag: 10.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Widget showing video thumbnails of recent videos from a YouTube channel or
playlist.

== Description ==

Use this plugin when you want to display a list of recent YouTube videos from a
channel or playlist in your site's sidebar.

This plugin is a fork of Aleksandar Urošević's [YouTube
Channel](https://wordpress.org/plugins/youtube-channel/). This plugin is much
more lightweight and does only have a subset of YouTube Channel's features.

The videos are each only presented with a thumbnail, and optionally also its
title and/or description. Clicking a thumbnail takes the user to the video's
page on YouTube. This results in a much smaller codebase and no additional
front-end dependencies. The original plugin, YouTube Channel by Aleksandar
Urošević, on the other hand depends on several JavaScript libraries
([jQuery](http://jquery.com/), [FitVids.JS](http://fitvidsjs.com/) and
[Magnific Popup](http://dimsemenov.com/plugins/magnific-popup/)), which
potentially makes the whole WordPress site both slower to load and use.

= Features =
* Display latest videos from YouTube channel, favorites or playlist.
* Option to get random videos from resources mentioned above.
* The videos are displayed with a thumbnail.
* Clicking a thumbnail takes the user to the video's page on YouTube.
* Custom caching timeout.

= Styling =
You can use the `style.css` from your theme to style the widget's content.

* `.youtube_channel` - Main widget wrapper class (non-responsive block has
  additional class `default`, responsive block has additional class
  `responsive`).
* `.ytc_title` - Class of video title above thumbnail.
* `.ytc_video_container` - Class of a single item's container wrapper.
* `.ytc_video_1`, `.ytc_video_2`, ... - Class for the container of a single
  item where the number is the item's placement in the list.
* `.ytc_video_first` - Class of the first item's container.
* `.ytc_video_last` - Class of the last item's container.
* `.ytc_video_mid` - Class of all other containers.
* `.ytc_description` - Class for video description text.

= Developer =

The full source code of the plugin can be found on Github at
[github.com/MaTachi/lightweight-youtube-channel-widget](https://github.com/MaTachi/lightweight-youtube-channel-widget).

= Credits =

* Original codebase ([YouTube Channel version
  2.4.1.3](https://wordpress.org/plugins/youtube-channel/)) is written by
  [Aleksandar Urošević](http://urosevic.net/) and licensed under GPLv3.

== Installation ==

1. Upload the plugin's directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Add the widget to one of your theme's widget areas.

== Frequently Asked Questions ==

= Who should I direct support questions to? =

Daniel Jonsson. Aleksandar Urošević has not been involved in this fork.

== Changelog ==

= 10.0 (2015-01-03) =
* Initial release

