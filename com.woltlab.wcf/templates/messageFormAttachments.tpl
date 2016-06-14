<div class="jsOnly formAttachmentContent messageTabMenuContent" id="attachments_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}">
	<ul class="formAttachmentList clearfix"{if !$attachmentHandler->getAttachmentList()|count} style="display: none"{/if}>
		{foreach from=$attachmentHandler->getAttachmentList() item=$attachment}
			<li class="box64" data-object-id="{@$attachment->attachmentID}" data-height="{@$attachment->height}" data-width="{@$attachment->width}">
				{if $attachment->tinyThumbnailType}
					<img src="{link controller='Attachment' object=$attachment}tiny=1{/link}" alt="" class="attachmentTinyThumbnail">
				{else}
					<span class="icon icon48 fa-paperclip"></span>
				{/if}
				
				<div>
					<div>
						<p><a href="{link controller='Attachment' object=$attachment}{/link}"{if $attachment->isImage} title="{$attachment->filename}" class="jsImageViewer"{/if}>{$attachment->filename}</a></p>
						<small>{@$attachment->filesize|filesize}</small>
					</div>
					
					<ul class="buttonGroup">
						<li><span class="button small jsDeleteButton" data-object-id="{@$attachment->attachmentID}" data-confirm-message="{lang}wcf.attachment.delete.sure{/lang}">{lang}wcf.global.button.delete{/lang}</span></li>
						{if $attachment->isImage}
							<li><span class="button small jsButtonAttachmentInsertThumbnail" data-object-id="{@$attachment->attachmentID}">{lang}wcf.attachment.insertThumbnail{/lang}</span></li>
							<li><span class="button small jsButtonAttachmentInsertFull" data-object-id="{@$attachment->attachmentID}">{lang}wcf.attachment.insertFull{/lang}</span></li>
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
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.attachment.upload.error.invalidExtension': '{lang}wcf.attachment.upload.error.invalidExtension{/lang}',
			'wcf.attachment.upload.error.tooLarge': '{lang}wcf.attachment.upload.error.tooLarge{/lang}',
			'wcf.attachment.upload.error.reachedLimit': '{lang}wcf.attachment.upload.error.reachedLimit{/lang}',
			'wcf.attachment.upload.error.reachedRemainingLimit': '{lang}wcf.attachment.upload.error.reachedRemainingLimit{/lang}',
			'wcf.attachment.upload.error.uploadFailed': '{lang}wcf.attachment.upload.error.uploadFailed{/lang}',
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
			'{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}'
		);
		new WCF.Action.Delete('wcf\\data\\attachment\\AttachmentAction', '.formAttachmentList > li');
	});
	//]]>
</script>

<input type="hidden" name="tmpHash" value="{$tmpHash}">