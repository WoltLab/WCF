<ul id="{@$field->getPrefixedID()}_attachmentList" {*
	*}class="formAttachmentList"{*
	*}{if !$field->getAttachmentHandler()->getAttachmentList()|count} style="display: none"{/if}{*
*}>
	{foreach from=$field->getAttachmentHandler()->getAttachmentList() item=$attachment}
		<li class="box64" {*
			*}data-object-id="{@$attachment->attachmentID}" {*
			*}data-height="{@$attachment->height}" {*
			*}data-width="{@$attachment->width}" {*
			*}data-is-image="{@$attachment->isImage}"{*
		*}>
			{if $attachment->tinyThumbnailType}
				<img src="{$attachment->getThumbnailLink('tiny')}" alt="" class="attachmentTinyThumbnail">
			{else}
				<span class="icon icon64 fa-{@$attachment->getIconName()}"></span>
			{/if}
			
			<div>
				<div>
					<p><a href="{$attachment->getLink()}" target="_blank"{if $attachment->isImage} title="{$attachment->filename}" class="jsImageViewer"{/if}>{$attachment->filename}</a></p>
					<small>{@$attachment->filesize|filesize}</small>
				</div>
				
				<ul class="buttonGroup">
					<li><span class="button small jsDeleteButton" data-object-id="{@$attachment->attachmentID}" data-confirm-message="{lang}wcf.attachment.delete.sure{/lang}">{lang}wcf.global.button.delete{/lang}</span></li>
					{if $attachment->isImage}
						{if $attachment->thumbnailType}
							<li><span class="button small jsButtonAttachmentInsertThumbnail" data-object-id="{@$attachment->attachmentID}" data-url="{$attachment->getThumbnailLink('thumbnail')}">{lang}wcf.attachment.insertThumbnail{/lang}</span></li>
						{/if}
						<li><span class="button small jsButtonAttachmentInsertFull" data-object-id="{@$attachment->attachmentID}" data-url="{$attachment->getLink()}">{lang}wcf.attachment.insertFull{/lang}</span></li>
					{else}
						<li><span class="button small jsButtonInsertAttachment" data-object-id="{@$attachment->attachmentID}">{lang}wcf.attachment.insert{/lang}</span></li>
					{/if}
				</ul>
			</div>
		</li>
	{/foreach}
</ul>
<div id="{@$field->getPrefixedID()}_uploadButton" class="formAttachmentButtons" data-max-size="{@$field->getAttachmentHandler()->getMaxSize()}"></div>

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.attachment.upload.error.invalidExtension': '{lang}wcf.attachment.upload.error.invalidExtension{/lang}',
			'wcf.attachment.upload.error.tooLarge': '{lang}wcf.attachment.upload.error.tooLarge{/lang}',
			'wcf.attachment.upload.error.reachedLimit': '{lang}wcf.attachment.upload.error.reachedLimit{/lang}',
			'wcf.attachment.upload.error.reachedRemainingLimit': '{lang}wcf.attachment.upload.error.reachedRemainingLimit{/lang}',
			'wcf.attachment.upload.error.uploadFailed': '{lang}wcf.attachment.upload.error.uploadFailed{/lang}',
			'wcf.attachment.upload.error.uploadPhpLimit': '{lang}wcf.attachment.upload.error.uploadPhpLimit{/lang}',
			'wcf.attachment.insert': '{lang}wcf.attachment.insert{/lang}',
			'wcf.attachment.insertAll': '{lang}wcf.attachment.insertAll{/lang}',
			'wcf.attachment.insertFull': '{lang}wcf.attachment.insertFull{/lang}',
			'wcf.attachment.insertThumbnail': '{lang}wcf.attachment.insertThumbnail{/lang}',
			'wcf.attachment.delete.sure': '{lang}wcf.attachment.delete.sure{/lang}'
		});
		
		new WCF.Attachment.Upload(
			$('#{@$field->getPrefixedID()}_uploadButton'),
			$('#{@$field->getPrefixedID()}_attachmentList'),
			'{@$field->getAttachmentHandler()->getObjectType()->objectType}',
			'{@$field->getAttachmentHandler()->getObjectID()}',
			'{$field->getAttachmentHandler()->getTmpHashes()[0]|encodeJS}',
			'{@$field->getAttachmentHandler()->getParentObjectID()}',
			{@$field->getAttachmentHandler()->getMaxCount()},
			'{@$field->getPrefixedWysiwygId()}'
		);
		new WCF.Action.Delete('wcf\\data\\attachment\\AttachmentAction', '.formAttachmentList > li');
	});
</script>

<input type="hidden" id="{@$field->getPrefixedID()}_tmpHash" name="{@$field->getPrefixedID()}_tmpHash" value="{$field->getAttachmentHandler()->getTmpHashes()[0]}">
