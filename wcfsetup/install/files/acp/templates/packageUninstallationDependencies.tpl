{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.ACP.PackageUninstallation({@$packageObj->packageID});
	});
//	]]>
</script>

<header class="wcf-container wcf-mainHeading">
	<img src="{@$__wcf->getPath()}icon/delete1.svg" alt="" class="wcf-containerIcon" />
	<hgroup class="wcf-containerContent">
		<h1>{lang}wcf.acp.package.uninstall{/lang}: {$packageObj->getName()}</h1>
		<h2>{$packageObj->packageDescription|language}</h2>
	</hgroup>
</header>

<fieldset>
	<legend>{lang}wcf.acp.package.view.properties{/lang}</legend>
	
	<dl>
		<dt>{lang}wcf.acp.package.view.identifier{/lang}</dt>
		<dd>{$packageObj->package}</dd>
	</dl>
	<dl>
		<dt>{lang}wcf.acp.package.view.version{/lang}</dt>
		<dd>{$packageObj->packageVersion}</dd>
	</dl>
	{if $packageObj->instanceNo > 0}
		<dl>
			<dt>{lang}wcf.acp.package.view.instanceNo{/lang}</dt>
			<dd>{$packageObj->instanceNo}</dd>
		</dl>
	{/if}
	{if $packageObj->packageDir != ''}
		<dl>
			<dt>{lang}wcf.acp.package.view.dir{/lang}</dt>
			<dd><a href="{@$__wcf->getPath()}{$packageObj->packageDir}">{$packageObj->packageDir}</a></dd>
		</dl>
	{/if}
	<dl>
		<dt>{lang}wcf.acp.package.view.date{/lang}</dt>
		<dd>{@$packageObj->packageDate|date}</dd>
	</dl>
	{if $packageObj->packageURL != ''}
		<dl>
			<dt>{lang}wcf.acp.package.view.url{/lang}</dt>
			<dd><a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$packageObj->packageURL|rawurlencode}" class="wcf-externalURL">{$packageObj->packageURL}</a></dd>
		</dl>
	{/if}
	{if $packageObj->parentPackageID}
		<dl>
			<dt>{lang}wcf.acp.package.view.parent{/lang}</dt>
			<dd><a href="{link controller='PackageView' id=$packageObj->parentPackageID}{/link}">{$packageObj->getParentPackage()->getName()}</a></dd>
		</dl>
	{/if}
	<dl>
		<dt>{lang}wcf.acp.package.view.author{/lang}</dt>
		<dd>{if $packageObj->authorURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$packageObj->authorURL|rawurlencode}" class="wcf-externalURL">{$packageObj->author}</a>{else}{$packageObj->author}{/if}</dd>
	</dl>
	
	{event name='propertyFields'}
</fieldset>

{if $dependentPackages|count > 0}
	{if $uninstallAvailable}
		<p class="wcf-warning">{lang}wcf.acp.package.uninstall.dependentPackages.warning{/lang}</p>
	{else}
		<p class="wcf-error">{lang}wcf.acp.package.uninstall.dependentPackages.error{/lang}</p>
	{/if}

	<div class="wcf-box wcf-boxPadding wcf-boxTitle">
		<hgroup>
			<h1>{lang}wcf.acp.package.view.dependentPackages{/lang}</h1>
		</hgroup>
		
		<table class="wcf-table">
			<thead>
				<tr>
					<th colspan="2" class="columnID">{lang}wcf.acp.package.list.id{/lang}</th>
					<th colspan="2" class="columnTitle">{lang}wcf.acp.package.list.name{/lang}</th>
					<th class="columnText">{lang}wcf.acp.package.list.author{/lang}</th>
					<th class="columnText">{lang}wcf.acp.package.list.version{/lang}</th>
					<th class="columnDate">{lang}wcf.acp.package.list.date{/lang}</th>
					
					{event name='headColumns'}
				</tr>
			</thead>
			
			<tbody>
			{foreach from=$dependentPackages item=package}
				<tr>
					<td class="columnIcon">
						{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
							<a href="{link controller='PackageStartInstall' id=$package.packageID}action=update{/link}"><img src="{@$__wcf->getPath()}icon/update1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" class="jsTooltip" /></a>
						{else}
							<img src="{@$__wcf->getPath()}icon/updateD1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" />
						{/if}
						{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $package.package != 'com.woltlab.wcf' && $package.packageID != PACKAGE_ID}
							<a onclick="return confirm('{lang}wcf.acp.package.view.button.uninstall.sure{/lang}')" href="{link controller='Package' id=$package.packageID}action=startUninstall{/link}"><img src="{@$__wcf->getPath()}icon/delete1.svg" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" class="jsTooltip" /></a>
						{else}
							<img src="{@$__wcf->getPath()}icon/deleteD1.svg" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" />
						{/if}
						
						{event name='buttons'}
					</td>
					<td class="columnID"><p>{@$package.packageID}</p></td>
					<td class="columnIcon">
						{if $package.isApplication}
							<img src="{@$__wcf->getPath()}icon/packageApplication1.svg" alt="" title="{lang}wcf.acp.package.list.isApplication{/lang}" class="jsTooltip" />
						{elseif $package.parentPackageID}
							<img src="{@$__wcf->getPath()}icon/packagePlugin1.svg" alt="" title="{lang}wcf.acp.package.list.plugin{/lang}" class="jsTooltip" />
						{else}
							<img src="{@$__wcf->getPath()}icon/package1.svg" alt="" title="{lang}wcf.acp.package.list.other{/lang}" class="jsTooltip" />
						{/if}
					</td>
					<td class="columnTitle" title="{$package.packageDescription|language}"><p><a href="{link controller='PackageView' id=$package.packageID}{/link}">{$package.packageName|language}{if $package.instanceNo > 1 && $package.instanceName == ''} (#{#$package.instanceNo}){/if}</a></p></td>
					<td class="columnText"><p>{if $package.authorURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$package.authorURL|rawurlencode}" class="wcf-externalURL">{$package.author}</a>{else}{$package.author}{/if}</p></td>
					<td class="columnText"><p>{$package.packageVersion}</p></td>
					<td class="columnDate"><p>{@$package.packageDate|date}</p></td>
					
					{event name='columns'}
				</tr>
			{/foreach}
			</tbody>
		</table>
		
	</div>
{/if}

<div class="wcf-formSubmit">
	{@SID_INPUT_TAG}
 	<input type="hidden" name="action" value="startUninstall" />
 	<input type="hidden" name="packageID" value="{@$packageObj->packageID}" />
 	<input type="hidden" name="send" value="1" />
	<input type="button" value="{lang}wcf.global.button.back{/lang}" onclick="document.location.href=fixURL('{link controller='PackageView' id=$packageID}{/link}')" accesskey="c" />
	<input type="button" id="uninstallPackage" value="{lang}wcf.global.button.next{/lang}" class="default" accesskey="s" />
</div>

{include file='footer'}
