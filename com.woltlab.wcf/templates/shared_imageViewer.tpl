{if !$__imageViewerLoaded|isset}
	<script data-eager="true">
		{jsphrase name='wcf.fancybox.image_error'}
		{jsphrase name='wcf.fancybox.move_up'}
		{jsphrase name='wcf.fancybox.move_down'}
		{jsphrase name='wcf.fancybox.move_left'}
		{jsphrase name='wcf.fancybox.move_right'}
		{jsphrase name='wcf.fancybox.zoom_in'}
		{jsphrase name='wcf.fancybox.zoom_out'}
		{jsphrase name='wcf.fancybox.toggle_full'}
		{jsphrase name='wcf.fancybox.toggle_1to1'}
		{jsphrase name='wcf.fancybox.iterate_zoom'}
		{jsphrase name='wcf.fancybox.rotate_ccw'}
		{jsphrase name='wcf.fancybox.rotate_cw'}
		{jsphrase name='wcf.fancybox.flip_x'}
		{jsphrase name='wcf.fancybox.flip_y'}
		{jsphrase name='wcf.fancybox.reset'}
		{jsphrase name='wcf.fancybox.error'}
		{jsphrase name='wcf.fancybox.next'}
		{jsphrase name='wcf.fancybox.prev'}
		{jsphrase name='wcf.fancybox.goto'}
		{jsphrase name='wcf.fancybox.download'}
		{jsphrase name='wcf.fancybox.toggle_fullscreen'}
		{jsphrase name='wcf.fancybox.toggle_expand'}
		{jsphrase name='wcf.fancybox.toggle_thumbs'}
		{jsphrase name='wcf.fancybox.toggle_autoplay'}
		{jsphrase name='wcf.fancybox.close'}
		{jsphrase name='wcf.fancybox.next'}
		{jsphrase name='wcf.fancybox.prev'}
		{jsphrase name='wcf.fancybox.modal'}
		{jsphrase name='wcf.fancybox.element_not_found'}
		{jsphrase name='wcf.fancybox.iframe_error'}

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
	
	{assign var=__imageViewerLoaded value=true}
{/if}
