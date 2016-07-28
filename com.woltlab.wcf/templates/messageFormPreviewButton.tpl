{if !$previewMessageFieldID|isset}{assign var=previewMessageFieldID value='text'}{/if}
{if !$previewButtonID|isset}{assign var=previewButtonID value='buttonMessagePreview'}{/if}
{if !$previewMessageObjectType|isset}{assign var=previewMessageObjectType value=''}{/if}
{if !$previewMessageObjectID|isset}{assign var=previewMessageObjectID value=0}{/if}

<button id="{$previewButtonID}" class="jsOnly">{lang}wcf.global.button.preview{/lang}</button>

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.global.preview': '{lang}wcf.global.preview{/lang}' 
		});
		
		new WCF.Message.DefaultPreview({
			messageFieldID: '{$previewMessageFieldID}',
			previewButtonID: '{$previewButtonID}',
			messageObjectType: '{$previewMessageObjectType}',
			messageObjectID: '{$previewMessageObjectID}'
		});
	});
	//]]>
</script>