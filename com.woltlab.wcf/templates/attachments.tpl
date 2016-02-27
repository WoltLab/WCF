{if $attachmentList && $attachmentList->getGroupedObjects($objectID)|count}
	{hascontent}
		<section class="section attachmentThumbnailList">
			<h2 class="sectionTitle">{lang}wcf.attachment.images{/lang}</h2>
			
			<ul>
				{content}
					{foreach from=$attachmentList->getGroupedObjects($objectID) item=attachment}
						{if $attachment->showAsImage() && !$attachment->isEmbedded()}
							<li class="attachmentThumbnail" data-attachment-id="{@$attachment->attachmentID}">
								{if $attachment->hasThumbnail()}
									<a href="{link controller='Attachment' object=$attachment}{/link}"{if $attachment->canDownload()} class="jsImageViewer" title="{$attachment->filename}"{/if}><img src="{link controller='Attachment' object=$attachment}thumbnail=1{/link}" alt="" style="{if $attachment->thumbnailHeight < ATTACHMENT_THUMBNAIL_HEIGHT}margin-top: {@ATTACHMENT_THUMBNAIL_HEIGHT/2-$attachment->thumbnailHeight/2}px; {/if}{if $attachment->thumbnailWidth < ATTACHMENT_THUMBNAIL_WIDTH}margin-left: {@ATTACHMENT_THUMBNAIL_WIDTH/2-$attachment->thumbnailWidth/2}px{/if}" /></a>
								{else}
									<img src="{link controller='Attachment' object=$attachment}{/link}" alt="" style="margin-top: {@ATTACHMENT_THUMBNAIL_HEIGHT/2-$attachment->height/2}px; margin-left: {@ATTACHMENT_THUMBNAIL_WIDTH/2-$attachment->width/2}px" />
								{/if}
								
								<div title="{lang}wcf.attachment.image.info{/lang}">
									<p>{$attachment->filename}</p>
									<small>{lang}wcf.attachment.image.info{/lang}</small>
								</div>
							</li>
						{/if}
					{/foreach}
				{/content}
			</ul>
		</section>
	{/hascontent}
	
	{hascontent}
		<section class="section attachmentFileList">
			<h2 class="sectionTitle">{lang}wcf.attachment.files{/lang}</h2>
				
			<ul>
				{content}
					{foreach from=$attachmentList->getGroupedObjects($objectID) item=attachment}
						{if $attachment->showAsFile() && !$attachment->isEmbedded()}
							<li class="box32" data-attachment-id="{@$attachment->attachmentID}">
								<a href="{link controller='Attachment' object=$attachment}{/link}"><span class="icon icon32 fa-paperclip"></span></a>
								
								<div>
									<p><a href="{link controller='Attachment' object=$attachment}{/link}">{$attachment->filename}</a></p>
									<small>{lang}wcf.attachment.file.info{/lang}</small>
								</div>
							</li>
						{/if}
					{/foreach}
				{/content}
			</ul>
		</section>
	{/hascontent}
{/if}
