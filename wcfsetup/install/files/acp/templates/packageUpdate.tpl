{include file='header'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.packageUpdate{/lang}</h1>
	</hgroup>
</header>

{if $errorField == 'updates'}
	{if $errorType === 'empty'}
		<p class="error">{lang}wcf.acp.packageUpdate.noneSelected{/lang}</p>
	{else}
		<p class="error">{lang}wcf.acp.packageUpdate.error{/lang} {$errorType->getMessage()} ({@$errorType->getCode()})</p>
		<!-- {$errorType->getTraceAsString()} -->
	{/if}
{/if}

{if $errorField == 'excludedPackages'}
	<div class="error">{lang}wcf.acp.packageUpdate.excludedPackages{/lang}
		<ul>
		{foreach from=$excludedPackages item=excludedPackage}
			<li>{if $excludedPackage.conflict == 'existingPackageExcludesNewPackage'}{lang}wcf.acp.packageUpdate.excludedPackages.existingPackageExcludesNewPackage{/lang}{else}{lang}wcf.acp.packageUpdate.excludedPackages.newPackageExcludesExistingPackage{/lang}{/if}</li>
		{/foreach}
		</ul>
	</div>
{/if}

{if $packageInstallationStack|count}
	<form method="post" action="{link controller='PackageUpdate'}{/link}">
		<div class="container containerPadding marginTop shadow">
			
			<fieldset>
				<legend>{lang}wcf.acp.packageUpdate.updates{/lang}</legend>
				
				<ul>
					{foreach from=$packageInstallationStack item=package}
						<li>
							{if $package.action == 'install'}
								{lang}wcf.acp.packageUpdate.install{/lang}
							{else}
								{lang}wcf.acp.packageUpdate.update{/lang}
							{/if}
						</li>
					{/foreach}
				</ul>
			</fieldset>
			
		</div>
		
		<div class="formSubmit">
			{if !$errorField}<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />{/if}
			{@SID_INPUT_TAG}
			<input type="hidden" name="send" value="1" />
			{foreach from=$updates key=package item=version}
				<input type="hidden" name="updates[{$package}]" value="{$version}" />
			{/foreach}
		</div>
	</form>
{/if}

{include file='footer'}
