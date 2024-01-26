{include file='header' pageTitle='wcf.acp.language.export'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.language.export{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='LanguageList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.language.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formError'}

<form enctype="multipart/form-data" method="post" action="{link controller='LanguageExport'}{/link}">
	<div class="section">
		<dl{if $errorField == 'languageID'} class="formError"{/if}>
			<dt><label for="languageID">{lang}wcf.user.language{/lang}</label></dt>
			<dd>
				{htmlOptions options=$languages selected=$languageID name='languageID' id='languageID'}
				{if $errorField == 'languageID'}
					<small class="innerError">
						{if $errorType == 'noValidSelection'}
							{lang}wcf.global.form.error.noValidSelection{/lang}
						{else}
							{lang}wcf.acp.language.languageID.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'packageID'} class="formError"{/if}>
			<dt><label for="packageID">{lang}wcf.acp.language.export.package{/lang}</label></dt>
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
							{lang}wcf.acp.language.export.package.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.language.export.package.description{/lang}</small>
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label for="exportCustomValues"><input type="checkbox" name="exportCustomValues" id="exportCustomValues" value="1"> {lang}wcf.acp.language.export.customValues{/lang}</label>
			</dd>
		</dl>
		
		{event name='exportFields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}">
		{csrfToken}
	</div>
</form>

{include file='footer'}
