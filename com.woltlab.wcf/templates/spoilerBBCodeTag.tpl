<!-- begin:parser_nonessential -->
<div class="container containerPadding spoilerBox jsSpoilerBox">
	<header class="jsOnly">
		<a class="button jsSpoilerToggle">{if $buttonTitle}{$buttonTitle}{else}{lang}wcf.bbcode.spoiler.show{/lang}{/if}</a>
	</header>
	
	<div style="display: none">
		{@$content}
	</div>
</div>

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		var $spoilerBox = $('.jsSpoilerBox').removeClass('jsSpoilerBox');
		$spoilerBox.find('> header > .jsSpoilerToggle').click(function() {
			$(this).toggleClass('active').parent().next().slideToggle({
				complete: function() {
					if ($(this).is(':visible')) {
						WCF.DOMNodeInsertedHandler.execute();
					}
				}
			});
		});
	});
	//]]>
</script>
<!-- end:parser_nonessential -->