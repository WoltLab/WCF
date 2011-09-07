{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		var packageInstallation = new WCF.ACP.PackageInstallation('install', {@$queueID}, true);
	});
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/package{@$action|ucfirst}L.png" alt="" />
	<hgroup>
		<h1>{$archive->getPackageInfo('packageName')}</h1>
		<h2>{$archive->getPackageInfo('packageDescription')}</h2>
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

<fieldset>
	<legend>{lang}wcf.acp.package.view.properties{/lang}</legend>

	<dl>
		<dt>{lang}wcf.acp.package.view.identifier{/lang}</dt>
		<dd>{$archive->getPackageInfo('name')}</dd>
	</dl>
	
	<dl>
		<dt>{lang}wcf.acp.package.install.version{/lang}</dt>
		<dd>{$archive->getPackageInfo('version')}</dd>
	</dl>

	<dl>
		<dt>{lang}wcf.acp.package.view.date{/lang}</dt>
		<dd>{@$archive->getPackageInfo('date')|date}</dd>
	</dl>

	{if $archive->getPackageInfo('packageURL') != ''}
		<dl>
			<dt>{lang}wcf.acp.package.view.url{/lang}</dt>
			<dd><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$archive->getPackageInfo('packageURL')|rawurlencode}" class="externalURL">{$archive->getPackageInfo('packageURL')}</a></dd>
		</dl>
	{/if}
	
	<dl>
		<dt>{lang}wcf.acp.package.view.author{/lang}</dt>
		<dd>{if $archive->getPackageInfo('authorURL')}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$archive->getPackageInfo('authorURL')|rawurlencode}" class="externalURL">{$archive->getPackageInfo('author')}</a>{else}{$archive->getPackageInfo('author')}{/if}</dd>
	</dl>
	
	{if $additionalFields|isset}{@$additionalFields}{/if}
</fieldset>

{if $updatableInstances|count > 0}
	<p class="warning">{lang}wcf.acp.package.install.updatableInstances.warning{/lang}</p>
	
	<div class="border boxTitle">
		<hgroup>
			<h1>{lang}wcf.acp.package.install.updatableInstances{/lang}</h1>
			<h2>{lang}wcf.acp.package.install.updatableInstances.description{/lang}</h2>
		</hgroup>
		
		<table>
			<thead>
				<tr class="tableHead">
					<th><p class="emptyHead">{lang}wcf.acp.package.list.name{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.package.list.version{/lang}</p></th>
				</tr>
			</thead>
			
			<tbody>
			{foreach from=$updatableInstances item=$package}
				<tr>
					<td class="columnText"><p><a href="index.php?page=Package&amp;action=install&amp;queueID={@$queueID}&amp;step=changeToUpdate&amp;packageID={@$package.packageID}{@SID_ARG_2ND}">{$package.packageName}{if $package.instanceNo > 1 && $package.instanceName == ''} (#{#$package.instanceNo}){/if}</a></p></td>
					<td class="columnText"><p>{$package.packageVersion}</p></td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
{/if}

{if $requiredPackages|count > 0}
	<div class="border boxTitle">
		<hgroup>
			<h1>{lang}wcf.acp.package.view.requiredPackages{/lang}</h1>
			<h2>{lang}wcf.acp.package.view.requiredPackages.description{/lang}</h2>
		</hgroup>
	</div>
	
	<table>
		<thead>
			<tr>
				<th>{lang}wcf.acp.package.list.name{/lang}</th>
				<th>{lang}wcf.acp.package.list.version{/lang}</th>
			</tr>
		</thead>
		
		<tbody>
		{foreach from=$requiredPackages item=$package}
			<tr>
				<td class="columnText"><p>{lang}wcf.acp.package.install.packageName{/lang}</p></td>
				<td class="columnText"><p>{if $package.minversion|isset}{$package.minversion}{/if}</p></td>
			</tr>
		{/foreach}
		</tbody>
	</table>
{/if}

<div class="formSubmit">
	<input type="button" onclick="document.location.href=fixURL('index.php?page=Package&amp;action={@$action}&amp;queueID={@$queueID}&amp;step=cancel{@SID_ARG_2ND}')" value="{lang}wcf.global.button.back{/lang}" accesskey="c" />
	{if $missingPackages == 0 && $excludingPackages|count == 0 && $excludedPackages|count == 0}
		<input type="button" id="submitButton" value="{lang}wcf.global.button.next{/lang}" accesskey="s" />
	{/if}
</div>

{include file='footer'}
