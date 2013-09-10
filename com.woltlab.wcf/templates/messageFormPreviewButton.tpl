<button id="previewButton" class="jsOnly" accesskey="p">{lang}wcf.global.button.preview{/lang}</button>

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.global.preview': '{lang}wcf.global.preview{/lang}' 
		});
		
		new WCF.Message.DefaultPreview({if MODULE_ATTACHMENT && $attachmentHandler !== null}'{@$attachmentObjectType}', '{@$attachmentObjectID}', '{$tmpHash|encodeJS}'{/if});
	});
	//]]>
</script>