{if $media->isImage}
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
		<dd id="mediaUploader">{@$media->getUserProfile()->getAnchorTag()}</dd>
	</dl>
</div>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.global.form.data{/lang}</h2>

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

	<dl>
		<dt><label for="title_{@$media->mediaID}">{lang}wcf.global.title{/lang}</label></dt>
		<dd>
			<input type="text" id="title_{@$media->mediaID}" name="title" class="long" value="TODO">
		</dd>
	</dl>
	{include file='multipleLanguageInputJavascript' elementIdentifier='title'|concat:'_':$media->mediaID forceSelection=true}

	<dl>
		<dt><label for="caption_{@$media->mediaID}">{lang}wcf.media.caption{/lang}</label></dt>
		<dd>
			<textarea id="caption_{@$media->mediaID}" name="caption" cols="40" rows="3">TODO</textarea>
		</dd>
	</dl>
	{include file='multipleLanguageInputJavascript' elementIdentifier='caption'|concat:'_':$media->mediaID forceSelection=true}

	<dl>
		<dt><label for="altText_{@$media->mediaID}">{lang}wcf.media.altText{/lang}</label></dt>
		<dd>
			<input type="text" id="altText_{@$media->mediaID}" name="altText" class="long" value="TODO">
		</dd>
	</dl>
	{include file='multipleLanguageInputJavascript' elementIdentifier='altText'|concat:'_':$media->mediaID forceSelection=true}

	{event name='dataFields'}
</section>

{include file='aclSimple'}

<div class="formSubmit">
	<button data-type="submit" class="buttonPrimary">{lang}wcf.global.button.submit{/lang}</button>
</div>
