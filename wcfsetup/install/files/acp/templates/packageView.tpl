{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/packageL.png" alt="" />
	<hgroup>
		<h1>{$package->getName()}</h1>
		<h2>{$package->packageDescription}</h2>
	</hgroup>
</header>

<fieldset>
	<legend>{lang}wcf.acp.package.view.properties{/lang}</legend>
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.identifier{/lang}</p>
		<p class="formField">{$package->package}</p>
	</div>
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.version{/lang}</p>
		<p class="formField">{$package->packageVersion}</p>
	</div>
	{if $package->instanceNo > 1}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.instanceNo{/lang}</p>
			<p class="formField">{#$package->instanceNo}</p>
		</div>
	{elseif $package->package == 'com.woltlab.wcf' && WCF_N != 1}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.instanceNo{/lang}</p>
			<p class="formField">{#WCF_N}</p>
		</div>
	{/if}
	{if $package->packageDir != ''}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.dir{/lang}</p>
			<p class="formField"><a href="{@RELATIVE_WCF_DIR}{$package->packageDir}">{$package->packageDir}</a></p>
		</div>
	{elseif $package->package == 'com.woltlab.wcf'}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.dir{/lang}</p>
			<p class="formField">{WCF_DIR}</p>
		</div>
	{/if}
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.date{/lang}</p>
		<p class="formField">{@$package->packageDate|date}</p>
	</div>
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.installDate{/lang}</p>
		<p class="formField">{@$package->installDate|time}</p>
	</div>
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.updateDate{/lang}</p>
		<p class="formField">{@$package->updateDate|time}</p>
	</div>
	{if $package->packageURL != ''}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.url{/lang}</p>
			<p class="formField"><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$package->packageURL|rawurlencode}" class="externalURL">{$package->packageURL}</a></p>
		</div>
	{/if}
	{if $package->parentPackageID}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.parent{/lang}</p>
			<p class="formField"><a href="index.php?page=PackageView&amp;activePackageID={@$package->parentPackageID}{@SID_ARG_2ND}">{$package->parentPackage()->getName()}</a></p>
		</div>
	{/if}
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.author{/lang}</p>
		<p class="formField">{if $package->authorURL}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$package->authorURL|rawurlencode}" class="externalURL">{$package->author}</a>{else}{$package->author}{/if}</p>
	</div>
	
	{if $additionalFields|isset}{@$additionalFields}{/if}
</fieldset>

{assign var=noDependentIsActive value=true}
{foreach from=$dependentPackages item=dependentPackage}
	{if $dependentPackage.package != 'com.woltlab.wcf' && $dependentPackage.packageID == PACKAGE_ID}
		{assign var=noDependentIsActive value=false}
		{* TODO: maybe show user that this package can't be uninstalled because a dependent package is the active standalone application *}
	{/if}
{/foreach}

<div class="contentHeader">
	<nav class="largeButtons">
		<ul>
			{if PACKAGE_ID != $package->packageID}
				{if $package->standalone && $package->package != 'com.woltlab.wcf'}<li><a href="{@RELATIVE_WCF_DIR}{$package->packageDir}acp/index.php?packageID={@$package->packageID}{@SID_ARG_2ND}" title="{lang}wcf.acp.package.view.button.makeActive{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/packageMakeActiveM.png" alt="" /> <span>{lang}wcf.acp.package.view.button.makeActive{/lang}</span></a></li>{/if}
				{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $noDependentIsActive}<li><a href="index.php?page=Package&amp;action=startUninstall&amp;activePackageID={@$package->packageID}{@SID_ARG_2ND}" onclick="return confirm('{lang}wcf.acp.package.view.button.uninstall.sure{/lang}')" title="{lang}wcf.acp.package.view.button.uninstall{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/packageUninstallM.png" alt="" /> <span>{lang}wcf.acp.package.view.button.uninstall{/lang}</span></a></li>{/if}
			{/if}
			{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}<li><a href="index.php?form=PackageStartInstall&amp;action=update&amp;activePackageID={@$package->packageID}{@SID_ARG_2ND}" title="{lang}wcf.acp.package.view.button.update{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/packageUpdateM.png" alt="" /> <span>{lang}wcf.acp.package.view.button.update{/lang}</span></a></li>{/if}
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

{if $requiredPackages|count > 0}
	<div class="border titleBarPanel">
		<div class="containerHead">
			<div class="containerIcon"><a onclick="openList('requiredPackages')"><img id="requiredPackagesImage" src="{@RELATIVE_WCF_DIR}icon/minusS.png" alt="" /></a></div>
			<div class="containerContent">
				<h3><a onclick="openList('requiredPackages')">{lang}wcf.acp.package.view.requiredPackages{/lang}</a></h3>
				<p class="smallFont light">{lang}wcf.acp.package.view.requiredPackages.description{/lang}</p>
			</div>
		</div>
	</div>
	<div class="border borderMarginRemove" id="requiredPackages">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th colspan="2"><div><span class="emptyHead">{lang}wcf.acp.package.list.id{/lang}</span></div></th>
					<th colspan="2"><div><span class="emptyHead">{lang}wcf.acp.package.list.name{/lang}</span></div></th>
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.author{/lang}</span></div></th>
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.version{/lang}</span></div></th>
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.date{/lang}</span></div></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$requiredPackages item=$package}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
							<a href="index.php?form=PackageStartInstall&amp;action=update&amp;activePackageID={@$package.packageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/packageUpdateS.png" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/packageUpdateDisabledS.png" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" />
						{/if}
						{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $package.package != 'com.woltlab.wcf' && $package.packageID != PACKAGE_ID}
							<a onclick="return confirm('{lang}wcf.acp.package.view.button.uninstall.sure{/lang}')" href="index.php?page=Package&amp;action=startUninstall&amp;activePackageID={@$package.packageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" /></a>
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
					<td class="columnText" title="{$package.packageDescription}"><p><a href="index.php?page=PackageView&amp;activePackageID={@$package.packageID}{@SID_ARG_2ND}">{$package.packageName}{if $package.instanceNo > 1 && $package.instanceName == ''} (#{#$package.instanceNo}){/if}</a></p></td>
					<td class="columnText">{if $package.authorURL}<p><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$package.authorURL|rawurlencode}" class="externalURL">{$package.author}</a>{else}{$package.author}</p>{/if}</td>
					<td class="columnText"><p>{$package.packageVersion}</p></td>
					<td class="columnDate"><p>{@$package.packageDate|date}</p></td>
					
					{if $package.additionalColumns|isset}{@$package.additionalColumns}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	
	<script type="text/javascript">
		//<![CDATA[
		initList('requiredPackages', 0);
		//]]>
	</script>
{/if}

{if $dependentPackages|count > 0}
	<div class="border titleBarPanel">
		<div class="containerHead">
			<div class="containerIcon"><a onclick="openList('dependentPackages')"><img id="dependentPackagesImage" src="{@RELATIVE_WCF_DIR}icon/minusS.png" alt="" /></a></div>
			<div class="containerContent">
				<h3><a onclick="openList('dependentPackages')">{lang}wcf.acp.package.view.dependentPackages{/lang}</a></h3>
				<p class="smallFont light">{lang}wcf.acp.package.view.dependentPackages.description{/lang}</p>
			</div>
		</div>
	</div>
	<div class="border borderMarginRemove" id="dependentPackages">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th colspan="2"><div><span class="emptyHead">{lang}wcf.acp.package.list.id{/lang}</span></div></th>
					<th colspan="2"><div><span class="emptyHead">{lang}wcf.acp.package.list.name{/lang}</span></div></th>
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.author{/lang}</span></div></th>
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.version{/lang}</span></div></th>
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.date{/lang}</span></div></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$dependentPackages item=$package}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
							<a href="index.php?form=PackageStartInstall&amp;action=update&amp;activePackageID={@$package.packageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/packageUpdateS.png" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/packageUpdateDisabledS.png" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" />
						{/if}
						{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $package.package != 'com.woltlab.wcf' && $package.packageID != PACKAGE_ID}
							<a onclick="return confirm('{lang}wcf.acp.package.view.button.uninstall.sure{/lang}')" href="index.php?page=Package&amp;action=startUninstall&amp;activePackageID={@$package.packageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" /></a>
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
					<td class="columnText" title="{$package.packageDescription}"><p><a href="index.php?page=PackageView&amp;activePackageID={@$package.packageID}{@SID_ARG_2ND}">{$package.packageName}{if $package.instanceNo > 1 && $package.instanceName == ''} (#{#$package.instanceNo}){/if}</a></p></td>
					<td class="columnText">{if $package.authorURL}<p><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$package.authorURL|rawurlencode}" class="externalURL">{$package.author}</a>{else}{$package.author}</p>{/if}</td>
					<td class="columnText"><p>{$package.packageVersion}</p></td>
					<td class="columnDate"><p>{@$package.packageDate|date}</p></td>
					
					{if $package.additionalColumns|isset}{@$package.additionalColumns}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	
	<script type="text/javascript">
		//<![CDATA[
		initList('dependentPackages', 0);
		//]]>
	</script>
{/if}

{include file='footer'}
