{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.ACP.PackageUninstallation({@$packageObj->packageID});
	});
//	]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/packageUninstallL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.package.uninstall{/lang}: {$packageObj->getName()}</h1>
		<h2>{$packageObj->packageDescription}</h2>
	</hgroup>
</header>

<fieldset>
	<legend>{lang}wcf.acp.package.view.properties{/lang}</legend>
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.identifier{/lang}</p>
		<p class="formField">{$packageObj->package}</p>
	</div>
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.version{/lang}</p>
		<p class="formField">{$packageObj->packageVersion}</p>
	</div>
	{if $packageObj->instanceNo > 0}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.instanceNo{/lang}</p>
			<p class="formField">{$packageObj->instanceNo}</p>
		</div>
	{/if}
	{if $packageObj->packageDir != ''}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.dir{/lang}</p>
			<p class="formField"><a href="{@RELATIVE_WCF_DIR}{$packageObj->packageDir}">{$packageObj->packageDir}</a></p>
		</div>
	{/if}
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.date{/lang}</p>
		<p class="formField">{@$packageObj->packageDate|date}</p>
	</div>
	{if $packageObj->packageURL != ''}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.url{/lang}</p>
			<p class="formField"><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$packageObj->packageURL|rawurlencode}" class="externalURL">{$packageObj->packageURL}</a></p>
		</div>
	{/if}
	{if $packageObj->parentPackageID}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.parent{/lang}</p>
			<p class="formField"><a href="index.php?page=PackageView&amp;packageID={@$packageObj->parentPackageID}{@SID_ARG_2ND}">{$packageObj->getParentPackage()->getName()}</a></p>
		</div>
	{/if}
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.author{/lang}</p>
		<p class="formField">{if $packageObj->authorURL}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$packageObj->authorURL|rawurlencode}" class="externalURL">{$packageObj->author}</a>{else}{$packageObj->author}{/if}</p>
	</div>
	
	{if $additionalFields|isset}{@$additionalFields}{/if}
</fieldset>

{if $dependentPackages|count > 0}
	{if $uninstallAvailable}
		<p class="warning">{lang}wcf.acp.package.uninstall.dependentPackages.warning{/lang}</p>
	{else}
		<p class="error">{lang}wcf.acp.package.uninstall.dependentPackages.error{/lang}</p>
	{/if}

	<div class="border boxTitle">
		<hgroup>
			<h1>{lang}wcf.acp.package.view.dependentPackages{/lang}</h1>
		</hgroup>
		<table>
			<thead>
				<tr>
					<th colspan="2"><p class="emptyHead">{lang}wcf.acp.package.list.id{/lang}</p></th>
					<th colspan="2"><p class="emptyHead">{lang}wcf.acp.package.list.name{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.package.list.author{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.package.list.version{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.package.list.date{/lang}</p></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$dependentPackages item=package}
				<tr>
					<td class="columnIcon">
						{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
							<a href="index.php?form=PackageStartInstall&amp;action=update&amp;packageID={@$package.packageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/packageUpdateS.png" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/packageUpdateDisabledS.png" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" />
						{/if}
						{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $package.package != 'com.woltlab.wcf' && $package.packageID != PACKAGE_ID}
							<a onclick="return confirm('{lang}wcf.acp.package.view.button.uninstall.sure{/lang}')" href="index.php?page=Package&amp;action=startUninstall&amp;packageID={@$package.packageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" />
						{/if}
						
						{if $package.additionalButtons|isset}{@$package.additionalButtons}{/if}
					</td>
					<td class="columnID"><p>{@$package.packageID}</p></td>
					<td class="columnIcon">
						{if $package.standalone}
							<img src="{@RELATIVE_WCF_DIR}icon/packageTypeStandaloneS.png" alt="" title="{lang}wcf.acp.package.list.standalone{/lang}" />
						{elseif $package.parentPackageID}
							<img src="{@RELATIVE_WCF_DIR}icon/packageTypePluginS.png" alt="" title="{lang}wcf.acp.package.list.plugin{/lang}" />
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/packageS.png" alt="" title="{lang}wcf.acp.package.list.other{/lang}" />
						{/if}
					</td>
					<td class="columnText" title="{$package.packageDescription}"><p><a href="index.php?page=PackageView&amp;packageID={@$package.packageID}{@SID_ARG_2ND}">{$package.packageName}{if $package.instanceNo > 1 && $package.instanceName == ''} (#{#$package.instanceNo}){/if}</a></p></td>
					<td class="columnText"><p>{if $package.authorURL}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$package.authorURL|rawurlencode}" class="externalURL">{$package.author}</a>{else}{$package.author}{/if}</p></td>
					<td class="columnText"><p>{$package.packageVersion}</p></td>
					<td class="columnDate"><p>{@$package.packageDate|date}</p></td>
					
					{if $package.additionalColumns|isset}{@$package.additionalColumns}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
{/if}

<div class="formSubmit">
	{@SID_INPUT_TAG}
 	<input type="hidden" name="action" value="startUninstall" />
 	<input type="hidden" name="packageID" value="{@$packageObj->packageID}" />
 	<input type="hidden" name="send" value="1" />
	<input type="button" accesskey="c" value="{lang}wcf.global.button.back{/lang}" onclick="document.location.href=fixURL('index.php?page=PackageView&amp;packageID={@$packageID}{@SID_ARG_2ND}')" />
	<input type="button" accesskey="s" id="uninstallPackage" value="{lang}wcf.global.button.next{/lang}" />
</div>

{include file='footer'}
