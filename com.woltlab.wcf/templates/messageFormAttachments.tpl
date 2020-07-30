<div class="jsOnly formAttachmentContent messageTabMenuContent" id="attachments_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}">
	<ul class="formAttachmentList clearfix"{if !$attachmentHandler->getAttachmentList()|count} style="display: none"{/if}>
		{foreach from=$attachmentHandler->getAttachmentList() item=$attachment}
			<li class="box64" data-object-id="{@$attachment->attachmentID}" data-height="{@$attachment->height}" data-width="{@$attachment->width}" data-is-image="{@$attachment->isImage}">
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
							{if $attachment->thumbnailType}<li><span class="button small jsButtonAttachmentInsertThumbnail" data-object-id="{@$attachment->attachmentID}" data-url="{$attachment->getThumbnailLink('thumbnail')}">{lang}wcf.attachment.insertThumbnail{/lang}</span></li>{/if}
							<li><span class="button small jsButtonAttachmentInsertFull" data-object-id="{@$attachment->attachmentID}" data-url="{$attachment->getLink()}">{lang}wcf.attachment.insertFull{/lang}</span></li>
						{else}
							<li><span class="button small jsButtonInsertAttachment" data-object-id="{@$attachment->attachmentID}">{lang}wcf.attachment.insert{/lang}</span></li>
						{/if}
					</ul>
				</div>
			</li>
		{/foreach}
	</ul>
	
	<dl class="wide">
		<dt></dt>
		<dd>
			<div data-max-size="{@$attachmentHandler->getMaxSize()}"></div>
			<small>{lang}wcf.attachment.upload.limits{/lang}</small>
		</dd>
	</dl>
	
	{event name='fields'}
</div>

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
			$('#attachments_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if} > dl > dd > div'),
			$('#attachments_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if} > ul'),
			'{@$attachmentObjectType}',
			'{@$attachmentObjectID}',
			'{$tmpHash|encodeJS}',
			'{@$attachmentParentObjectID}',
			{@$attachmentHandler->getMaxCount()},
			'{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}',
			{
				autoScale: {
					enable: {if ATTACHMENT_IMAGE_AUTOSCALE}true{else}false{/if},
					maxWidth: {ATTACHMENT_IMAGE_AUTOSCALE_MAX_WIDTH},
					maxHeight: {ATTACHMENT_IMAGE_AUTOSCALE_MAX_HEIGHT},
					fileType: '{ATTACHMENT_IMAGE_AUTOSCALE_FILE_TYPE}',
					quality: {ATTACHMENT_IMAGE_AUTOSCALE_QUALITY / 100}
				}
			}
		);
		new WCF.Action.Delete('wcf\\data\\attachment\\AttachmentAction', '.formAttachmentList > li');
	});
</script>

<input type="hidden" name="tmpHash" value="{$tmpHash}">
