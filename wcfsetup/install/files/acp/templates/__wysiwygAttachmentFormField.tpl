<ul id="{$field->getPrefixedID()}_attachmentList" {*
	*}class="formAttachmentList jsObjectActionContainer" {*
	*}data-object-action-class-name="wcf\data\attachment\AttachmentAction"{*
	*}{if !$field->getAttachmentHandler()->getAttachmentList()|count} style="display: none"{/if}{*
*}>
	{foreach from=$field->getAttachmentHandler()->getAttachmentList() item=$attachment}
		<li class="box64 jsObjectActionObject" {*
			*}data-object-id="{@$attachment->getObjectID()}" {*
			*}data-height="{@$attachment->height}" {*
			*}data-width="{@$attachment->width}" {*
			*}data-is-image="{@$attachment->isImage}"{*
		*}>
			{if $attachment->tinyThumbnailType}
				<img src="{$attachment->getThumbnailLink('tiny')}" alt="" class="attachmentTinyThumbnail">
			{else}
				{icon size=64 name=$attachment->getIconName()}
			{/if}
			
			<div>
				<div>
					<p><a href="{$attachment->getLink()}" target="_blank"{if $attachment->isImage} title="{$attachment->filename}" class="jsImageViewer"{/if}>{$attachment->filename}</a></p>
					<small>{@$attachment->filesize|filesize}</small>
				</div>
				
				<ul class="buttonGroup">
					<li><button type="button" class="button small jsObjectAction" data-object-action="delete" data-confirm-message="{lang}wcf.attachment.delete.sure{/lang}">{lang}wcf.global.button.delete{/lang}</button></li>
					{if $attachment->isImage}
						{if $attachment->thumbnailType}
							<li><button type="button" class="button small jsButtonAttachmentInsertThumbnail" data-object-id="{@$attachment->attachmentID}" data-url="{$attachment->getThumbnailLink('thumbnail')}">{lang}wcf.attachment.insertThumbnail{/lang}</button></li>
						{/if}
						<li><button type="button" class="button small jsButtonAttachmentInsertFull" data-object-id="{@$attachment->attachmentID}" data-url="{$attachment->getLink()}">{lang}wcf.attachment.insertFull{/lang}</button></li>
					{else}
						<li><button type="button" class="button small jsButtonInsertAttachment" data-object-id="{@$attachment->attachmentID}">{lang}wcf.attachment.insert{/lang}</button></li>
					{/if}
				</ul>
			</div>
		</li>
	{/foreach}
</ul>
<div id="{$field->getPrefixedID()}_uploadButton" class="formAttachmentButtons" data-max-size="{@$field->getAttachmentHandler()->getMaxSize()}"></div>

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.attachment.upload.error.invalidExtension': '{jslang}wcf.attachment.upload.error.invalidExtension{/jslang}',
			'wcf.attachment.upload.error.tooLarge': '{jslang}wcf.attachment.upload.error.tooLarge{/jslang}',
			'wcf.attachment.upload.error.reachedLimit': '{jslang}wcf.attachment.upload.error.reachedLimit{/jslang}',
			'wcf.attachment.upload.error.reachedRemainingLimit': '{jslang}wcf.attachment.upload.error.reachedRemainingLimit{/jslang}',
			'wcf.attachment.upload.error.uploadFailed': '{jslang}wcf.attachment.upload.error.uploadFailed{/jslang}',
			'wcf.attachment.upload.error.http413': '{jslang}wcf.attachment.upload.error.http413{/jslang}',
			'wcf.attachment.upload.error.uploadPhpLimit': '{jslang}wcf.attachment.upload.error.uploadPhpLimit{/jslang}',
			'wcf.attachment.insert': '{jslang}wcf.attachment.insert{/jslang}',
			'wcf.attachment.insertAll': '{jslang}wcf.attachment.insertAll{/jslang}',
			'wcf.attachment.insertFull': '{jslang}wcf.attachment.insertFull{/jslang}',
			'wcf.attachment.insertThumbnail': '{jslang}wcf.attachment.insertThumbnail{/jslang}',
			'wcf.attachment.delete.sure': '{jslang}wcf.attachment.delete.sure{/jslang}'
		});
		
		new WCF.Attachment.Upload(
			$('#{@$field->getPrefixedID()|encodeJS}_uploadButton'),
			$('#{@$field->getPrefixedID()|encodeJS}_attachmentList'),
			'{@$field->getAttachmentHandler()->getObjectType()->objectType}',
			'{@$field->getAttachmentHandler()->getObjectID()}',
			'{$field->getAttachmentHandler()->getTmpHashes()[0]|encodeJS}',
			'{@$field->getAttachmentHandler()->getParentObjectID()}',
			{@$field->getAttachmentHandler()->getMaxCount()},
			'{@$field->getPrefixedWysiwygId()}'
		);
	});
</script>

<input type="hidden" id="{$field->getPrefixedID()}_tmpHash" name="{$field->getPrefixedID()}_tmpHash" value="{$field->getAttachmentHandler()->getTmpHashes()[0]}">
