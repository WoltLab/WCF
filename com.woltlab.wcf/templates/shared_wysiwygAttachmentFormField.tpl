<div class="messageTabMenuContent" id="attachments_{$field->getPrefixedWysiwygId()}">
	<div class="fileDropZone">
		{icon size=32 name='cloud-arrow-up'}
		<span>
			{lang}wcf.upload.dropZone{/lang}
			<button type="button" class="fileDropZone__select">{lang}wcf.upload.dropZone.select{/lang}</button>
		</span>
		<div class="fileDropZone__limits">{lang attachmentHandler=$field->getAttachmentHandler()}wcf.attachment.upload.limits{/lang}</div>
	</div>
	{unsafe:$field->getAttachmentHandler()->getHtmlElement()}

	<div class="attachment__list__existingFiles">
		{foreach from=$field->getAttachmentHandler()->getAttachmentList() item=attachment}
			{unsafe:$attachment->toHtmlElement()}
		{/foreach}
	</div>

	{foreach from=$field->getAttachmentHandler()->getTmpHashes() item=tmpHash}
		<input type="hidden" name="{$field->getPrefixedID()}_tmpHash[]" value="{$tmpHash}">
	{/foreach}

	<script data-relocate="true">
		{jsphrase name='wcf.attachment.insert'}
		{jsphrase name='wcf.attachment.inserted'}
		{jsphrase name='wcf.attachment.insertFull'}
		{jsphrase name='wcf.attachment.moreOptions'}

		require(["WoltLabSuite/Core/Component/Attachment/List"], ({ setup }) => {
			setup("{unsafe:$field->getPrefixedWysiwygId()|encodeJS}");
		});
	</script>
</div>
