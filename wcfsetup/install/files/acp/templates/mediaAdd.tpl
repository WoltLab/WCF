{include file='header' pageTitle='wcf.acp.media.'|concat:$action}

{if $action == 'add'}
	<script data-relocate="true">
		require(['EventHandler', 'WoltLab/WCF/Media/Upload'], function(EventHandler, MediaUpload) {
			new MediaUpload('uploadButton', 'mediaFile');
			
			// redirect the user to the edit form after uploading the file
			EventHandler.add('com.woltlab.wcf.media.upload', 'success', function(data) {
				for (var index in data.media) {
					window.location = '{link controller='MediaEdit' id=2147483648 encode=false}{/link}'.replace(2147483648, data.media[index].mediaID);
				}
			});
		});
	</script>
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 id="mediaActionTitle" class="contentTitle">{lang}wcf.acp.media.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='MediaList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.media.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $action == 'edit'}
	{include file='formError'}
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

{if $action == 'add'}
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.media.file{/lang}</h2>
		
		<dl>
			<dt></dt>
			<dd>
				<div id="mediaFile"></div>
				<div id="uploadButton"></div>
			</dd>
		</dl>
	</section>
{else}
	<form method="post" action="{link controller='MediaEdit' object=$media}{/link}">
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.form.data{/lang}</h2>
			
			<dl>
				<dt>{lang}wcf.media.file{/lang}</dt>
				<dd>
					{if $media->isImage}
						{@$media->getThumbnailTag('small')}
					{else}
						{$media->filename}
					{/if}
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>
					<label>
						<input type="checkbox" id="isMultilingual" name="isMultilingual" value="1"{if $isMultilingual} checked="checked"{/if} />
						<span>{lang}wcf.media.isMultilingual{/lang}</span>
					</label>
				</dd>
			</dl>
			
			{include file='languageChooser' label='wcf.media.languageID'}
			
			<dl{if $errorField == 'title'} class="formError"{/if}>
				<dt>{lang}wcf.global.title{/lang}</dt>
				<dd>
					<input type="text" id="title" name="title" value="{$i18nPlainValues['title']}" class="long" />
					{if $errorField == 'title'}
						<small class="innerError">
							{if $errorType == 'title' || $errorType == 'multilingual'}
								{lang}wcf.global.form.error.{@$errorType}{/lang}
							{else}
								{lang}wcf.media.title.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=true}
			
			<dl{if $errorField == 'caption'} class="formError"{/if}>
				<dt>{lang}wcf.media.caption{/lang}</dt>
				<dd>
					<textarea id="caption" name="caption" cols="40" rows="3">{$i18nPlainValues['caption']}</textarea>
					{if $errorField == 'caption'}
						<small class="innerError">
							{if $errorType == 'title' || $errorType == 'multilingual'}
								{lang}wcf.global.form.error.{@$errorType}{/lang}
							{else}
								{lang}wcf.media.caption.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			{include file='multipleLanguageInputJavascript' elementIdentifier='caption' forceSelection=true}
			
			<dl{if $errorField == 'altText'} class="formError"{/if}>
				<dt>{lang}wcf.media.altText{/lang}</dt>
				<dd>
					<input type="text" id="altText" name="altText" value="{$i18nPlainValues['altText']}" class="long" />
					{if $errorField == 'altText'}
						<small class="innerError">
							{if $errorType == 'title' || $errorType == 'multilingual'}
								{lang}wcf.global.form.error.{@$errorType}{/lang}
							{else}
								{lang}wcf.media.altText.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			{include file='multipleLanguageInputJavascript' elementIdentifier='altText' forceSelection=true}
			
			{event name='dataFields'}
		</section>
		
		{event name='sections'}
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</form>
{/if}

{if $action == 'edit'}
	{* this code needs to be put after all multipleLanguageInputJavascript template have been included *}
	<script data-relocate="true">
		require(['WoltLab/WCF/Language/Input'], function(LanguageInput) {
			function updateLanguageFields() {
				var languageIdContainer = elById('languageIDContainer').parentNode;
				
				if (elById('isMultilingual').checked) {
					LanguageInput.enable('title');
					LanguageInput.enable('caption');
					LanguageInput.enable('altText');
					
					elHide(languageIdContainer);
				}
				else {
					LanguageInput.disable('title');
					LanguageInput.disable('caption');
					LanguageInput.disable('altText');
					
					elShow(languageIdContainer);
				}
			};
			
			elById('isMultilingual').addEventListener('change', updateLanguageFields);
			
			updateLanguageFields();
			
			{if !$isMultilingual}
				elById('title').value = '{$i18nPlainValues['title']|encodeJs}';
				elById('title').caption = '{$i18nPlainValues['caption']|encodeJs}';
				elById('title').altText = '{$i18nPlainValues['altText']|encodeJs}';
			{/if}
		});
	</script>
{/if}

{include file='footer'}
