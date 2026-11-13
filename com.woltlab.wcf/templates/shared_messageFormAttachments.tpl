<div class="messageTabMenuContent" id="attachments_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}">
	<div class="fileDropZone">
		{icon size=32 name='cloud-arrow-up'}
		<span>
			{lang}wcf.upload.dropZone{/lang}
			<button type="button" class="fileDropZone__select">{lang}wcf.upload.dropZone.select{/lang}</button>
		</span>
		<div class="fileDropZone__limits">{lang}wcf.attachment.upload.limits{/lang}</div>
	</div>
	{unsafe:$attachmentHandler->getHtmlElement()}

	<div class="attachment__list__existingFiles">
		{foreach from=$attachmentHandler->getAttachmentList() item=attachment}
			{unsafe:$attachment->toHtmlElement()}
		{/foreach}
	</div>

	<input type="hidden" name="tmpHash" value="{$tmpHash}">

	<script data-relocate="true">
		{jsphrase name='wcf.attachment.insert'}
		{jsphrase name='wcf.attachment.inserted'}
		{jsphrase name='wcf.attachment.insertFull'}
		{jsphrase name='wcf.attachment.moreOptions'}

		require(["WoltLabSuite/Core/Component/Attachment/List"], ({ setup }) => {
			setup("{if $wysiwygSelector|isset}{unsafe:$wysiwygSelector|encodeJS}{else}text{/if}");
		});
	</script>
	
	{event name='fields'}
</div>
