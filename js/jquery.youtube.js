(function ( $, window, document, undefined ) {

	var pluginName = 'responsiveplaylist';
	var defaults = {
				autoPlay: false,
				allowFullScreen: true,
				deepLinks: false,
				onChange: function(){},
				start: 1,
				youtube: {
					autohide: '2', // '2' = autohide title, '1' = autohide everything, '0' = show all
					rel: '1', // '1' = show related videos, '0' = hide related videos
					theme: 'dark', // 'light' = standard theme, 'dark' = dark theme
					color: 'white', // 'red' = red progress bar, 'white' = white progress bar
					showinfo: '1', // '1' = show title and info, '0' = hide title and info
					vq: 'medium' // 'vq=small' = 240p, 'vq=medium' = 360p, 'vq=large' = 480p, 'vq=hd720' = 720p, 'vq=hd1080' = 1080p
				},
				vimeo: {
					title: '1', // '1' = show title, '0' = hide title
					byline: '1', // '1' = show byline, '0' = hide byline
					portrait: '1', // '1' = show portrait, '0' = hide portrait
					color: 'ffffff' // player interface color (do not include # symbol)
				},
				// youtubeUsername: 'username',
				// vimeoUsername: 'username',
				// youtubePlaylist: 'XXXXXXXXXXXXXXXXXX',
				// vimeoAlbum: 'XXXXXXX',
				holderId: 'rp_video',
				secure: 'auto' //false|true|'auto'
			};


	function isYouTube(videoID){
		if(videoID.length <= 8){
			return false;
		}
		return true;
	}

	// Get a video id from a url
	function getVideoID(url) {
		var id = null;

		if(url.indexOf('vim') !== -1){
			// look for a string with 'vimeo', then whatever, then a 
			// forward slash and a group of digits.
			var match = /vimeo.*\/(\d+)/i.exec( url );

			// if the match isn't null (i.e. it matched)
			if ( match ) {
				// the grouped/matched digits from the regex
				id =  match[1];
			}
		} else if(url.indexOf('yout') !== -1){
			var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
			var match = url.match(regExp);

			if (match&&match[2].length==11){
			    id = match[2];
			}
		}

		return id;
	};

	function getVimeoData(options){
		var link = options.link,
				url = options.url;

		$.ajax({
			url: url,
			dataType: "jsonp",
			success: function (data) {
				var title = data[0].title;
				var author = 'by ' + data[0].user_name;

				if(title.length > 50){
					link.find('.rp_title').html(title.substr(0, 50)+'&hellip;');
				}
				else {
					link.find('.rp_title').html(title);
				}

				link.find('.rp_author').html(author);
				link.find('.rp_thumbnail img').attr('src', data[0].thumbnail_small);
			}
		});
	};

	function getYouTubeData(options){
		var link = options.link,
			url = options.url;

		$.ajax({
			url: url,
			dataType: "jsonp",
			success: function (data) {
				var title = data.entry[ "title" ].$t;
				var author = 'by ' + data.entry[ "author" ][ 0 ].name.$t;
				if(title.length > 50){
					link.find('.rp_title').html(title.substr(0, 50)+'&hellip;');
				}
				else {
					link.find('.rp_title').html(title);
				}
				link.find('.rp_author').html(author);
			}
		});
	};

	function getYoutubePlaylistID(username, cb){
		$.ajax({
			url: 'https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername='+username+'&key=AIzaSyCIlwa-7d7qpKS0Nj5vhI7tb-0minC-qZ8',
			dataType: 'jsonp',
			success: function(result){
				cb(result.items[0].contentDetails.relatedPlaylists.uploads);
			}
		});
	};

	function getYoutubeVideos(playlistID, cb){
		$.ajax({
			url: 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=50&playlistId='+playlistID+'&key=AIzaSyCIlwa-7d7qpKS0Nj5vhI7tb-0minC-qZ8',
			dataType: 'jsonp',
			success: function(result){
				cb(result.items.reverse());
			}
		});
	};

	function prependYoutubeVideos(videos, cb){
		var video,
				videoID;

		for(var i = 0, j = videos.length; i < j; i++){
			video = videos[i];
			videoID = video.snippet.resourceId.videoId;
			li = '<li><a href="https://www.youtube.com/watch?v='+videoID+'"></a></li>';
			$(li).prependTo('#rp_playlist');
		}

		cb();
	};

	function getVimeoVideos(username, cb){
		$.ajax({
			url: 'http://vimeo.com/api/v2/'+username+'/videos.json',
			dataType: 'jsonp',
			success: function(result){
				cb(result.reverse());
			}
		});
	};

	function getVimeoAlbum(albumID, cb){
		$.ajax({
			url: 'http://vimeo.com/api/v2/album/'+albumID+'/videos.json',
			dataType: 'jsonp',
			success: function(result){
				cb(result.reverse());
			}
		});
	};

	function prependVimeoVideos(videos, cb){
		var video,
				videoID;

		for(var i = 0, j = videos.length; i < j; i++){
			video = videos[i];
			videoID = video.id;
			li = '<li><a href="https://www.vimeo.com/'+videoID+'"></a></li>';
			$(li).prependTo('#rp_playlist');
		}

		cb();
	};

	// Main plugin contstructor
	function Plugin(element, options ) {
			this.element = element;
			this.options = $.extend( {}, defaults, options) ;
			this._defaults = defaults;
			this._name = pluginName;

			this._protocol = (this.options.secure === 'auto') ? window.location.protocol === 'https:' ? 'https://' : 'http://' :
				this.options.secure ? 'https://' : 'http://';
			this.init();
	};


	Plugin.prototype = {


			// Initialise gallery - Loop through <li> elements, setting up click handlers etc.
			init: function() {
				var self = this;
				var initialItem = self.options.deepLinks && window.location.hash.indexOf('#video-') !== -1 ? window.location.hash : null;

				var createListElements = function(){
					// Setup initial classification of content
					$(self.element).find('li').each(function(index) {

						var listItem = $(this),
								listIndex = index + 1;

						listItem.find('a:first').each(function() {

							var link = $(this),
								videoID = getVideoID(link.attr('href')),
								replacedText = listItem.text();

							link.data('yt-href', link.attr('href'));
							link.attr('href', '#video-' + listIndex);
							link.data('yt-id', videoID);

							var thumbHtml,
									thumbUrl = '';

							if(!isYouTube(videoID)) { 
								getVimeoData({
									link: link,
									url: self._protocol + 'vimeo.com/api/v2/video/' + videoID + '.json'
								});
							} else {
								getYouTubeData({
									link: link,
									url: self._protocol + 'gdata.youtube.com/feeds/api/videos/' + videoID + '?v=2&alt=json'
								});
								thumbUrl = self._protocol + 'i.ytimg.com/vi/' + videoID + '/default.jpg';
							}

							thumbHtml = '<span class="rp_thumbnail"><img src="' + thumbUrl + '" alt="' + replacedText + '" /></span><p class="rp_title"></p><p class="rp_author"></p>';
							link.empty().html(thumbHtml + replacedText).attr('title', replacedText);

							if (!self.options.deepLinks) {
								link.click(function(e) {
									e.preventDefault();
									self.handleClick(link, self.options);
									self.options.onChange.call();
								});
							}

						});

						var firstLink = $(listItem.children('a')[0]);
						if (initialItem) {
							if (firstLink.attr('href') === initialItem) {
								self.handleClick(firstLink, self.options);
							}
						}
						else if (listIndex === self.options.start) {
							self.handleClick(firstLink, self.options);
						}

					});

					// Setup deep links
					if (self.options.deepLinks) {
						$(window).bind('hashchange', function(e) {
							var hash = window.location.hash;
							var clicked = $(self.element).find('a[href="' + hash + '"]');
							if (clicked.length > 0) {
								self.handleClick(clicked, self.options);
							}
							else if (hash === '') {
								self.handleClick($(self.element).find('a:first'), self.options);
							}
						});
					}

					//prevent most blinking when videos load
	        // Inject CSS which makes iframe invisible
	        var div = document.createElement('div'),
	            ref = document.getElementsByTagName('base')[0] || 
	                  document.getElementsByTagName('script')[0];

	        div.innerHTML = '&shy;<style> iframe { visibility: hidden; } </style>';

	        ref.parentNode.insertBefore(div, ref);
	        // Removes CSS    
	        window.onload = function() {
	            div.parentNode.removeChild(div);
	        }
				};

				var addYouTubePlaylist = function(done){
					getYoutubeVideos(self.options.youtubePlaylist, function(youtubeVideos){
						prependYoutubeVideos(youtubeVideos, done);
					});
				};

				var addVimeoAlbum = function(done){
					getVimeoAlbum(self.options.vimeoAlbum, function(vimeoVideos){
						prependVimeoVideos(vimeoVideos, done);
					});
				};

				var addYouTubeUsername = function(done){
					getYoutubePlaylistID(self.options.youtubeUsername, function(playlistID){
						getYoutubeVideos(playlistID, function(youtubeVideos){
							prependYoutubeVideos(youtubeVideos, done);
						});
					});
				};

				var addVimeoUsername = function(done){
					getVimeoVideos(self.options.vimeoUsername, function(vimeoVideos){
						prependVimeoVideos(vimeoVideos, done);
					});
				};

				var functions = [];
				if(self.options.vimeoAlbum) functions.push(addVimeoAlbum);
				if(self.options.youtubePlaylist) functions.push(addYouTubePlaylist);
				if(self.options.vimeoUsername) functions.push(addVimeoUsername);
				if(self.options.youtubeUsername) functions.push(addYouTubeUsername);

				functions.push(createListElements);

				// executes an array of functions in order
				var syncFunctions = function(functions, index) {
					if(!index) index = 0;

					functions[index].call(this, function(){
						if(index + 1 < functions.length){
							syncFunctions(functions, index + 1);
						}
					});
				};

				syncFunctions(functions);
			},

			// Get youtube embed code
			getEmbedCode: function(options, id) {
				if(!isYouTube(id)){ //vimeo
					var html = '';
					html += '<iframe';

					html += ' src="' + this._protocol + 'player.vimeo.com/video/' + id;
					html += '?';
					html += (options.autoPlay) ? 'autoplay=1' : 'autoplay=0';
					html += '&title='+options.vimeo.title;
					html += '&byline='+options.vimeo.byline;
					html += '&portrait='+options.vimeo.portrait;
					html += '&color='+options.vimeo.color;
					html += '" ';

					if(options.allowFullScreen){
						html += ' webkitAllowFullScreen mozallowfullscreen allowFullScreen ';
					}
					html += ' type="text/html" frameborder="0" ></iframe>';
				} else {
					var html = '';
					html += '<iframe';
					html += ' src="' + this._protocol + 'www.youtube.com/embed/' + id;
					html += '?';
					html += (options.autoPlay) ? 'autoplay=1' : 'autoplay=0';
					html += '&autohide='+options.youtube.autohide;
					html += '&rel='+options.youtube.rel;
					html += '&theme='+options.youtube.theme;
					html += '&color='+options.youtube.color;
					html += '&showinfo='+options.youtube.showinfo;
					html += '&vq='+options.youtube.vq;
					html += '" ';

					if(options.allowFullScreen){
						html += ' webkitAllowFullScreen mozallowfullscreen allowFullScreen ';
					}
					html += ' frameborder="0" ></iframe>';
				}

				return html;
			},

			// Handle clicks on all items
			handleClick: function(link, options) {
				options.onChange.call();
				return this.handleVideoClick(link, options);
			},


			// Handle clicks on video items
			handleVideoClick: function(link, options) {
				var self = this;
				var holder = (options.holder ? options.holder : $('#' + options.holderId));
				holder.html(self.getEmbedCode(self.options, link.data('yt-id')));
				link.parent().parent('ul').find('li.rp_currentVideo').removeClass('rp_currentVideo');
				link.parent('li').addClass('rp_currentVideo');

				return false;
			}
	};

	$.fn[pluginName] = function (options) {
			return this.each(function () {
					if (!$.data(this, 'plugin_' + pluginName)) {
							$.data(this, 'plugin_' + pluginName,
							new Plugin(this, options));
					}
			});
	};

})(jQuery, window, document);
