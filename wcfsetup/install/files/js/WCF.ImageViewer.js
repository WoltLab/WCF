"use strict";

/**
 * Enhanced image viewer for WCF.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.ImageViewer = Class.extend({
	/**
	 * trigger element to mimic a slideshow button
	 * @var	jQuery
	 */
	_triggerElement: null,
	
	/**
	 * Initializes the WCF.ImageViewer class.
	 */
	init: function() {
		this._triggerElement = $('<span class="wcfImageViewerTriggerElement" />').data('disableSlideshow', true).hide().appendTo(document.body);
		this._triggerElement.wcfImageViewer({
			enableSlideshow: 0,
			imageSelector: '.jsImageViewerEnabled',
			staticViewer: true
		});
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.ImageViewer', $.proxy(this._domNodeInserted, this));
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Executes actions upon DOMNodeInserted events.
	 */
	_domNodeInserted: function() {
		this._initImageSizeCheck();
		this._rebuildImageViewer();
	},
	
	/**
	 * Rebuilds the image viewer.
	 */
	_rebuildImageViewer: function() {
		var $links = $('a.jsImageViewer');
		if ($links.length) {
			$links.removeClass('jsImageViewer').addClass('jsImageViewerEnabled').click($.proxy(this._click, this));
		}
	},
	
	/**
	 * Handles click on an image with image viewer support.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		// ignore clicks while ctrl key is being pressed
		if (event.ctrlKey) {
			return;
		}
		
		event.preventDefault();
		event.stopPropagation();
		// skip if element is in a popover
		if ($(event.currentTarget).closest('.popover').length) return;
		
		this._triggerElement.wcfImageViewer('open', null, $(event.currentTarget).wcfIdentify());
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
		
		// check if image falls within the signature, in that case ignore it
		if ($image.closest('.messageSignature').length) {
			return;
		}
		
		// setting img { max-width: 100% } causes the image to fit within boundaries, but does not reveal the original dimenions
		var $imageObject = new Image();
		$imageObject.src = $image.attr('src');
		
		var $maxWidth = $image.closest('div.messageText, div.messageTextPreview').width();
		if ($maxWidth < $imageObject.width) {
			if (!$image.parents('a').length) {
				$image.wrap('<a href="' + $image.attr('src') + '" class="jsImageViewerEnabled embeddedImageLink" />');
				$image.parent().click($.proxy(this._click, this));
				
				if ($image.css('float') == 'right') {
					$image.parent().addClass('messageFloatObjectRight');
				}
				else if ($image.css('float') == 'left') {
					$image.parent().addClass('messageFloatObjectLeft');
				}
				$image[0].style.removeProperty('float');
				$image[0].style.removeProperty('margin');
			}
		}
		else {
			$image.removeClass('embeddedAttachmentLink');
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
	 * true if image viewer uses the mobile-optimized UI
	 * @var	boolean
	 */
	_isMobile: false,
	
	/**
	 * true if image viewer is open
	 * @var	boolean
	 */
	_isOpen: false,

	/**
	 * @var HTMLElement|null
	 */
	_messageSignature: null,
	
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
		className: '', // must be an instance of \wcf\data\IImageViewerAction
		
		// alternative mode - static view
		imageSelector: '',
		staticViewer: false
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
		this._isMobile = false;
		this._isOpen = false;
		this._items = -1;
		this._maxDimensions = {
			height: document.documentElement.clientHeight,
			width: document.documentElement.clientWidth
		};
		this._messageSignature = null;
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

		window.addEventListener('popstate', (function(event) {
			if (event.state != null && event.state.name === 'imageViewer') {
				if (event.state.container === this._eventNamespace) {
					this.open(event);
					this.showImage(event.state.image);
					
					return;
				}
			}
			
			this.close(event);
		}).bind(this));
	},
	
	/**
	 * Opens the image viewer.
	 * 
	 * @param	object		event
	 * @param	string		targetImageElementID
	 * @return	boolean
	 */
	open: function(event, targetImageElementID) {
		if (event) event.preventDefault();
		
		if (this._isOpen) {
			return false;
		}

		if (document.activeElement instanceof HTMLElement) {
			document.activeElement.blur();
		}
		
		// add history item for the image viewer
		if (!event || event.type !== 'popstate') {
			window.history.pushState({
				name: 'imageViewer'
			}, '', '');
		}
		
		this._messageSignature = null;
		if (this.options.staticViewer) {
			if (targetImageElementID) {
				this._messageSignature = document.getElementById(targetImageElementID).closest(".messageSignature");
			}

			// Reset the internal state because it could refer to a different set of images.
			this._active = -1;
			if (this._activeImage !== null) {
				this._ui.images[this._activeImage].removeClass('active');
			}
			this._activeImage = null;

			var $images = this._getStaticImages();
			this._initUI();
			this._createThumbnails($images, true);
			this._render(true, undefined, targetImageElementID);
			
			this._isOpen = true;
			
			WCF.System.DisableScrolling.disable();
			WCF.System.DisableZoom.disable();
			
			// switch to fullscreen mode on smartphones
			if ($.browser.touch) {
				setTimeout($.proxy(function() {
					if (this._isMobile && !this._container.hasClass('maximized')) {
						this._toggleView();
					}
				}, this), 500);
			}
		}
		else {
			if (this._images.length === 0) {
				this._loadNextImages(true);
			}
			else {
				this._render(false, this.element.data('targetImageID'));
				
				if (this._items > 1 && this._slideshowEnabled) {
					this.startSlideshow();
				}
				
				this._isOpen = true;
				
				WCF.System.DisableScrolling.disable();
				WCF.System.DisableZoom.disable();
			}
		}
		
		this._bindListener();
		
		require(['Ui/Screen'], function(UiScreen) {
			UiScreen.pageOverlayOpen();
		});
		
		return true;
	},
	
	/**
	 * Closes the image viewer.
	 * 
	 * @return	boolean
	 */
	close: function(event) {
		if (event) event.preventDefault();
		
		// clear history item of the image viewer
		if (!event || event.type !== 'popstate') {
			window.history.back();
			return;
		}
		
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
		WCF.System.DisableZoom.enable();
		
		require(['Ui/Screen'], function(UiScreen) {
			UiScreen.pageOverlayClose();
		});
		
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
		
		this._ui.slideshow.toggle[0].querySelector("fa-icon").setIcon("pause");
		
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
			this._ui.slideshow.toggle[0].querySelector("fa-icon").setIcon("play");
		}
		
		this._slideshowEnabled = false;
		
		return true;
	},
	
	/**
	 * Binds event listeners.
	 */
	_bindListener: function() {
		$(document).on('keydown.' + this._eventNamespace, $.proxy(this._keyDown, this));
		$(window).on('resize.' + this._eventNamespace, () => {
			// The resize event can trigger before the mobile UI has
			// adapted to the new screen size (`screen-sm-down` no
			// longer matches or previously did not match).
			window.setTimeout(() => this._renderImage(), 0);
		});
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
				var $link = this._ui.header.find('h1 > a');
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
	 * @param	string		targetImageElementID
	 */
	_render: function(initialized, targetImageID, targetImageElementID) {
		this._container.addClass('open');
		
		var $thumbnail = null;
		if (initialized) {
			$thumbnail = this._ui.imageList.children('li:eq(0)');
			this._thumbnailMarginRight = parseInt($thumbnail.css('marginRight').replace(/px$/, '')) || 0;
			this._thumbnailWidth = $thumbnail.outerWidth(true);
			this._thumbnailContainerWidth = this._ui.imageList.parent().innerWidth();
			
			if (this._items > 1 && this.options.enableSlideshow && !targetImageID && !targetImageElementID) {
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
		else if (targetImageElementID) {
			var images = [];

			$(this.options.imageSelector).each((function (_index, image) {
				// If the target image is inside a signature, then only include images within
				// the same signature. Otherwise this check will exclude images that are within
				// a user's signature.
				if (image.closest(".messageSignature") !== this._messageSignature) {
					return;
				}

				images.push(image);
			}).bind(this));

			var $i = 0;
			images.forEach(function (image, index) {
				if (image.id === targetImageElementID) {
					$i = index;
				}
			})
			
			var $item = this._ui.imageList.children('li:eq(' + $i + ')');
			
			// check if currently active image does not exist anymore
			if (this._active !== -1) {
				var $clear = false;
				if (this._active != $item.data('index')) {
					$clear = true;
				}
				
				if (this._ui.images[this._activeImage].prop('src') != this._images[this._active].image.url) {
					$clear = true;
				}
				
				if ($clear) {
					// reset active state
					this._active = -1;
				}
			}
			
			$item.trigger('click');
			this.moveToImage($item.data('index'));
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
		
		// store latest image in history entry
		window.history.replaceState({
			name: 'imageViewer',
			container: this._eventNamespace,
			image: this._active
		}, '', '');
		
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
		this._ui.images[$newImageIndex].off('load').prop('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='); // 1x1 pixel transparent gif
		this._ui.images[$newImageIndex].on('load', $.proxy(function() {
			this._imageOnLoad($currentActiveImage, $newImageIndex);
		}, this));
		
		this._renderImage($newImageIndex, $image, $dimensions);
		
		// user
		if (!this.options.staticViewer) {
			var $link = this._ui.header.find('> div > a').prop('href', $image.user.link).prop('title', $image.user.username);
			$link.children('img').prop('src', $image.user.avatarURL);
		}
		
		// meta data
		var $title = WCF.String.escapeHTML($image.image.title);
		if ($image.image.link) $title = '<a href="' + $image.image.link + '">' + $title + '</a>';
		this._ui.header.find('h1').html($title);
		
		if (!this.options.staticViewer) {
			var $seriesTitle = ($image.series && $image.series.title ? WCF.String.escapeHTML($image.series.title) : '');
			if ($image.series.link) $seriesTitle = '<a href="' + $image.series.link + '">' + $seriesTitle + '</a>';
			this._ui.header.find('h2').html($seriesTitle);
		}
		
		this._ui.header.find('h3').text(WCF.Language.get('wcf.imageViewer.seriesIndex').replace(/{x}/, $image.listItem.data('index') + 1).replace(/{y}/, this._items));
		
		this._ui.slideshow.full[0].querySelector('a').href = $image.image.fullURL ? $image.image.fullURL : $image.image.url;
		
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
		
		if (this.options.staticViewer) {
			this._renderImage(activeImageIndex, null);
		}
		
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
		var $checkForComplete = true;
		if (!imageData) {
			targetIndex = this._activeImage;
			imageData = this._images[this._active];
			
			containerDimensions = {
				height: $(window).height() - (this._container.hasClass('maximized') || this._container.hasClass('wcfImageViewerMobile') ? 0 : 200),
				width: this._ui.imageContainer.innerWidth()
			};
			
			$checkForComplete = false;
		}
		
		// simulate padding
		containerDimensions.height -= 22;
		containerDimensions.width -= 20;
		
		var $image = this._ui.images[targetIndex];
		if ($image.prop('src') !== imageData.image.url) {
			// assigning the same exact source again breaks Internet Explorer 10
			$image.prop('src', imageData.image.url);
		}
		
		if ($checkForComplete && $image[0].complete) {
			$image.trigger('load');
		}
		
		if (this.options.staticViewer && !imageData.image.height && $image[0].complete) {
			// Firefox and Safari returns bogus values if attempting to read the real dimensions
			if ($.browser.mozilla || $.browser.safari) {
				var $img = new Image();
				$img.src = imageData.image.url;
				
				imageData.image.height = $img.height || $image[0].naturalHeight;
				imageData.image.width = $img.width || $image[0].naturalWidth;
			}
			else {
				$image.css({
					height: 'auto',
					width: 'auto'
				});
				
				imageData.image.height = $image[0].height;
				imageData.image.width = $image[0].width;
			}
		}
		
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
	 * Initializes the user interface.
	 * 
	 * @return	boolean
	 */
	_initUI: function() {
		if (this._didInit) {
			return false;
		}
		
		this._didInit = true;
		
		this._container = $('<div class="wcfImageViewer' + (this.options.staticViewer ? ' wcfImageViewerStatic' : '') + '" />').appendTo(document.body);
		var $imageContainer = $('<div><img /><img /></div>').appendTo(this._container);
		var $imageList = $('<footer><span class="wcfImageViewerButtonPrevious"><fa-icon size="24" name="angles-left"></fa-icon></span><div><ul /></div><span class="wcfImageViewerButtonNext"><fa-icon size="24" name="angles-right"></fa-icon></span></footer>').appendTo(this._container);
		var $slideshowContainer = $('<ul />').appendTo($imageContainer);
		var $slideshowButtonPrevious = $('<li class="wcfImageViewerSlideshowButtonPrevious"><fa-icon size="32" name="angle-left"></fa-icon></li>').appendTo($slideshowContainer);
		var $slideshowButtonToggle = $('<li class="wcfImageViewerSlideshowButtonToggle pointer"><fa-icon size="32" name="play"></fa-icon></li>').appendTo($slideshowContainer);
		var $slideshowButtonNext = $('<li class="wcfImageViewerSlideshowButtonNext"><fa-icon size="32" name="angle-right"></fa-icon></li>').appendTo($slideshowContainer);
		var $slideshowButtonEnlarge = $('<li class="wcfImageViewerSlideshowButtonEnlarge pointer jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.button.enlarge') + '"><fa-icon size="32" name="expand"></fa-icon></li>').appendTo($slideshowContainer);
		var $slideshowButtonFull = $('<li class="wcfImageViewerSlideshowButtonFull pointer jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.button.full') + '"><a href="#" target="_blank"><fa-icon size="32" name="arrow-up-right-from-square"></fa-icon></a></li>').appendTo($slideshowContainer);
		
		this._ui = {
			buttonNext: $imageList.children('span.wcfImageViewerButtonNext'),
			buttonPrevious: $imageList.children('span.wcfImageViewerButtonPrevious'),
			header: $('<header><div' + (this.options.staticViewer ? '>' : ' class="box64"><a class="jsTooltip"><img /></a>' ) + '<div><h1 /><h2 /><h3 /></div></div></header>').appendTo(this._container),
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
			if (this._items < 2) {
				return;
			}
			
			if (this._slideshowEnabled) {
				this.stopSlideshow(true);
			}
			else {
				this._disableSlideshow = false;
				this.startSlideshow();
			}
		}, this));
		
		// close button
		$(`<button type="button" class="wcfImageViewerButtonClose jsTooltip" title="${WCF.Language.get('wcf.global.button.close')}">
			<fa-icon size="48" name="xmark"></fa-icon>
		</button>`).appendTo(this._ui.header).click($.proxy(this.close, this));
		
		if (!$.browser.mobile) {
			// clicking on the inner container should close the dialog, but it should not be available on mobile due to
			// the lack of precision causing accidental closing, the close button is big enough and easily reachable
			$imageContainer.click((function(event) {
				if (event.target === $imageContainer[0]) {
					this.close();
				}
			}).bind(this));
		}
		
		WCF.DOMNodeInsertedHandler.execute();
		
		require(['Ui/Screen'], function(UiScreen) {
			UiScreen.on('screen-sm-down', {
				match: $.proxy(this._enableMobileView, this),
				unmatch: $.proxy(this._disableMobileView, this)
			});
		}.bind(this));
		
		return true;
	},
	
	/**
	 * Enables the mobile-optimized UI.
	 */
	_enableMobileView: function() {
		this._container.addClass('wcfImageViewerMobile');
		
		var self = this;
		this._ui.imageContainer.swipe({
			swipeLeft: function(event) {
				if (self._container.hasClass('maximized')) {
					self._nextImage(event);
				}
			},
			swipeRight: function(event) {
				if (self._container.hasClass('maximized')) {
					self._previousImage(event);
				}
			},
			tap: function(event, element) {
				// tap fires before click, prevent conflicts
				switch (element.tagName) {
					case 'DIV':
					case 'IMG':
						self._toggleView();
					break;
				}
			}
		});
		
		this._isMobile = true;
	},
	
	/**
	 * Disables the mobile-optimized UI.
	 */
	_disableMobileView: function() {
		this._container.removeClass('wcfImageViewerMobile');
		this._ui.imageContainer.swipe('destroy');
		
		this._isMobile = false;
	},
	
	/**
	 * Toggles between normal and fullscreen view.
	 */
	_toggleView: function() {
		this._ui.images[this._activeImage].addClass('animateTransformation');
		this._container.toggleClass('maximized');
		this._ui.slideshow.enlarge.toggleClass('active');
		this._ui.slideshow.enlarge[0].querySelector("fa-icon").setIcon("compress");
		
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
			
			if (event) {
				event.preventDefault();
				event.stopPropagation();
			}
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
			
			if (event) {
				event.preventDefault();
				event.stopPropagation();
			}
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
		
		if (this._items < 2) {
			this._ui.slideshow.toggle.removeClass('pointer');
		}
		else {
			this._ui.slideshow.toggle.addClass('pointer');
		}
	},
	
	/**
	 * Inserts thumbnails.
	 * 
	 * @param	array<object>	images
	 */
	_createThumbnails: function(images) {
		if (this.options.staticViewer) {
			this._images = [ ];
			this._ui.imageList.empty();
		}
		
		for (var $i = 0, $length = images.length; $i < $length; $i++) {
			var $image = images[$i];
			
			var $listItem = $('<li class="loading pointer"><img src="' + $image.thumbnail.url + '" /></li>').appendTo(this._ui.imageList);
			$listItem.data('index', this._images.length).data('objectID', $image.objectID).click($.proxy(this._showImage, this));
			var $img = $listItem.children('img');
			if ($img.get(0).complete) {
				// thumbnail is read from cache
				$listItem.removeClass('loading');
				
				// fix dimensions
				if (this.options.staticViewer) {
					this._fixThumbnailDimensions($img);
				}
			}
			else {
				var self = this;
				$img.on('load', function() {
					var $img = $(this);
					$img.parent().removeClass('loading');
					
					if (self.options.staticViewer) {
						self._fixThumbnailDimensions($img);
					}
				});
			}
			
			$image.listItem = $listItem;
			this._images.push($image);
		}
	},
	
	/**
	 * Fixes thumbnail dimensions within static mode.
	 * 
	 * @param	jQuery		image
	 */
	_fixThumbnailDimensions: function(image) {
		var $image = new Image();
		$image.src = image.prop('src');
		
		var $height = $image.height;
		var $width = $image.width;
		
		// quadratic, scale to 80x80
		if ($height == $width) {
			$height = $width = 80;
		}
		else if ($height < $width) {
			// landscape, use width as reference
			var $scale = 80 / $width;
			$width = 80;
			$height *= $scale;
		}
		else {
			// portrait, use height as reference
			var $scale = 80 / $height;
			$height = 80;
			$width *= $scale;
		}
		
		image.css({
			height: $height + 'px',
			width: $width + 'px'
		});
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
	 * Builds the list of static images and returns it.
	 * 
	 * @return	array<object>
	 */
	_getStaticImages: function() {
		var $images = [ ];

		$(this.options.imageSelector).each((function(index, link) {
			// If the target image is inside a signature, then only include images within
			// the same signature. Otherwise this check will exclude images that are within
			// a user's signature.
			if (link.closest(".messageSignature") !== this._messageSignature) {
				return;
			}

			var $link = $(link);
			var $thumbnail = $link.find('> img, .attachmentThumbnailImage > img').first();
			if (!$thumbnail.length) {
				$thumbnail = $link.parentsUntil('.formAttachmentList').last().find('.attachmentTinyThumbnail');
			}

			let thumbnailSrc = '';
			if ($thumbnail.length === 0) {
				const attachmentItem = $link[0].closest(".attachment__item");
				if (attachmentItem !== null) {
					const file = attachmentItem.querySelector("woltlab-core-file");
					const thumbnail = file?.thumbnails.find((x) => x.identifier === "tiny");
					thumbnailSrc = thumbnail.link;
				}
			} else {
				thumbnailSrc = $thumbnail.prop("src");
			}

			
			$images.push({
				image: {
					fullURL: $thumbnail.data('source') ? $thumbnail.data('source').replace(/\\\//g, '/') : $link.prop('href'),
					link: '',
					title: $link.prop('title'),
					url: $link.prop('href')
				},
				series: null,
				thumbnail: {
					url: thumbnailSrc
				},
				user: null
			});
		}).bind(this));
		
		this._items = $images.length;
		
		return $images;
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
		
		if (!this._isOpen) {
			this._isOpen = true;
			
			WCF.System.DisableScrolling.disable();
			WCF.System.DisableZoom.disable();
		}
	}
});
