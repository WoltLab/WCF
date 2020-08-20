<div class="spoilerBox jsSpoilerBox">
	<div class="jsOnly spoilerBoxHeader">
		<a href="#" id="spoiler-button-{@$spoilerID}" role="button" aria-expanded="false" aria-controls="spoiler-content-{@$spoilerID}" class="button small jsSpoilerToggle"{if $buttonLabel} data-has-custom-label="true"{/if}>{if $buttonLabel}{$buttonLabel}{else}{lang}wcf.bbcode.spoiler.show{/lang}{/if}</a>
	</div>
	
	<div class="spoilerBoxContent" style="display: none" id="spoiler-content-{@$spoilerID}" aria-hidden="true" aria-labelledby="spoiler-button-{@$spoilerID}">
		<!-- META_CODE_INNER_CONTENT -->
	</div>
</div>
<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Bbcode/Spoiler'], function (Language, BbcodeSpoiler) {
		Language.addObject({
			'wcf.bbcode.spoiler.hide' : '{lang}wcf.bbcode.spoiler.hide{/lang}',
			'wcf.bbcode.spoiler.show' : '{lang}wcf.bbcode.spoiler.show{/lang}'
		});
		
		BbcodeSpoiler.observe();
	});
</script>
