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
	
	<dl>
		<dt>{lang}wcf.acp.package.view.identifier{/lang}</dt>
		<dd>{$package->package}</dd>
	</dl>
	<dl>
		<dt>{lang}wcf.acp.package.view.version{/lang}</dt>
		<dd>{$package->packageVersion}</dd>
	</dl>
	{if $package->instanceNo > 1}
		<dl>
			<dt>{lang}wcf.acp.package.view.instanceNo{/lang}</dt>
			<dd>{#$package->instanceNo}</dd>
		</dl>
	{elseif $package->package == 'com.woltlab.wcf' && WCF_N != 1}
		<dl>
			<dt>{lang}wcf.acp.package.view.instanceNo{/lang}</dt>
			<dd>{#WCF_N}</dd>
		</dl>
	{/if}
	{if $package->packageDir != ''}
		<dl>
			<dt>{lang}wcf.acp.package.view.dir{/lang}</dt>
			<dd><a href="{@RELATIVE_WCF_DIR}{$package->packageDir}">{$package->packageDir}</a></dd>
		</dl>
	{elseif $package->package == 'com.woltlab.wcf'}
		<dl>
			<dt>{lang}wcf.acp.package.view.dir{/lang}</dt>
			<dd>{WCF_DIR}</dd>
		</dl>
	{/if}
	<dl>
		<dt>{lang}wcf.acp.package.view.date{/lang}</dt>
		<dd>{@$package->packageDate|date}</dd>
	</dl>
	<dl>
		<dt>{lang}wcf.acp.package.installDate{/lang}</dt>
		<dd>{@$package->installDate|time}</dd>
	</dl>
	<dl>
		<dt>{lang}wcf.acp.package.updateDate{/lang}</dt>
		<dd>{@$package->updateDate|time}</dd>
	</dl>
	{if $package->packageURL != ''}
		<dl>
			<dt>{lang}wcf.acp.package.view.url{/lang}</dt>
			<dd><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$package->packageURL|rawurlencode}" class="externalURL">{$package->packageURL}</a></dd>
		</dl>
	{/if}
	{if $package->parentPackageID}
		<dl>
			<dt>{lang}wcf.acp.package.view.parent{/lang}</dt>
			<dd><a href="index.php?page=PackageView&amp;packageID={@$package->parentPackageID}{@SID_ARG_2ND}">{$package->parentPackage()->getName()}</a></dd>
		</dl>
	{/if}
	<dl>
		<dt>{lang}wcf.acp.package.view.author{/lang}</dt>
		<dd>{if $package->authorURL}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$package->authorURL|rawurlencode}" class="externalURL">{$package->author}</a>{else}{$package->author}{/if}</dd>
	</dl>
	
	{if $additionalFields|isset}{@$additionalFields}{/if}
</fieldset>

