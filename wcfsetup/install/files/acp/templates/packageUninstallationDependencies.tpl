{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.ACP.PackageUninstallation({@$packageObj->packageID});
	});
//	]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.package.uninstall{/lang}: {$packageObj->getName()}</h1>
		<h2>{$packageObj->packageDescription}</h2>
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
			<dd><a href="{@RELATIVE_WCF_DIR}{$packageObj->packageDir}">{$packageObj->packageDir}</a></dd>
		</dl>
	{/if}
	<dl>
		<dt>{lang}wcf.acp.package.view.date{/lang}</dt>
		<dd>{@$packageObj->packageDate|date}</dd>
	</dl>
	{if $packageObj->packageURL != ''}
		<dl>
			<dt>{lang}wcf.acp.package.view.url{/lang}</dt>
			<dd><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$packageObj->packageURL|rawurlencode}" class="externalURL">{$packageObj->packageURL}</a></dd>
		</dl>
	{/if}
	{if $packageObj->parentPackageID}
		<dl>
			<dt>{lang}wcf.acp.package.view.parent{/lang}</dt>
			<dd><a href="index.php?page=PackageView&amp;packageID={@$packageObj->parentPackageID}{@SID_ARG_2ND}">{$packageObj->getParentPackage()->getName()}</a></dd>
		</dl>
	{/if}
	<dl>
		<dt>{lang}wcf.acp.package.view.author{/lang}</dt>
		<dd>{if $packageObj->authorURL}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$packageObj->authorURL|rawurlencode}" class="externalURL">{$packageObj->author}</a>{else}{$packageObj->author}{/if}</dd>
	</dl>
	
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
					<th colspan="2" class="columnID">{lang}wcf.acp.package.list.id{/lang}</th>
					<th colspan="2" class="columnTitle">{lang}wcf.acp.package.list.name{/lang}</th>
					<th class="columnText">{lang}wcf.acp.package.list.author{/lang}</th>
					<th class="columnText">{lang}wcf.acp.package.list.version{/lang}</th>
					<th class="columnDate">{lang}wcf.acp.package.list.date{/lang}</th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			
			<tbody>
			{foreach from=$dependentPackages item=package}
				<tr>
					<td class="columnIcon">
						{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
							<a href="index.php?form=PackageStartInstall&amp;action=update&amp;packageID={@$package.packageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" class="balloonTooltip" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/updateD1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" />
						{/if}
						{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $package.package != 'com.woltlab.wcf' && $package.packageID != PACKAGE_ID}
							<a onclick="return confirm('{lang}wcf.acp.package.view.button.uninstall.sure{/lang}')" href="index.php?page=Package&amp;action=startUninstall&amp;packageID={@$package.packageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" class="balloonTooltip" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteD1.svg" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" />
						{/if}
						
						{if $package.additionalButtons|isset}{@$package.additionalButtons}{/if}
					</td>
					<td class="columnID"><p>{@$package.packageID}</p></td>
					<td class="columnIcon">
						{if $package.standalone}
							<img src="{@RELATIVE_WCF_DIR}icon/packageStandalone1.svg" alt="" title="{lang}wcf.acp.package.list.standalone{/lang}" class="balloonTooltip" />
						{elseif $package.parentPackageID}
							<img src="{@RELATIVE_WCF_DIR}icon/packagePlugin1.svg" alt="" title="{lang}wcf.acp.package.list.plugin{/lang}" class="balloonTooltip" />
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/package1.svg" alt="" title="{lang}wcf.acp.package.list.other{/lang}" class="balloonTooltip" />
						{/if}
					</td>
					<td class="columnTitle" title="{$package.packageDescription}"><p><a href="index.php?page=PackageView&amp;packageID={@$package.packageID}{@SID_ARG_2ND}">{$package.packageName}{if $package.instanceNo > 1 && $package.instanceName == ''} (#{#$package.instanceNo}){/if}</a></p></td>
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
	<input type="button" value="{lang}wcf.global.button.back{/lang}" onclick="document.location.href=fixURL('index.php?page=PackageView&amp;packageID={@$packageID}{@SID_ARG_2ND}')" accesskey="c" />
	<input type="button" id="uninstallPackage" value="{lang}wcf.global.button.next{/lang}" class="default" accesskey="s" />
</div>

{include file='footer'}
