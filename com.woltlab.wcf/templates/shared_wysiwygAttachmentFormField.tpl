<div class="messageTabMenuContent" id="attachments_{$field->getPrefixedWysiwygId()}">
	{unsafe:$field->getAttachmentHandler()->getHtmlElement()}

	<div class="attachment__list__existingFiles">
		{foreach from=$field->getAttachmentHandler()->getAttachmentList() item=attachment}
			{unsafe:$attachment->toHtmlElement()}
		{/foreach}
	</div>

	<dl class="wide">
		<dt></dt>
		<dd>
			<div data-max-size="{$field->getAttachmentHandler()->getMaxSize()}"></div>
			<small>{lang attachmentHandler=$field->getAttachmentHandler()}wcf.attachment.upload.limits{/lang}</small>
		</dd>
	</dl>

	{foreach from=$field->getAttachmentHandler()->getTmpHashes() item=tmpHash}
		<input type="hidden" name="{$field->getPrefixedID()}_tmpHash[]" value="{$tmpHash}">
	{/foreach}

	<script data-relocate="true">
		{jsphrase name='wcf.attachment.insert'}
		{jsphrase name='wcf.attachment.insertFull'}
		{jsphrase name='wcf.attachment.moreOptions'}

		require(["WoltLabSuite/Core/Component/Attachment/List"], ({ setup }) => {
			setup("{$field->getPrefixedWysiwygId()}");
		});
	</script>
</div>
