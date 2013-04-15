{include file='header' pageTitle='wcf.acp.language.export'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.language.export{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.language.add.success{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.language.canDeleteLanguage') || $__wcf->session->getPermission('admin.language.canEditLanguage')}
						<li><a href="{link controller='LanguageList'}{/link}" title="{lang}wcf.acp.menu.link.language.list{/lang}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.language.list{/lang}</span></a></li>
					{/if}
					
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form enctype="multipart/form-data" method="post" action="{link controller='LanguageExport' id=$languageID}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.language.export{/lang}</legend>
			
			<dl>
				<dt><label for="languageID">{lang}wcf.user.language{/lang}</label></dt>
				<dd>
					{htmlOptions options=$languages selected=$languageID name='languageID' id='languageID'}
				</dd>
			</dl>
			
			<dl>
				<dt><label for="selectedPackages">{lang}wcf.acp.language.export.selectPackages{/lang}</label></dt>
				<dd>
					<select id="selectedPackages" name="selectedPackages[]" multiple="multiple" size="20" class="long">
						<option value="*"{if $selectAllPackages} selected="selected"{/if}>{lang}wcf.acp.language.export.allPackages{/lang}</option>
						<option value="-">--------------------</option>
						{foreach from=$packages item=package}
							{assign var=loop value=$packageNameLength-$package->packageNameLength}
							<option value="{@$package->packageID}"{if $selectedPackages[$package->packageID]|isset} selected="selected"{/if}>{lang}{$package->packageName}{/lang} {section name=i loop=$loop}&nbsp;{/section}&nbsp;&nbsp;{$package->package}</option>
						{/foreach}
					</select>
				</dd>
			</dl>
			
			<dl>
				<dd>
					<label for="exportCustomValues"><input type="checkbox" name="exportCustomValues" id="exportCustomValues" value="1" /> {lang}wcf.acp.language.export.customValues{/lang}</label>
				</dd>
			</dl>
			
			{event name='exportFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
	</div>
</form>

{include file='footer'}