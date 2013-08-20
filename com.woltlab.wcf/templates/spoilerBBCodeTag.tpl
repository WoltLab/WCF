<div class="container containerPadding spoilerBox jsSpoilerBox">
	<!-- begin:parser_nonessential -->
	<header class="jsOnly">
		<a class="button jsSpoilerToggle">{if $buttonTitle}{$buttonTitle}{else}{lang}wcf.bbcode.spoiler.show{/lang}{/if}</a>
	</header>
	<!-- end:parser_nonessential -->
	
	<div style="display: none">
		{@$content}
	</div>
</div>

<!-- begin:parser_nonessential -->
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		var $spoilerBox = $('.jsSpoilerBox').removeClass('jsSpoilerBox');
		$spoilerBox.find('> header > .jsSpoilerToggle').click(function() {
			$(this).toggleClass('active').parent().next().slideToggle();
		});
	});
	//]]>
</script>
<!-- end:parser_nonessential -->