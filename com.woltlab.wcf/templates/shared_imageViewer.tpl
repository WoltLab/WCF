{if !$__imageViewerLoaded|isset}
	<script data-eager="true">
		{jsphrase name='wcf.fancybox.imageError'}
		{jsphrase name='wcf.fancybox.moveUp'}
		{jsphrase name='wcf.fancybox.moveDown'}
		{jsphrase name='wcf.fancybox.moveLeft'}
		{jsphrase name='wcf.fancybox.moveRight'}
		{jsphrase name='wcf.fancybox.zoomIn'}
		{jsphrase name='wcf.fancybox.zoomOut'}
		{jsphrase name='wcf.fancybox.toggleFull'}
		{jsphrase name='wcf.fancybox.toggle1to1'}
		{jsphrase name='wcf.fancybox.iterateZoom'}
		{jsphrase name='wcf.fancybox.rotateCcw'}
		{jsphrase name='wcf.fancybox.rotateCw'}
		{jsphrase name='wcf.fancybox.flipX'}
		{jsphrase name='wcf.fancybox.flipY'}
		{jsphrase name='wcf.fancybox.reset'}
		{jsphrase name='wcf.fancybox.error'}
		{jsphrase name='wcf.fancybox.next'}
		{jsphrase name='wcf.fancybox.prev'}
		{jsphrase name='wcf.fancybox.goto'}
		{jsphrase name='wcf.fancybox.download'}
		{jsphrase name='wcf.fancybox.toggleFullscreen'}
		{jsphrase name='wcf.fancybox.toggleExpand'}
		{jsphrase name='wcf.fancybox.toggleThumbs'}
		{jsphrase name='wcf.fancybox.toggleAutoplay'}
		{jsphrase name='wcf.fancybox.close'}
		{jsphrase name='wcf.fancybox.next'}
		{jsphrase name='wcf.fancybox.prev'}
		{jsphrase name='wcf.fancybox.modal'}
		{jsphrase name='wcf.fancybox.elementNotFound'}
		{jsphrase name='wcf.fancybox.iframeError'}

	  {
		let stylesheet = document.getElementById("fancybox-stylesheet");
		if (stylesheet === null) {
		  stylesheet = document.createElement("link");
		  stylesheet.rel = "stylesheet";
		  stylesheet.type = "text/css";
		  stylesheet.href = "{$__wcf->getPath()}style/fancybox.css";
		  stylesheet.id = "fancybox-stylesheet";

		  document.querySelector('link[rel="stylesheet"]').before(stylesheet);
		}
	  }
	</script>
	{if MESSAGE_ENABLE_USER_CONSENT}
		<template id="consentImageViewer" data-show-all-media="{if $__wcf->getUser()->userID && $__wcf->getUser()->getUserOption('enableEmbeddedMedia')}1{else}0{/if}">
			{include file="messageUserConsent" target='' host='' url='' sandbox=true}
		</template>
	{/if}
	
	{assign var=__imageViewerLoaded value=true}
{/if}