{assign var=noDependentIsActive value=true}
{foreach from=$dependentPackages item=dependentPackage}
	{if $dependentPackage.package != 'com.woltlab.wcf' && $dependentPackage.packageID == PACKAGE_ID}
		{assign var=noDependentIsActive value=false}
		{* TODO: maybe show users that this package can't be uninstalled because a dependent package is the active standalone application *}
	{/if}
{/foreach}

<div class="contentHeader">
	<nav class="largeButtons">
		<ul>
			{if PACKAGE_ID != $package->packageID}
				{if $package->standalone && $package->package != 'com.woltlab.wcf'}<li><a href="{@RELATIVE_WCF_DIR}{$package->packageDir}acp/index.php{@SID_ARG_1ST}" title="{lang}wcf.acp.package.view.button.makeActive{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/packageMakeActiveM.png" alt="" /> <span>{lang}wcf.acp.package.view.button.makeActive{/lang}</span></a></li>{/if}
				{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $noDependentIsActive}<li><a href="index.php?page=Package&amp;action=startUninstall&amp;packageID={@$package->packageID}{@SID_ARG_2ND}" onclick="return confirm('{lang}wcf.acp.package.view.button.uninstall.sure{/lang}')" title="{lang}wcf.acp.package.view.button.uninstall{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/packageUninstallM.png" alt="" /> <span>{lang}wcf.acp.package.view.button.uninstall{/lang}</span></a></li>{/if}
			{/if}
			{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}<li><a href="index.php?form=PackageStartInstall&amp;action=update&amp;packageID={@$package->packageID}{@SID_ARG_2ND}" title="{lang}wcf.acp.package.view.button.update{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/packageUpdateM.png" alt="" /> <span>{lang}wcf.acp.package.view.button.update{/lang}</span></a></li>{/if}
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

{if $requiredPackages|count > 0}
	<div class="border boxTitle">
		<a onclick="openList('requiredPackages')"><img id="requiredPackagesImage" src="{@RELATIVE_WCF_DIR}icon/minusS.png" alt="" /></a>
		<hgroup>
			<h1><a onclick="openList('requiredPackages')">{lang}wcf.acp.package.view.requiredPackages{/lang}</a></h1>
			<h2>{lang}wcf.acp.package.view.requiredPackages.description{/lang}</h2>
		</hgroup>
		
		<table id="requiredPackages">
			<thead>
				<tr class="tableHead">
					<th colspan="2"><p class="emptyHead">{lang}wcf.acp.package.list.id{/lang}</p></th>
					<th colspan="2"><p class="emptyHead">{lang}wcf.acp.package.list.name{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.package.list.author{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.package.list.version{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.package.list.date{/lang}</p></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			
			<tbody>
			{foreach from=$requiredPackages item=$package}
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
	<div class="border boxTitle">
		<a onclick="openList('dependentPackages')"><img id="dependentPackagesImage" src="{@RELATIVE_WCF_DIR}icon/minusS.png" alt="" /></a>
		<hgroup>
			<h1><a onclick="openList('dependentPackages')">{lang}wcf.acp.package.view.dependentPackages{/lang}</a></h1>
			<h2>{lang}wcf.acp.package.view.dependentPackages.description{/lang}</h2>
		</hgroup>
		
		<table id="dependentPackages">
			<thead>
				<tr class="tableHead">
					<th colspan="2"><p class="emptyHead">{lang}wcf.acp.package.list.id{/lang}</p></th>
					<th colspan="2"><p class="emptyHead">{lang}wcf.acp.package.list.name{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.package.list.author{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.package.list.version{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.package.list.date{/lang}</p></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			
			<tbody>
			{foreach from=$dependentPackages item=$package}
				<tr>
					<td class="columnIcon">
						{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
							<a href="index.php?form=PackageStartInstall&amp;action=update&amp;packageID={@$package.packageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/packageUpdateS.png" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" class="balloonTooltip" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/packageUpdateDisabledS.png" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" />
						{/if}
						{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $package.package != 'com.woltlab.wcf' && $package.packageID != PACKAGE_ID}
							<a onclick="return confirm('{lang}wcf.acp.package.view.button.uninstall.sure{/lang}')" href="index.php?page=Package&amp;action=startUninstall&amp;packageID={@$package.packageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" class="balloonTooltip" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" />
						{/if}
						
						{if $package.additionalButtons|isset}{@$package.additionalButtons}{/if}
					</td>
					<td class="columnID"><p>{@$package.packageID}</p></td>
					<td class="columnIcon">
						{if $package.standalone}
							<img src="{@RELATIVE_WCF_DIR}icon/packageTypeStandaloneS.png" alt="" title="{lang}wcf.acp.package.list.standalone{/lang}" class="balloonTooltip" />
						{elseif $package.parentPackageID}
							<img src="{@RELATIVE_WCF_DIR}icon/packageTypePluginS.png" alt="" title="{lang}wcf.acp.package.list.plugin{/lang}" class="balloonTooltip" />
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/packageS.png" alt="" title="{lang}wcf.acp.package.list.other{/lang}" class="balloonTooltip" />
						{/if}
					</td>
					<td class="columnText" title="{$package.packageDescription}"><p><a href="index.php?page=PackageView&amp;packageID={@$package.packageID}{@SID_ARG_2ND}">{$package.packageName}{if $package.instanceNo > 1 && $package.instanceName == ''} (#{#$package.instanceNo}){/if}</a></p></td>
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
