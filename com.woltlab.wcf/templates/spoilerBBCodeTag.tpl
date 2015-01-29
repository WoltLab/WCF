<!-- begin:parser_nonessential -->
<div class="container containerPadding spoilerBox jsSpoilerBox">
	<header class="jsOnly">
		<a class="button jsSpoilerToggle"{if $buttonTitle} data-button-title="{$buttonTitle}"{/if}>{if $buttonTitle}{@$buttonTitle}{else}{lang}wcf.bbcode.spoiler.show{/lang}{/if}</a>
	</header>
	
	<div style="display: none">
		{@$content}
	</div>
</div>

{if !$__wcfSpoilerBBCodeJavaScript|isset}
	{assign var='__wcfSpoilerBBCodeJavaScript' value=true}
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			$('.jsSpoilerBox').each(function(index, spoilerBox) {
				var $toggle = $(spoilerBox).removeClass('jsSpoilerBox').find('> header > .jsSpoilerToggle');
				$toggle.click(function() {
					$toggle.toggleClass('active').parent().next().slideToggle({
						complete: function() {
							var $container = $(this);
							if ($container.is(':visible')) {
								WCF.DOMNodeInsertedHandler.execute();
							}
							
							if (!$toggle.data('buttonTitle')) {
								if ($container.is(':visible')) {
									$toggle.text('{lang}wcf.bbcode.spoiler.hide{/lang}');
								}
								else {
									$toggle.text('{lang}wcf.bbcode.spoiler.show{/lang}');
								}
							}
						}
					});
				});
			});
		});
		//]]>
	</script>
{/if}
<!-- end:parser_nonessential -->
