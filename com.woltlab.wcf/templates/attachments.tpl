{if $attachmentList && $attachmentList->getGroupedObjects($objectID)|count}
	{hascontent}
		<section class="section attachmentThumbnailList">
			<h2 class="messageSectionTitle">{lang}wcf.attachment.images{/lang}</h2>
			
			<ul class="inlineList">
				{content}
					{foreach from=$attachmentList->getGroupedObjects($objectID) item=attachment}
						{if $attachment->showAsImage() && !$attachment->isEmbedded()}
							<li class="attachmentThumbnail" data-attachment-id="{@$attachment->attachmentID}">
								{if $attachment->hasThumbnail()}
									<a href="{$attachment->getLink()}"{if $attachment->canDownload()} class="jsImageViewer jsTooltip" title="{lang}wcf.attachment.image.title{/lang}"{/if}>
								{/if}
								
								<div class="attachmentThumbnailContainer">
									<span class="attachmentThumbnailImage">
										{if $attachment->hasThumbnail()}
											<img src="{$attachment->getThumbnailLink('thumbnail')}" alt="" {if $attachment->thumbnailWidth >= ATTACHMENT_THUMBNAIL_WIDTH && $attachment->thumbnailHeight >= ATTACHMENT_THUMBNAIL_HEIGHT} class="attachmentThumbnailImageScalable"{/if}>
										{else}
											<img src="{$attachment->getLink()}" alt="" {if $attachment->width >= ATTACHMENT_THUMBNAIL_WIDTH && $attachment->height >= ATTACHMENT_THUMBNAIL_HEIGHT} class="attachmentThumbnailImageScalable"{/if}>
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
			<h2 class="messageSectionTitle">{lang}wcf.attachment.files{/lang}</h2>
			
            <div class="messageAttachmentList">
                {content}
                    {foreach from=$attachmentList->getGroupedObjects($objectID) item=attachment}
                        {if $attachment->showAsFile() && !$attachment->isEmbedded()}
                            <a href="{$attachment->getLink()}" class="messageAttachment jsTooltip" title="{lang}wcf.attachment.file.title{/lang}">
                                <span class="messageAttachmentIcon">
                                    <span class="messageAttachmentIconDefault icon icon32 fa-{@$attachment->getIconName()}"></span>
                                    <span class="messageAttachmentIconDownload icon icon32 fa-download"></span>
                                </span>
                                <span class="messageAttachmentFilename">{$attachment->filename}</span>
                                <span class="messageAttachmentMeta">{lang}wcf.attachment.file.info{/lang}</span>
                            </a>
                        {/if}
                    {/foreach}
                {/content}
            </div>
		</section>
	{/hascontent}
{/if}
