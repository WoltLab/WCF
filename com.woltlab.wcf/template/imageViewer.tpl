<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ImageViewer.class.js"></script>
<script type="text/javascript">
	//<![CDATA[			
	// when the window is fully loaded, add image viewer
	Event.observe(window, 'load', function() {
		new ImageViewer($$('.enlargable'), {
			langCaption		: '{lang}wcf.imageViewer.caption{/lang}',
			langPrevious		: '{lang}wcf.imageViewer.previous{/lang}',
			langNext		: '{lang}wcf.imageViewer.next{/lang}',
			langPlay		: '{lang}wcf.imageViewer.play{/lang}',
			langPause		: '{lang}wcf.imageViewer.pause{/lang}',
			langEnlarge		: '{lang}wcf.imageViewer.enlarge{/lang}',
			langClose		: '{lang}wcf.imageViewer.close{/lang}',
			imgBlankSrc		: '{@RELATIVE_WCF_DIR}images/imageViewer/blank.png',
			imgMenuSrc		: '{@RELATIVE_WCF_DIR}images/imageViewer/menu.png',
			imgPlaySrc		: '{icon}imageViewer/playM.png{/icon}',
			imgPreviousSrc		: '{icon}imageViewer/previousM.png{/icon}',
			imgNextSrc		: '{icon}imageViewer/nextM.png{/icon}',
			imgEnlargeSrc		: '{icon}imageViewer/enlargeM.png{/icon}',
			imgPauseSrc		: '{icon}imageViewer/pauseM.png{/icon}',
			imgCloseSrc		: '{icon}imageViewer/closeM.png{/icon}',
			imgPlayHoverSrc		: '{icon}imageViewer/playHoverM.png{/icon}',
			imgPreviousHoverSrc	: '{icon}imageViewer/previousHoverM.png{/icon}',
			imgNextHoverSrc		: '{icon}imageViewer/nextHoverM.png{/icon}',
			imgEnlargeHoverSrc	: '{icon}imageViewer/enlargeHoverM.png{/icon}',
			imgPauseHoverSrc	: '{icon}imageViewer/pauseHoverM.png{/icon}',
			imgCloseHoverSrc	: '{icon}imageViewer/closeHoverM.png{/icon}'	
		});
	});
//]]>
</script>