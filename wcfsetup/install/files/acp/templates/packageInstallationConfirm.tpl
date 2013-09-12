{capture assign='pageTitle'}{lang}wcf.acp.package.{@$queue->action}.title{/lang}: {$archive->getLocalizedPackageInfo('packageName')}{/capture}
{include file='header'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.package.install.title': '{lang}wcf.acp.package.install.title{/lang}',
			'wcf.acp.package.installation.rollback': '{lang}wcf.acp.package.installation.rollback{/lang}',
			'wcf.acp.package.uninstallation.title': '{lang}wcf.acp.package.uninstallation.title{/lang}',
			'wcf.acp.package.update.title': '{lang}wcf.acp.package.update.title{/lang}'
		});
		
		new WCF.ACP.Package.Installation({@$queue->queueID}, undefined, {if $queue->action == 'install'}{if $queue->isApplication}false{else}true{/if}, false{else}false, true{/if});
		
		new WCF.ACP.Package.Installation.Cancel({@$queue->queueID});
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.package.{@$queue->action}.title{/lang}: {$archive->getLocalizedPackageInfo('packageName')}</h1>
	<p>{$archive->getLocalizedPackageInfo('packageDescription')}</p>
</header>

{if $missingPackages > 0}
	<p class="error">{lang}wcf.acp.package.install.error.missingRequirements{/lang}</p>
{/if}

{if $excludingPackages|count > 0}
	<div class="error">{lang}wcf.acp.package.install.error.excludingPackages{/lang}
		<ul>
		{foreach from=$excludingPackages item=excludingPackage}
			<li>{lang}wcf.acp.package.install.error.excludingPackages.excludingPackage{/lang}</li>
		{/foreach}
		</ul>
	</div>
{/if}

{if $excludedPackages|count > 0}
	<div class="error">{lang}wcf.acp.package.install.error.excludedPackages{/lang}
		<ul>
		{foreach from=$excludedPackages item=excludedPackage}
			<li>{lang}wcf.acp.package.install.error.excludedPackages.excludedPackage{/lang}</li>
		{/foreach}
		</ul>
	</div>
{/if}

<div class="container containerPadding marginTop">
	<fieldset>
		<legend>{lang}wcf.acp.package.information.properties{/lang}</legend>
		
		<dl>
			<dt>{lang}wcf.acp.package.identifier{/lang}</dt>
			<dd>{$archive->getPackageInfo('name')}</dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.package.version{/lang}</dt>
			<dd>{$archive->getPackageInfo('version')}</dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.package.packageDate{/lang}</dt>
			<dd>{@$archive->getPackageInfo('date')|date}</dd>
		</dl>
		
		{if $archive->getPackageInfo('packageURL') != ''}
			<dl>
				<dt>{lang}wcf.acp.package.url{/lang}</dt>
				<dd><a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$archive->getPackageInfo('packageURL')|rawurlencode}" class="externalURL">{$archive->getPackageInfo('packageURL')}</a></dd>
			</dl>
		{/if}
		
		<dl>
			<dt>{lang}wcf.acp.package.author{/lang}</dt>
			<dd>{if $archive->getAuthorInfo('authorURL')}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$archive->getAuthorInfo('authorURL')|rawurlencode}" class="externalURL">{$archive->getAuthorInfo('author')}</a>{else}{$archive->getAuthorInfo('author')}{/if}</dd>
		</dl>
		
		{event name='propertyFields'}
	</fieldset>
</div>

{if $requiredPackages|count > 0}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.package.dependencies.required{/lang} <span class="badge badgeInverse">{#$requiredPackages|count}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnTitle columnPackageName">{lang}wcf.acp.package.name{/lang}</th>
					<th class="columnText columnPackage">{lang}wcf.acp.package.identifier{/lang}</th>
					<th class="columnText columnPackageVersion">{lang}wcf.acp.package.installation.requiredVersion{/lang}</th>
					<th class="columnText">{lang}wcf.acp.package.installation.packageStatus{/lang}</th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$requiredPackages item=$package}
					<tr>
						<td class="columnTitle columnPackageName">{if $package[package]}{$package[package]->packageName|language}{/if}</td>
						<td class="columnText columnPackage">{@$package.name}</td>
						<td class="columnText columnPackageVersion">{if $package.minversion|isset}<span class="badge label {if $package.status == 'installed'}green{elseif $package.status == 'delivered'}yellow{else}red{/if}">{$package.minversion}</span>{/if}</td>
						<td class="columnText">{lang}wcf.acp.package.installation.packageStatus.{@$package.status}{/lang}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{/if}

<div class="formSubmit">
	<input type="button" id="backButton" value="{lang}wcf.global.button.back{/lang}" accesskey="c" />
	{if $missingPackages == 0 && $excludingPackages|count == 0 && $excludedPackages|count == 0}
		<input type="button" class="buttonPrimary" id="submitButton" value="{lang}wcf.global.button.next{/lang}" class="default" accesskey="s" />
	{/if}
</div>

{include file='footer'}
