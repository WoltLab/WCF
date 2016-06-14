{if $attachmentList && $attachmentList->getGroupedObjects($objectID)|count}
	{hascontent}
		<section class="section attachmentThumbnailList">
			<h2 class="sectionTitle">{lang}wcf.attachment.images{/lang}</h2>
			
			<ul class="inlineList">
				{content}
					{foreach from=$attachmentList->getGroupedObjects($objectID) item=attachment}
						{if $attachment->showAsImage() && !$attachment->isEmbedded()}
							<li class="attachmentThumbnail" data-attachment-id="{@$attachment->attachmentID}">
								{if $attachment->hasThumbnail()}
									<a href="{link controller='Attachment' object=$attachment}{/link}"{if $attachment->canDownload()} class="jsImageViewer" title="{$attachment->filename}"{/if}>
								{/if}
								
								<div class="attachmentThumbnailContainer">		
									<span class="attachmentThumbnailImage">
										{if $attachment->hasThumbnail()}
											<img src="{link controller='Attachment' object=$attachment}thumbnail=1{/link}" alt="" {if $attachment->thumbnailWidth >= ATTACHMENT_THUMBNAIL_WIDTH && $attachment->thumbnailHeight >= ATTACHMENT_THUMBNAIL_HEIGHT} class="attachmentThumbnailImageScalable"{/if}>
										{else}
											<img src="{link controller='Attachment' object=$attachment}{/link}" alt="" {if $attachment->width >= ATTACHMENT_THUMBNAIL_WIDTH && $attachment->height >= ATTACHMENT_THUMBNAIL_HEIGHT} class="attachmentThumbnailImageScalable"{/if}>
										{/if}
									</span>
								
									<span class="attachmentThumbnailData">
										<span class="attachmentFilename">{$attachment->filename}</span>
									</span>
								</div>
										
								<ul class="attachmentMetaData inlineList">
									<li>
										<span class="icon icon16 fa-file-text-o"></span>
										{@$attachment->filesize|filesize}
									</li>
									<li>
										<span class="icon icon16 fa-expand"></span>
										{#$attachment->width}×{#$attachment->height}
									</li>
									<li>
										<span class="icon icon16 fa-eye"></span>
										{#$attachment->downloads}
									</li>
								</ul>		
										
								{if $attachment->hasThumbnail()}
									</a>
								{/if}
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
				
			<ul class="inlineList">
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
