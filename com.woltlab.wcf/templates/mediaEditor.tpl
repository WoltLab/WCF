<ul class="mediaEditorButtons buttonGroup">
	<li><div class="mediaManagerMediaReplaceButton"></div></li>
</ul>

{if $media->isImage && $media->hasThumbnail('small')}
	<div class="mediaThumbnail">
		{@$media->getThumbnailTag('small')}
	</div>
{/if}

<div class="box48">
	{if $media->isImage}
		<span class="icon icon48 fa-file-image-o"></span>
	{else}
		{@$media->getElementTag(48)}
	{/if}
	
	<dl class="plain dataList">
		<dt>{lang}wcf.media.filename{/lang}</dt>
		<dd id="mediaFilename">{$media->filename}</dd>
		
		<dt>{lang}wcf.media.filesize{/lang}</dt>
		<dd id="mediaFilesize">{@$media->filesize|filesize}</dd>
		
		{if $media->isImage}
			<dt>{lang}wcf.media.imageDimensions{/lang}</dt>
			<dd id="mediaImageDimensions">{lang}wcf.media.imageDimensions.value{/lang}</dd>
		{/if}
		
		<dt>{lang}wcf.media.uploader{/lang}</dt>
		<dd id="mediaUploader">{user object=$media->getUserProfile()}</dd>
		
		<dt>{lang}wcf.media.downloads{/lang}</dt>
		<dd id="mediaDownloads">{#$media->downloads}</dd>
		
		{if $media->downloads}
			<dt>{lang}wcf.media.lastDownloadTime{/lang}</dt>
			<dd id="mediaDownloads">{@$media->lastDownloadTime|time}</dd>
		{/if}
	</dl>
</div>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.global.form.data{/lang}</h2>
	
	{hascontent}
		<dl>
			<dt><label for="categoryID_{@$media->mediaID}">{lang}wcf.media.categoryID{/lang}</label></dt>
			<dd>
				<select id="categoryID_{@$media->mediaID}" name="categoryID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					
					{content}
						{foreach from=$categoryList item=categoryItem}
							<option value="{$categoryItem->categoryID}">{$categoryItem->getTitle()}</option>
							
							{if $categoryItem->hasChildren()}
								{foreach from=$categoryItem item=subCategoryItem}
									<option value="{$subCategoryItem->categoryID}">&nbsp;&nbsp;&nbsp;&nbsp;{$subCategoryItem->getTitle()}</option>
									
									{if $subCategoryItem->hasChildren()}
										{foreach from=$subCategoryItem item=subSubCategoryItem}
											<option value="{$subSubCategoryItem->categoryID}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$subSubCategoryItem->getTitle()}</option>
										{/foreach}
									{/if}
								{/foreach}
							{/if}
						{/foreach}
					{/content}
				</select>
			</dd>
		</dl>
	{/hascontent}
	
	{if $availableLanguages|count > 1}
		<dl>
			<dt></dt>
			<dd>
				<label>
					<input type="checkbox" name="isMultilingual" value="1"{if $media->isMultilingual} checked{/if}>
					<span>{lang}wcf.media.isMultilingual{/lang}</span>
				</label>
			</dd>
		</dl>
		
		{include file='languageChooser' label='wcf.media.languageID'}
	{/if}
	
	<dl>
		<dt><label for="title_{@$media->mediaID}">{lang}wcf.global.title{/lang}</label></dt>
		<dd>
			<input type="text" id="title_{@$media->mediaID}" name="title" class="long">
		</dd>
	</dl>
	{if $availableLanguages|count > 1}
		{include file='multipleLanguageInputJavascript' elementIdentifier='title'|concat:'_':$media->mediaID forceSelection=true}
	{/if}
	
	{if $media->isImage}
		<dl>
			<dt><label for="caption_{@$media->mediaID}">{lang}wcf.media.caption{/lang}</label></dt>
			<dd>
				<textarea id="caption_{@$media->mediaID}" name="caption" cols="40" rows="3"></textarea>
			</dd>
		</dl>
		{if $availableLanguages|count > 1}
			{include file='multipleLanguageInputJavascript' elementIdentifier='caption'|concat:'_':$media->mediaID forceSelection=true}
		{/if}
		
		<dl>
			<dt></dt>
			<dd>
				<label>
					<input type="checkbox" name="captionEnableHtml" value="1"{if $media->captionEnableHtml} checked{/if}>
					<span>{lang}wcf.media.caption.enableHtml{/lang}</span>
				</label>
			</dd>
		</dl>
		
		<dl>
			<dt><label for="altText_{@$media->mediaID}">{lang}wcf.media.altText{/lang}</label></dt>
			<dd>
				<input type="text" id="altText_{@$media->mediaID}" name="altText" class="long">
			</dd>
		</dl>
		{if $availableLanguages|count > 1}
			{include file='multipleLanguageInputJavascript' elementIdentifier='altText'|concat:'_':$media->mediaID forceSelection=true}
		{/if}
	{/if}
	
	{event name='dataFields'}
</section>

{include file='aclSimple'}

<div class="formSubmit">
	<button data-type="submit" class="buttonPrimary">{lang}wcf.global.button.submit{/lang}</button>
</div>
