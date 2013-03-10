{include file='header' pageTitle=$archive->getLocalizedPackageInfo('packageName')}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Language.add('wcf.acp.package.installation.title', '{lang}wcf.acp.package.installation.title{/lang}');
		new WCF.ACP.Package.Installation({@$queueID}, undefined, true);
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{$archive->getLocalizedPackageInfo('packageName')}</h1>
		<h2>{$archive->getLocalizedPackageInfo('packageDescription')}</h2>
	</hgroup>
</header>

{if $missingPackages > 0}
	<p class="error">{lang}wcf.acp.package.install.error{/lang}</p>
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

<fieldset class="marginTop">
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

{if $requiredPackages|count > 0}
	<div class="tabularBox tabularBoxTitle marginTop">
		<hgroup>
			<h1>{lang}wcf.acp.package.dependencies.required{/lang} <span class="badge badgeInverse">{#$requiredPackages|count}</span></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnTitle">{lang}wcf.acp.package.name{/lang}</th>
					<th class="columnText">{lang}wcf.acp.package.installation.packageStatus{/lang}</th>
					<th class="columnDigits">{lang}wcf.acp.package.installation.requiredVersion{/lang}</th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$requiredPackages item=$package}
					<tr>
						<td class="columnTitle"><p><span class="badge label {if $package.status == 'installed'}green{elseif $package.status == 'delivered'}yellow{else}red{/if}">{@$package.name}</span></p></td>
						<td class="columnText"><p>{lang}wcf.acp.package.installation.packageStatus.{@$package.status}{/lang}</p></td>
						<td class="columnDigits"><p>{if $package.minversion|isset}{if $package.status == 'missingVersion'}<span class="badge label red">{/if}{$package.minversion}{if $package.status == 'missingVersion'}</span>{/if}{/if}</p></td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{/if}

<div class="formSubmit">
	<input type="button" onclick="document.location.href=fixURL('{link controller='Package'}action={@$action}&queueID={@$queueID}&step=cancel{/link}')" value="{lang}wcf.global.button.back{/lang}" accesskey="c" />
	{if $missingPackages == 0 && $excludingPackages|count == 0 && $excludedPackages|count == 0}
		<input type="button" id="submitButton" value="{lang}wcf.global.button.next{/lang}" class="default" accesskey="s" />
	{/if}
</div>

{include file='footer'}
