{include file='header' pageTitle='wcf.acp.language.import'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.language.import{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='LanguageList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.language.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formError'}

{if $success|isset}
	<woltlab-core-notice type="success">{lang}wcf.global.success.add{/lang}</woltlab-core-notice>
{/if}

<form enctype="multipart/form-data" method="post" action="{link controller='LanguageImport'}{/link}">
	<div class="section">
		<dl{if $errorField == 'languageUpload'} class="formError"{/if}>
			<dt><label for="languageUpload">{lang}wcf.acp.language.import.source.upload{/lang}</label></dt>
			<dd>
				<input type="file" id="languageUpload" name="languageUpload">
				{if $errorField == 'languageUpload'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.language.import.error{/lang} {$errorType}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'sourceLanguageID'} class="formError"{/if}>
			<dt><label for="sourceLanguageID">{lang}wcf.acp.language.add.source{/lang}</label></dt>
			<dd>
				<select id="sourceLanguageID" name="sourceLanguageID">
					{foreach from=$languages item=language}
						<option value="{$language->languageID}"{if $language->languageID == $sourceLanguageID} selected{/if}>{$language->languageName} ({$language->languageCode})</option>
					{/foreach}
				</select>
				{if $errorField == 'sourceLanguageID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.language.add.source.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.language.add.source.description{/lang}</small>
			</dd>
		</dl>

		<dl{if $errorField == 'packageID'} class="formError"{/if}>
			<dt><label for="packageID">{lang}wcf.acp.language.add.package{/lang}</label></dt>
			<dd>
				<select id="packageID" name="packageID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					{foreach from=$packages item=package}
						<option value="{$package->packageID}"{if $package->packageID == $packageID} selected{/if}>{$package->getName()} ({$package->package})</option>
					{/foreach}
				</select>
				{if $errorField == 'packageID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.language.add.package.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.language.add.package.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='fields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
