{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		var packageInstallation = new WCF.ACP.PackageInstallation('install', {@$queueID}, true);
	});
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/package{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{$archive->getPackageInfo('packageName')}</h2>
		<p>{$archive->getPackageInfo('packageDescription')}</p>
	</div>
</div>

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

	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.identifier{/lang}</p>
		<p class="formField">{$archive->getPackageInfo('name')}</p>
	</div>
	
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.install.version{/lang}</p>
		<p class="formField">{$archive->getPackageInfo('version')}</p>
	</div>

	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.date{/lang}</p>
		<p class="formField">{@$archive->getPackageInfo('date')|date}</p>
	</div>

	{if $archive->getPackageInfo('packageURL') != ''}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.url{/lang}</p>
			<p class="formField"><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$archive->getPackageInfo('packageURL')|rawurlencode}" class="externalURL">{$archive->getPackageInfo('packageURL')}</a></p>
		</div>
	{/if}
	
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.author{/lang}</p>
		<p class="formField">{if $archive->getPackageInfo('authorURL')}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$archive->getPackageInfo('authorURL')|rawurlencode}" class="externalURL">{$archive->getPackageInfo('author')}</a>{else}{$archive->getPackageInfo('author')}{/if}</p>
	</div>
	
	{if $additionalFields|isset}{@$additionalFields}{/if}
</fieldset>

{if $updatableInstances|count > 0}
	<p class="warning" style="margin: 20px 0 10px 0">{lang}wcf.acp.package.install.updatableInstances.warning{/lang}</p>
	
	<div class="border titleBarPanel">
		<div class="containerHead">
			<h3>{lang}wcf.acp.package.install.updatableInstances{/lang}</h3>
			<p class="smallFont light">{lang}wcf.acp.package.install.updatableInstances.description{/lang}</p>
		</div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.name{/lang}</span></div></th>
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.version{/lang}</span></div></th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$updatableInstances item=$package}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnText"><a href="index.php?page=Package&amp;action=install&amp;queueID={@$queueID}&amp;step=changeToUpdate&amp;updatePackageID={@$package.packageID}{@SID_ARG_2ND}">{$package.packageName}{if $package.instanceNo > 1 && $package.instanceName == ''} (#{#$package.instanceNo}){/if}</a></td>
					<td class="columnText">{$package.packageVersion}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
{/if}

{if $requiredPackages|count > 0}
	<div class="border titleBarPanel">
		<div class="containerHead">
			<h3>{lang}wcf.acp.package.view.requiredPackages{/lang}</h3>
			<p class="smallFont light">{lang}wcf.acp.package.view.requiredPackages.description{/lang}</p>
		</div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.name{/lang}</span></div></th>
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.version{/lang}</span></div></th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$requiredPackages item=$package}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnText">{lang}wcf.acp.package.install.packageName{/lang}</td>
					<td class="columnText">{if $package.minversion|isset}{$package.minversion}{/if}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
{/if}

<div class="formSubmit">
	<input type="button" accesskey="c" value="{lang}wcf.global.button.back{/lang}" onclick="document.location.href=fixURL('index.php?page=Package&amp;action={@$action}&amp;queueID={@$queueID}&amp;step=cancel{@SID_ARG_2ND}')" />
	
	{if $missingPackages == 0 && $excludingPackages|count == 0 && $excludedPackages|count == 0}
		<input type="button" accesskey="s" id="submitButton" value="{lang}wcf.global.button.next{/lang}" />
	{/if}
</div>

{include file='footer'}