<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ImageViewer.class.js"></script>
<script type="text/javascript">
	//<![CDATA[			
	// when the dom is fully loaded, add image viewer
	document.observe("dom:loaded", function() {
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
			imgPlaySrc		: '{@RELATIVE_WCF_DIR}icon/imageViewer/playM.png',
			imgPreviousSrc		: '{@RELATIVE_WCF_DIR}icon/imageViewer/previousM.png',
			imgNextSrc		: '{@RELATIVE_WCF_DIR}icon/imageViewer/nextM.png',
			imgEnlargeSrc		: '{@RELATIVE_WCF_DIR}icon/imageViewer/enlargeM.png',
			imgPauseSrc		: '{@RELATIVE_WCF_DIR}icon/imageViewer/pauseM.png',
			imgCloseSrc		: '{@RELATIVE_WCF_DIR}icon/imageViewer/closeM.png',
			imgPlayHoverSrc		: '{@RELATIVE_WCF_DIR}icon/imageViewer/playHoverM.png',
			imgPreviousHoverSrc	: '{@RELATIVE_WCF_DIR}icon/imageViewer/previousHoverM.png',
			imgNextHoverSrc		: '{@RELATIVE_WCF_DIR}icon/imageViewer/nextHoverM.png',
			imgEnlargeHoverSrc	: '{@RELATIVE_WCF_DIR}icon/imageViewer/enlargeHoverM.png',
			imgPauseHoverSrc	: '{@RELATIVE_WCF_DIR}icon/imageViewer/pauseHoverM.png',
			imgCloseHoverSrc	: '{@RELATIVE_WCF_DIR}icon/imageViewer/closeHoverM.png'	
		});
	});
//]]>
</script>