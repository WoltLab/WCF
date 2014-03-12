/**
 * ImageViewer for WCF.
 * Based upon "Slimbox 2" by Christophe Beyls 2007-2012, http://www.digitalia.be/software/slimbox2, MIT-style license.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.ImageViewer = Class.extend({
	/**
	 * Initializes the ImageViewer for every a-tag with the attribute rel = imageviewer.
	 */
	init: function() {
		// navigation buttons
		$('<span class="icon icon16 icon-chevron-left jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.previous') + '" />').appendTo($('#lbPrevLink'));
		$('<span class="icon icon16 icon-chevron-right jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.next') + '" />').appendTo($('#lbNextLink'));
		
		// close and enlarge icons
		$('<span class="icon icon32 icon-remove jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.close') + '" />').appendTo($('#lbCloseLink'));
		var $buttonEnlarge = $('<span class="icon icon32 icon-resize-full jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.enlarge') + '" id="lbEnlarge" />').insertAfter($('#lbCloseLink'));
		
		// handle enlarge button
		$buttonEnlarge.click($.proxy(this._enlarge, this));
		
		this._initImageViewer();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.ImageViewer', $.proxy(this._domNodeInserted, this));
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Executes actions upon DOMNodeInserted events.
	 */
	_domNodeInserted: function() {
		this._initImageSizeCheck();
		this._initImageViewer();
	},
	
	/**
	 * Initializes the image viewer for all links with class ".jsImageViewer"
	 */
	_initImageViewer: function() {
		// disable ImageViewer on touch devices identifying themselves as 'mobile'
		if ($.browser.touch && /[Mm]obile/.test(navigator.userAgent)) {
			// Apple always appends mobile regardless if it is an iPad or iP(hone|od)
			if (!/iPad/.test(navigator.userAgent)) {
				return;
			}
		}
		
		var $links = $('a.jsImageViewer');
		if ($links.length) {
			$links.removeClass('jsImageViewer').slimbox({
				counterText: WCF.Language.get('wcf.imageViewer.counter'),
				loop: true
			});
		}
	},
	
	/**
	 * Redirects to image for full view.
	 */
	_enlarge: function() {
		var $url = $('#lbImage').css('backgroundImage');
		if ($url) {
			$url = $url.replace(/^url\((["']?)(.*)\1\)$/, '$2');
			window.location = $url;
		}
	},
	
	/**
	 * Initializes the image size check.
	 */
	_initImageSizeCheck: function() {
		$('.jsResizeImage').each($.proxy(function(index, image) {
			if (image.complete) this._checkImageSize({ currentTarget: image });
		}, this));
		
		$('.jsResizeImage').on('load', $.proxy(this._checkImageSize, this));
	},
	
	/**
	 * Checks the image size.
	 */
	_checkImageSize: function(event) {
		var $image = $(event.currentTarget);
		if (!$image.is(':visible')) {
			$image.off('load');
			
			return;
		}
		
		$image.removeClass('jsResizeImage');
		var $dimensions = $image.getDimensions();
		var $maxWidth = $image.parents('div').innerWidth();
		
		if ($dimensions.width > $maxWidth) {
			$image.css({
				height: Math.round($dimensions.height * ($maxWidth / $dimensions.width)) + 'px',
				width: $maxWidth + 'px'
			});
			
			if (!$image.parents('a').length) {
				$image.wrap('<a href="' + $image.attr('src') + '" />');
				$image.parent().slimbox();
			}
		}
	}
});

/**
 * Provides a focused image viewer for WCF.
 * 
 * Usage:
 * $('.triggerElement').wcfImageViewer({
 * 	shiftBy: 5,
 * 	
 * 	enableSlideshow: 1,
 * 	speed: 5,
 * 	
 * 	className: 'wcf\\data\\foo\\FooAction'
 * });
 */
$.widget('ui.wcfImageViewer', {
	/**
	 * active image index
	 * @var	integer
	 */
	_active: -1,
	
	/**
	 * active image object id
	 * @var	integer
	 */
	_activeImage: null,
	
	/**
	 * image viewer container object
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * overrides slideshow settings unless explicitly enabled by user
	 * @var	boolean
	 */
	_disableSlideshow: false,
	
	/**
	 * event namespace used to distinguish event handlers using $.proxy
	 * @var	string
	 */
	_eventNamespace: '',
	
	/**
	 * list of available images
	 * @var	array<object>
	 */
	_images: [ ],
	
	/**
	 * true if image viewer is open
	 * @var	boolean
	 */
	_isOpen: false,
	
	/**
	 * number of total images
	 * @var	integer
	 */
	_items: -1,
	
	/**
	 * maximum dimensions for enlarged view
	 * @var	object<integer>
	 */
	_maxDimensions: {
		height: 0,
		width: 0
	},
	
	/**
	 * action proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * true if slideshow is currently running
	 * @var	boolean
	 */
	_slideshowEnabled: false,
	
	/**
	 * visible width of thumbnail container
	 * @var	integer
	 */
	_thumbnailContainerWidth: 0,
	
	/**
	 * right margin of a thumbnail
	 * @var	integer
	 */
	_thumbnailMarginRight: 0,
	
	/**
	 * left offset of thumbnail list
	 * @var	integer
	 */
	_thumbnailOffset: 0,
	
	/**
	 * outer width of a thumbnail (includes margin)
	 * @var	integer
	 */
	_thumbnailWidth: 0,
	
	/**
	 * slideshow timer object
	 * @var	WCF.PeriodicalExecuter
	 */
	_timer: null,
	
	/**
	 * list of interface elements
	 * @var	object<jQuery>
	 */
	_ui: {
		buttonNext: null,
		buttonPrevious: null,
		header: null,
		image: null,
		imageContainer: null,
		imageList: null,
		slideshow: {
			container: null,
			enlarge: null,
			next: null,
			previous: null,
			toggle: null
		}
	},
	
	/**
	 * list of options parsed during init
	 * @var	object<mixed>
	 */
	options: {
		// navigation
		shiftBy: 5, // thumbnail slider control
		
		// slideshow
		enableSlideshow: 1,
		speed: 5, // time in seconds
		
		// ajax
		className: '' // must be an instance of \wcf\data\IImageViewerAction
	},
	
	/**
	 * Creates a new wcfImageViewer instance.
	 */
	_create: function() {
		this._active = -1;
		this._activeImage = null;
		this._container = null;
		this._didInit = false;
		this._disableSlideshow = (this.element.data('disableSlideshow'));
		this._eventNamespace = this.element.wcfIdentify();
		this._images = [ ];
		this._isOpen = false;
		this._items = -1;
		this._maxDimensions = {
			height: document.documentElement.clientHeight,
			width: document.documentElement.clientWidth
		};
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		this._slideshowEnabled = false;
		this._thumbnailContainerWidth = 0;
		this._thumbnailMarginRight = 0;
		this._thumbnailOffset = 0;
		this._thumbnaiLWidth = 0;
		this._timer = null;
		this._ui = { };
		
		this.element.click($.proxy(this.open, this));
	},
	
	/**
	 * Opens the image viewer.
	 * 
	 * @param	object		event
	 * @return	boolean
	 */
	open: function(event) {
		if (event) event.preventDefault();
		
		if (this._isOpen) {
			return false;
		}
		
		if (this._images.length === 0) {
			this._loadNextImages(true);
		}
		else {
			this._render(false, this.element.data('targetImageID'));
			
			if (this._items > 1 && this._slideshowEnabled) {
				this.startSlideshow();
			}
		}
		
		this._bindListener();
		
		this._isOpen = true;
		
		WCF.System.DisableScrolling.disable();
		
		return true;
	},
	
	/**
	 * Closes the image viewer.
	 * 
	 * @return	boolean
	 */
	close: function(event) {
		if (event) event.preventDefault();
		
		if (!this._isOpen) {
			return false;
		}
		
		this._container.removeClass('open');
		if (this._timer !== null) {
			this._timer.stop();
		}
		
		this._unbindListener();
		
		this._isOpen = false;
		
		WCF.System.DisableScrolling.enable();
		
		return true;
	},
	
	/**
	 * Enables the slideshow.
	 * 
	 * @return	boolean
	 */
	startSlideshow: function() {
		if (this._disableSlideshow || this._slideshowEnabled) {
			return false;
		}
		
		if (this._timer === null) {
			this._timer = new WCF.PeriodicalExecuter($.proxy(function() {
				var $index = this._active + 1;
				if ($index == this._items) {
					$index = 0;
				}
				
				this.showImage($index);
			}, this), this.options.speed * 1000);
		}
		else {
			this._timer.resume();
		}
		
		this._slideshowEnabled = true;
		
		this._ui.slideshow.toggle.children('span').removeClass('icon-play').addClass('icon-pause');
		
		return true;
	},
	
	/**
	 * Disables the slideshow.
	 * 
	 * @param	boolean		disableSlideshow
	 * @return	boolean
	 */
	stopSlideshow: function(disableSlideshow) {
		if (!this._slideshowEnabled) {
			return false;
		}
		
		this._timer.stop();
		if (disableSlideshow) {
			this._ui.slideshow.toggle.children('span').removeClass('icon-pause').addClass('icon-play');
		}
		
		this._slideshowEnabled = false;
		
		return true;
	},
	
	/**
	 * Binds event listeners.
	 */
	_bindListener: function() {
		$(document).on('keydown.' + this._eventNamespace, $.proxy(this._keyDown, this));
		$(window).on('resize.' + this._eventNamespace, $.proxy(this._renderImage, this));
	},
	
	/**
	 * Unbinds event listeners.
	 */
	_unbindListener: function() {
		$(document).off('keydown.' + this._eventNamespace);
		$(window).off('resize.' + this._eventNamespace);
	},
	
	/**
	 * Closes the slideshow on escape.
	 * 
	 * @param	object		event
	 * @return	boolean
	 */
	_keyDown: function(event) {
		switch (event.which) {
			// close slideshow
			case $.ui.keyCode.ESCAPE:
				this.close();
			break;
			
			// show previous image
			case $.ui.keyCode.LEFT:
				this._previousImage();
			break;
			
			// show next image
			case $.ui.keyCode.RIGHT:
				this._nextImage();
			break;
			
			// enable fullscreen mode
			case $.ui.keyCode.UP:
				if (!this._container.hasClass('maximized')) {
					this._toggleView();
				}
			break;
			
			// disable fullscreen mode
			case $.ui.keyCode.DOWN:
				if (this._container.hasClass('maximized')) {
					this._toggleView();
				}
			break;
			
			// jump to image page or full version
			case $.ui.keyCode.ENTER:
				var $link = this._ui.header.find('> div > h1 > a');
				if ($link.length == 1) {
					// forward to image page
					window.location = $link.prop('href');
				}
				else {
					// forward to full version
					this._ui.slideshow.full.trigger('click');
				}
			break;
			
			// toggle play/pause (80 = [p])
			case 80:
				this._ui.slideshow.toggle.trigger('click');
			break;
			
			default:
				return true;
			break;
		}
		
		return false;
	},
	
	/**
	 * Renders the image viewer UI.
	 * 
	 * @param	boolean		initialized
	 * @param	integer		targetImageID
	 */
	_render: function(initialized, targetImageID) {
		this._container.addClass('open');
		
		var $thumbnail = null;
		if (initialized) {
			$thumbnail = this._ui.imageList.children('li:eq(0)');
			this._thumbnailMarginRight = parseInt($thumbnail.css('marginRight').replace(/px$/, '')) || 0;
			this._thumbnailWidth = $thumbnail.outerWidth(true);
			this._thumbnailContainerWidth = this._ui.imageList.parent().innerWidth();
			
			if (this._items > 1 && this.options.enableSlideshow && !targetImageID) {
				this.startSlideshow();
			}
		}
		
		if (targetImageID) {
			this._ui.imageList.children('li').each($.proxy(function(index, item) {
				var $item = $(item);
				if ($item.data('objectID') == targetImageID) {
					$item.trigger('click');
					this.moveToImage($item.data('index'));
					
					return false;
				}
			}, this));
		}
		else if ($thumbnail !== null) {
			$thumbnail.trigger('click');
		}
			
		this._toggleButtons();
		
		// check if there is enough space to load more thumbnails
		this._preload();
	},
	
	/**
	 * Attempts to load the next images.
	 */
	_preload: function() {
		if (this._images.length < this._items) {
			var $thumbnailsWidth = this._images.length * this._thumbnailWidth;
			if ($thumbnailsWidth - this._thumbnailOffset < this._thumbnailContainerWidth) {
				this._loadNextImages(false);
			}
		}
	},
	
	/**
	 * Displays image on thumbnail click.
	 * 
	 * @param	object		event
	 */
	_showImage: function(event) {
		this.showImage($(event.currentTarget).data('index'), true);
	},
	
	/**
	 * Displays an image by index.
	 * 
	 * @param	integer		index
	 * @param	boolean		disableSlideshow
	 * @return	boolean
	 */
	showImage: function(index, disableSlideshow) {
		if (this._active == index) {
			return false;
		}
		
		this.stopSlideshow(disableSlideshow || false);
		
		// reset active marking
		if (this._active != -1) {
			this._images[this._active].listItem.removeClass('active');
		}
		
		this._active = index;
		var $image = this._images[index];
		
		this._ui.imageList.children('li').removeClass('active');
		$image.listItem.addClass('active');
		
		var $dimensions = this._ui.imageContainer.getDimensions('inner');
		var $newImageIndex = (this._activeImage ? 0 : 1);
		
		if (this._activeImage !== null) {
			this._ui.images[this._activeImage].removeClass('active');
		}
		
		this._activeImage = $newImageIndex;
		
		var $currentActiveImage = this._active;
		this._ui.imageContainer.addClass('loading');
		this._ui.images[$newImageIndex].off('load').prop('src', false).on('load', $.proxy(function() {
			this._imageOnLoad($currentActiveImage, $newImageIndex);
		}, this));
		
		this._renderImage($newImageIndex, $image, $dimensions);
		
		// user
		var $link = this._ui.header.find('> div > a').prop('href', $image.user.link).prop('title', $image.user.username);
		$link.children('img').prop('src', $image.user.avatarURL);
		
		// meta data
		var $title = WCF.String.escapeHTML($image.image.title);
		if ($image.image.link) $title = '<a href="' + $image.image.link + '">' + $image.image.title + '</a>';
		this._ui.header.find('> div > h1').html($title);
		
		var $seriesTitle = ($image.series && $image.series.title ? WCF.String.escapeHTML($image.series.title) : '');
		if ($image.series.link) $seriesTitle = '<a href="' + $image.series.link + '">' + $seriesTitle + '</a>';
		this._ui.header.find('> div > h2').html($seriesTitle);
		
		this._ui.header.find('> div > h3').text(WCF.Language.get('wcf.imageViewer.seriesIndex').replace(/{x}/, $image.listItem.data('index') + 1).replace(/{y}/, this._items));
		
		this._ui.slideshow.full.data('link', ($image.image.fullURL ? $image.image.fullURL : $image.image.url));
		
		this.moveToImage($image.listItem.data('index'));
		
		this._toggleButtons();
		
		return true;
	},
	
	/**
	 * Callback function for the image 'load' event.
	 * 
	 * @param	integer		currentActiveImage
	 * @param	integer		activeImageIndex
	 */
	_imageOnLoad: function(currentActiveImage, activeImageIndex) {
		// image did not load in time, ignore
		if (currentActiveImage != this._active) {
			return;
		}
		
		this._ui.imageContainer.removeClass('loading');
		this._ui.images[activeImageIndex].addClass('active');
		
		this.startSlideshow();
	},
	
	/**
	 * Renders target image, leaving 'imageData' undefined will invoke the rendering process for the currently active image.
	 * 
	 * @param	integer		targetIndex
	 * @param	object		imageData
	 * @param	object		containerDimensions
	 */
	_renderImage: function(targetIndex, imageData, containerDimensions) {
		if (!imageData) {
			targetIndex = this._activeImage;
			imageData = this._images[this._active];
			
			containerDimensions = {
				height: $(window).height() - (this._container.hasClass('maximized') ? 0 : 200),
				width: this._ui.imageContainer.innerWidth()
			};
		}
		
		// simulate padding
		containerDimensions.height -= 22;
		containerDimensions.width -= 20;
		
		this._ui.images[targetIndex].prop('src', imageData.image.url);
		
		var $height = imageData.image.height;
		var $width = imageData.image.width;
		var $ratio = 0.0;
		
		// check if image exceeds dimensions on the Y axis
		if ($height > containerDimensions.height) {
			$ratio = containerDimensions.height / $height;
			$height = containerDimensions.height;
			$width = Math.floor($width * $ratio);
		}
		
		// check if image exceeds dimensions on the X axis
		if ($width > containerDimensions.width) {
			$ratio = containerDimensions.width / $width;
			$width = containerDimensions.width;
			$height = Math.floor($height * $ratio);
		}
		
		var $left = Math.floor((containerDimensions.width - $width) / 2);
		
		this._ui.images[targetIndex].css({
			height: $height + 'px',
			left: ($left + 10) + 'px',
			marginTop: (Math.round($height / 2) * -1) + 'px',
			width: $width + 'px'
		});
	},
	
	/**
	 * Initialites the user interface.
	 * 
	 * @return	boolean
	 */
	_initUI: function() {
		if (this._didInit) {
			return false;
		}
		
		this._didInit = true;
		
		this._container = $('<div class="wcfImageViewer" />').appendTo(document.body);
		var $imageContainer = $('<div><img class="active" /><img /></div>').appendTo(this._container);
		var $imageList = $('<footer><span class="wcfImageViewerButtonPrevious icon icon-double-angle-left" /><div><ul /></div><span class="wcfImageViewerButtonNext icon icon-double-angle-right" /></footer>').appendTo(this._container);
		var $slideshowContainer = $('<ul />').appendTo($imageContainer);
		var $slideshowButtonPrevious = $('<li class="wcfImageViewerSlideshowButtonPrevious"><span class="icon icon48 icon-angle-left" /></li>').appendTo($slideshowContainer);
		var $slideshowButtonToggle = $('<li class="wcfImageViewerSlideshowButtonToggle pointer"><span class="icon icon48 icon-play" /></li>').appendTo($slideshowContainer);
		var $slideshowButtonNext = $('<li class="wcfImageViewerSlideshowButtonNext"><span class="icon icon48 icon-angle-right" /></li>').appendTo($slideshowContainer);
		var $slideshowButtonEnlarge = $('<li class="wcfImageViewerSlideshowButtonEnlarge pointer jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.button.enlarge') + '"><span class="icon icon48 icon-resize-full" /></li>').appendTo($slideshowContainer);
		var $slideshowButtonFull = $('<li class="wcfImageViewerSlideshowButtonFull pointer jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.button.full') + '"><span class="icon icon48 icon-external-link" /></li>').appendTo($slideshowContainer);
		
		this._ui = {
			buttonNext: $imageList.children('span.wcfImageViewerButtonNext'),
			buttonPrevious: $imageList.children('span.wcfImageViewerButtonPrevious'),
			header: $('<header><div class="box64"><a class="framed jsTooltip"><img /></a><h1 /><h2 /><h3 /></div></header>').appendTo(this._container),
			imageContainer: $imageContainer,
			images: [
				$imageContainer.children('img:eq(0)').on('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', function() { $(this).removeClass('animateTransformation'); }),
				$imageContainer.children('img:eq(1)').on('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', function() { $(this).removeClass('animateTransformation'); })
			],
			imageList: $imageList.find('> div > ul'),
			slideshow: {
				container: $slideshowContainer,
				enlarge: $slideshowButtonEnlarge,
				full: $slideshowButtonFull,
				next: $slideshowButtonNext,
				previous: $slideshowButtonPrevious,
				toggle: $slideshowButtonToggle
			}
		};
		
		this._ui.buttonNext.click($.proxy(this._next, this));
		this._ui.buttonPrevious.click($.proxy(this._previous, this));
		
		$slideshowButtonNext.click($.proxy(this._nextImage, this));
		$slideshowButtonPrevious.click($.proxy(this._previousImage, this));
		$slideshowButtonEnlarge.click($.proxy(this._toggleView, this));
		$slideshowButtonToggle.click($.proxy(function() {
			if (this._slideshowEnabled) {
				this.stopSlideshow(true);
			}
			else {
				this._disableSlideshow = false;
				this.startSlideshow();
			}
		}, this));
		$slideshowButtonFull.click(function(event) { window.location = $(event.currentTarget).data('link'); });
		
		// close button
		$('<span class="wcfImageViewerButtonClose icon icon48 icon-remove pointer jsTooltip" title="' + WCF.Language.get('wcf.global.button.close') + '" />').appendTo(this._ui.header).click($.proxy(this.close, this));
		
		return true;
	},
	
	/**
	 * Toggles between normal and fullscreen view.
	 */
	_toggleView: function() {
		this._ui.images[this._activeImage].addClass('animateTransformation');
		this._container.toggleClass('maximized');
		this._ui.slideshow.enlarge.toggleClass('active').children('span').toggleClass('icon-resize-full').toggleClass('icon-resize-small');
		
		this._renderImage(null, undefined, null);
	},
	
	/**
	 * Shifts the thumbnail list.
	 * 
	 * @param	object		event
	 * @param	integer		shiftBy
	 */
	_next: function(event, shiftBy) {
		if (this._ui.buttonNext.hasClass('pointer')) {
			if (shiftBy == undefined) {
				this.stopSlideshow(true);
			}
			
			var $maximumOffset = Math.max((this._items * this._thumbnailWidth) - this._thumbnailContainerWidth - this._thumbnailMarginRight, 0);
			this._thumbnailOffset = Math.min(this._thumbnailOffset + (this._thumbnailWidth * (shiftBy ? shiftBy : this.options.shiftBy)), $maximumOffset);
			this._ui.imageList.css('marginLeft', (this._thumbnailOffset * -1));
		}
		
		this._preload();
		
		this._toggleButtons();
	},
	
	/**
	 * Unshifts the thumbnail list.
	 * 
	 * @param	object		event
	 * @param	integer		shiftBy
	 */
	_previous: function(event, unshiftBy) {
		if (this._ui.buttonPrevious.hasClass('pointer')) {
			if (unshiftBy == undefined) {
				this.stopSlideshow(true);
			}
			
			this._thumbnailOffset = Math.max(this._thumbnailOffset - (this._thumbnailWidth * (unshiftBy ? unshiftBy : this.options.shiftBy)), 0);
			this._ui.imageList.css('marginLeft', (this._thumbnailOffset * -1));
		}
		
		this._toggleButtons();
	},
	
	/**
	 * Displays the next image.
	 * 
	 * @param	object		event
	 */
	_nextImage: function(event) {
		if (this._ui.slideshow.next.hasClass('pointer')) {
			this._disableSlideshow = true;
			
			this.stopSlideshow(true);
			this.showImage(this._active + 1);
		}
	},
	
	/**
	 * Displays the previous image.
	 * 
	 * @param	object		event
	 */
	_previousImage: function(event) {
		if (this._ui.slideshow.previous.hasClass('pointer')) {
			this._disableSlideshow = true;
			
			this.stopSlideshow(true);
			this.showImage(this._active - 1);
		}
	},
	
	/**
	 * Moves thumbnail list to target thumbnail.
	 * 
	 * @param	integer		seriesIndex
	 */
	moveToImage: function(seriesIndex) {
		// calculate start and end of thumbnail
		var $start = (seriesIndex - 3) * this._thumbnailWidth;
		var $end = $start + (this._thumbnailWidth * 5);
		
		// calculate visible offsets
		var $left = this._thumbnailOffset;
		var $right = this._thumbnailOffset + this._thumbnailContainerWidth;
		
		// check if thumbnail is within boundaries
		var $shouldMove = false;
		if ($start < $left || $end > $right) {
			$shouldMove = true;
		}
		
		// try to shift until the thumbnail itself and the next/previous 2 thumbnails are visible
		if ($shouldMove) {
			var $shiftBy = 0;
			
			// unshift
			if ($start < $left) {
				while ($start < $left) {
					$shiftBy++;
					$left -= this._thumbnailWidth;
				}
				
				this._previous(null, $shiftBy);
			}
			else {
				// shift
				while ($end > $right) {
					$shiftBy++;
					$right += this._thumbnailWidth;
				}
				
				this._next(null, $shiftBy);
			}
		}
	},
	
	/**
	 * Toggles control buttons.
	 */
	_toggleButtons: function() {
		// button 'previous'
		if (this._thumbnailOffset > 0) {
			this._ui.buttonPrevious.addClass('pointer');
		}
		else {
			this._ui.buttonPrevious.removeClass('pointer');
		}
		
		// button 'next'
		var $maximumOffset = (this._images.length * this._thumbnailWidth) - this._thumbnailContainerWidth - this._thumbnailMarginRight;
		if (this._thumbnailOffset >= $maximumOffset) {
			this._ui.buttonNext.removeClass('pointer');
		}
		else {
			this._ui.buttonNext.addClass('pointer');
		}
		
		// slideshow controls
		if (this._active > 0) {
			this._ui.slideshow.previous.addClass('pointer');
		}
		else {
			this._ui.slideshow.previous.removeClass('pointer');
		}
		
		if (this._active + 1 < this._images.length) {
			this._ui.slideshow.next.addClass('pointer');
		}
		else {
			this._ui.slideshow.next.removeClass('pointer');
		}
	},
	
	/**
	 * Inserts thumbnails.
	 * 
	 * @param	array<object>	images
	 */
	_createThumbnails: function(images) {
		for (var $i = 0, $length = images.length; $i < $length; $i++) {
			var $image = images[$i];
			
			var $listItem = $('<li class="loading pointer"><img src="' + $image.thumbnail.url + '" /></li>').appendTo(this._ui.imageList);
			$listItem.data('index', this._images.length).data('objectID', $image.objectID).click($.proxy(this._showImage, this));
			var $img = $listItem.children('img');
			if ($img.get(0).complete) {
				// thumbnail is read from cache
				$listItem.removeClass('loading');
			}
			else {
				$img.on('load', function() { $(this).parent().removeClass('loading'); });
			}
			
			$image.listItem = $listItem;
			this._images.push($image);
		}
	},
	
	/**
	 * Loads the next images via AJAX.
	 * 
	 * @param	boolean		init
	 */
	_loadNextImages: function(init) {
		this._proxy.setOption('data', {
			actionName: 'loadNextImages',
			className: this.options.className,
			interfaceName: 'wcf\\data\\IImageViewerAction',
			objectIDs: [ this.element.data('objectID') ],
			parameters: {
				maximumHeight: this._maxDimensions.height,
				maximumWidth: this._maxDimensions.width,
				offset: this._images.length,
				targetImageID: (init && this.element.data('targetImageID') ? this.element.data('targetImageID') : 0)
			}
		});
		this._proxy.setOption('showLoadingOverlay', false);
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.items) {
			this._items = data.returnValues.items;
		}
		
		var $initialized = this._initUI();
		
		this._createThumbnails(data.returnValues.images);
		
		var $targetImageID = (data.returnValues.targetImageID ? data.returnValues.targetImageID : 0);
		this._render($initialized, $targetImageID);
	}
});
