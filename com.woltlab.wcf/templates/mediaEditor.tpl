<div id="mediaThumbnail"></div>

<div class="box48">
	<span id="mediaFileIcon" class="icon icon48 fa-file-o"></span>
	
	<dl class="plain dataList">
		<dt>{lang}wcf.media.filename{/lang}</dt>
		<dd id="mediaFilename"></dd>
		
		<dt>{lang}wcf.media.filesize{/lang}</dt>
		<dd id="mediaFilesize"></dd>
		
		<dt>{lang}wcf.media.imageDimensions{/lang}</dt>
		<dd id="mediaImageDimensions"></dd>
		
		<dt>{lang}wcf.media.uploader{/lang}</dt>
		<dd id="mediaUploader"></dd>
	</dl>
</div>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.global.form.data{/lang}</h2>
	
	<dl>
		<dt></dt>
		<dd>
			<label>
				<input type="checkbox" id="isMultilingual" name="isMultilingual" value="1">
				<span>{lang}wcf.media.isMultilingual{/lang}</span>
			</label>
		</dd>
	</dl>
	
	{include file='languageChooser' label='wcf.media.languageID'}
	
	<dl>
		<dt><label for="title">{lang}wcf.global.title{/lang}</label></dt>
		<dd>
			<input type="text" id="title" name="title" class="long">
		</dd>
	</dl>
	{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=true}
	
	<dl>
		<dt><label for="caption">{lang}wcf.media.caption{/lang}</label></dt>
		<dd>
			<textarea id="caption" name="caption" cols="40" rows="3"></textarea>
		</dd>
	</dl>
	{include file='multipleLanguageInputJavascript' elementIdentifier='caption' forceSelection=true}
	
	<dl>
		<dt><label for="altText">{lang}wcf.media.altText{/lang}</label></dt>
		<dd>
			<input type="text" id="altText" name="altText" class="long">
		</dd>
	</dl>
	{include file='multipleLanguageInputJavascript' elementIdentifier='altText' forceSelection=true}
	
	{event name='dataFields'}
</section>

<div class="formSubmit">
	<button data-type="submit" class="buttonPrimary">{lang}wcf.global.button.submit{/lang}</button>
</div>
