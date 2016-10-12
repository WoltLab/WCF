{include file='header' pageTitle='wcf.acp.language.export'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.language.export{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='LanguageList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.language.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

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
		
		<dl>
			<dt><label for="selectedPackages">{lang}wcf.acp.language.export.selectPackages{/lang}</label></dt>
			<dd>
				<select id="selectedPackages" name="selectedPackages[]" multiple size="20" class="long monospace" dir="ltr">
					<option value="*"{if $selectAllPackages} selected{/if}>{lang}wcf.acp.language.export.allPackages{/lang}</option>
					<option value="-">--------------------</option>
					{foreach from=$packages item=package}
						{assign var=loop value=$packageNameLength-$package->packageNameLength}
						<option value="{@$package->packageID}"{if $selectedPackages[$package->packageID]|isset} selected{/if}>{lang}{$package->packageName}{/lang} {section name=i loop=$loop}&nbsp;{/section}&nbsp;&nbsp;{$package->package}</option>
					{/foreach}
				</select>
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
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
