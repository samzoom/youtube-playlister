## YouTube Playlister ##

This plugin will allow you to create a custom playlist of YouTube Videos using a simple shortcode, you can enter YouTube URL's, a YouTube Username, even a Youtube Playlist ID to append onto your Custom Playlist in either a post or a page, or even a custom post type.

![screenshot](https://raw.github.com/chrismccoy/youtube-playlister/master/screenshot.jpg)

### Example of a shortcode ###

<pre>
[youtubeplaylist username="fred"]
https://www.youtube.com/watch?v=ILziHvCqsuU
https://www.youtube.com/watch?v=BFd2dDtXv60
https://www.youtube.com/watch?v=VruZ_KFl5RA
https://www.youtube.com/watch?v=kaY_-aNidJc
https://www.youtube.com/watch?v=kMDwuFSK4ZE
https://www.youtube.com/watch?v=GMoRiTJ_7Tw
[/youtubeplaylist]
</pre>

### Default Parameters for shortcode ####

<pre>
// YouTube Username
// Available Options: Any Valid YouTube Username
// Default: none

'username' => ''

// YouTube Playlist ID
// Available Options: Any Valid YouTube Playlist ID
// Default: None

'playlist' => ''

// Auto Play Videos
// Available Options: true,false
// Default: false

'autoplay' => ''

// Allow to go fullscreen
// Available Options: true,false
// Default: true

'fullscreen' => ''

// Theme
// Available Options: dark,light
// Default: dark

'theme' => ''

// Progress bar color:
// Available Options:  white,red
// Default: white

'color' => ''

// Show Related Videos
// Available Options: true,false
// Default: true

'related' => '' 

// Show title and info
// Available Options: true,false
// Default: true
'showinfo' => ''

// Autohide Video Information 
// Available Options: title = autohide title, everything = auto hide everything, all = show all
// Default: title

'autohide' => '' 

// Quality of videos
// Available Options: 'small' = 240p, 'medium' = 360p, 'large' = 480p, 'hd720' = 720p, 'hd1080' = 1080p
// Default: medium

'quality' => '', 
</pre>

