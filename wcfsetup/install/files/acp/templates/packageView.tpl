{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init()
	});
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/packageStandalone1.svg" alt="" /><!-- ToDo: Add possibility to show a custom app icon if given! -->
	<hgroup>
		<h1>{$package->getName()}</h1>
		<h2>{$package->packageDescription}</h2>
	</hgroup>
</header>

<div class="tabMenuContainer">
	<nav class="tabMenu">
		<ul>
			<li><a href="#overview">overview</a></li>
			{if $requiredPackages|count || $dependentPackages|count}<li><a href="#dependencies">dependencies</a></li>{/if}
		</ul>
	</nav>

	<div id="overview" class="border tabMenuContent hidden">
		<hgroup class="subHeading">
			<h1>information</h1>
		</hgroup>

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
					<dd><a href="index.php/PackageView/{@$package->parentPackageID}/{@SID_ARG_1ST}">{$package->getParentPackage()->getName()}</a></dd>
				</dl>
			{/if}
			<dl>
				<dt>{lang}wcf.acp.package.view.author{/lang}</dt>
				<dd>{if $package->authorURL}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$package->authorURL|rawurlencode}" class="externalURL">{$package->author}</a>{else}{$package->author}{/if}</dd>
			</dl>
	
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</fieldset>

		{if $package->packageDescription}
			<hgroup class="subHeading">
				<h1>description</h1>
			</hgroup>

			<p>{$package->packageDescription}</p>
		{/if}
	</div>

	{if $requiredPackages|count || $dependentPackages|count}
		<div id="dependencies" class="tabMenuContainer border tabMenuContent">
			<nav class="menu">
				<ul>
					{if $requiredPackages|count}<li><a href="#dependencies-required">required</a></li>{/if}
					{if $dependentPackages|count}<li><a href="#dependencies-dependent">dependent</a></li>{/if}
				</ul>
			</nav>

			{hascontent}
				<div id="dependencies-required" class="hidden">
					<hgroup class="subHeading">
						<h1>{lang}wcf.acp.package.view.requiredPackages{/lang}</h1>
						<h2>{lang}wcf.acp.package.view.requiredPackages.description{/lang}</h2>
					</hgroup>
		
					<table>
						<thead>
							<tr class="tableHead">
								<th colspan="2" class="columnID">{lang}wcf.acp.package.list.id{/lang}</th>
								<th colspan="2" class="columnTitle">{lang}wcf.acp.package.list.name{/lang}</th>
								<th class="columnText">{lang}wcf.acp.package.list.author{/lang}</th>
								<th class="columnText">{lang}wcf.acp.package.list.version{/lang}</th>
								<th class="columnDigits">{lang}wcf.acp.package.list.date{/lang}</th>
					
								{if $additionalColumns|isset}{@$additionalColumns}{/if}
							</tr>
						</thead>
			
						<tbody>
							{content}
								{foreach from=$requiredPackages item=requiredPackage}
									<tr>
										<td class="columnIcon">
											{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
												<a href="index.php/PackageStartInstall/{@$requiredPackage.packageID}/?action=update{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" class="balloonTooltip" /></a>
											{else}
												<img src="{@RELATIVE_WCF_DIR}icon/update1D.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" />
											{/if}
											{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $requiredPackage.package != 'com.woltlab.wcf' && $requiredPackage.packageID != PACKAGE_ID}
												<a onclick="return confirm('{lang}wcf.acp.package.view.button.uninstall.sure{/lang}')" href="{link controller='Package'}action=startUninstall&packageID={@$requiredPackage.packageID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" class="balloonTooltip" /></a>
											{else}
												<img src="{@RELATIVE_WCF_DIR}icon/delete1D.svg" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" />
											{/if}
										</td>
										<td class="columnID"><p>{@$requiredPackage.packageID}</p></td>
										<td class="columnIcon">
											{if $requiredPackage.standalone}
												<img src="{@RELATIVE_WCF_DIR}icon/packageStandalone1.svg" alt="" title="{lang}wcf.acp.package.list.standalone{/lang}" class="balloonTooltip" />
											{elseif $requiredPackage.parentPackageID}
												<img src="{@RELATIVE_WCF_DIR}icon/packagePlugin1.svg" alt="" title="{lang}wcf.acp.package.list.plugin{/lang}" class="balloonTooltip" />
											{else}
												<img src="{@RELATIVE_WCF_DIR}icon/package1.svg" alt="" title="{lang}wcf.acp.package.list.other{/lang}" class="balloonTooltip" />
											{/if}
										</td>
										<td class="columnText" title="{$requiredPackage.packageDescription}"><p><a href="index.php/PackageView/{@$requiredPackage.packageID}/{@SID_ARG_1ST}">{$requiredPackage.packageName}{if $requiredPackage.instanceNo > 1 && $requiredPackage.instanceName == ''} (#{#$requiredPackage.instanceNo}){/if}</a></p></td>
										<td class="columnText">{if $requiredPackage.authorURL}<p><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$requiredPackage.authorURL|rawurlencode}" class="externalURL">{$requiredPackage.author}</a>{else}{$requiredPackage.author}</p>{/if}</td>
										<td class="columnText"><p>{$requiredPackage.packageVersion}</p></td>
										<td class="columnDate"><p>{@$requiredPackage.packageDate|date}</p></td>
									</tr>
								{/foreach}
							{/content}
						</tbody>
					</table>
				</div>
			{/hascontent}

			{hascontent}
				<div id="dependencies-dependent" class="hidden">
					<hgroup class="subHeading">
						<h1>{lang}wcf.acp.package.view.dependentPackages{/lang}</h1>
						<h2>{lang}wcf.acp.package.view.dependentPackages.description{/lang}</h2>
					</hgroup>
		
					<table>
						<thead>
							<tr class="tableHead">
								<th colspan="2">{lang}wcf.acp.package.list.id{/lang}</th>
								<th colspan="2">{lang}wcf.acp.package.list.name{/lang}</th>
								<th>{lang}wcf.acp.package.list.author{/lang}</th>
								<th>{lang}wcf.acp.package.list.version{/lang}</th>
								<th>{lang}wcf.acp.package.list.date{/lang}</th>
					
								{if $additionalColumns|isset}{@$additionalColumns}{/if}
							</tr>
						</thead>
			
						<tbody>
							{content}
								{foreach from=$dependentPackages item=dependentPackage}
									<tr>
										<td class="columnIcon">
											{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
												<a href="index.php/PackageStartInstall/{@$dependentPackage.packageID}/?action=update{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" class="balloonTooltip" /></a>
											{else}
												<img src="{@RELATIVE_WCF_DIR}icon/update1D.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" />
											{/if}
											{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $dependentPackage.package != 'com.woltlab.wcf' && $dependentPackage.packageID != PACKAGE_ID}
												<a onclick="return confirm('{lang}wcf.acp.package.view.button.uninstall.sure{/lang}')" href="{link controller='Package'}action=startUninstall&packageID={@$dependentPackage.packageID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" class="balloonTooltip" /></a>
											{else}
												<img src="{@RELATIVE_WCF_DIR}icon/delete1D.svg" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" />
											{/if}
										</td>
										<td class="columnID"><p>{@$dependentPackage.packageID}</p></td>
										<td class="columnIcon">
											{if $dependentPackage.standalone}
												<img src="{@RELATIVE_WCF_DIR}icon/packageStandalone1.svg" alt="" title="{lang}wcf.acp.package.list.standalone{/lang}" class="balloonTooltip" />
											{elseif $dependentPackage.parentPackageID}
												<img src="{@RELATIVE_WCF_DIR}icon/packagePlugin1.svg" alt="" title="{lang}wcf.acp.package.list.plugin{/lang}" class="balloonTooltip" />
											{else}
												<img src="{@RELATIVE_WCF_DIR}icon/package1.svg" alt="" title="{lang}wcf.acp.package.list.other{/lang}" class="balloonTooltip" />
											{/if}
										</td>
										<td class="columnText" title="{$dependentPackage.packageDescription}"><p><a href="index.php/PackageView/{@$dependentPackage.packageID}/{@SID_ARG_1ST}">{$dependentPackage.packageName}{if $dependentPackage.instanceNo > 1 && $dependentPackage.instanceName == ''} (#{#$dependentPackage.instanceNo}){/if}</a></p></td>
										<td class="columnText">{if $dependentPackage.authorURL}<p><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$dependentPackage.authorURL|rawurlencode}" class="externalURL">{$dependentPackage.author}</a>{else}{$dependentPackage.author}</p>{/if}</td>
										<td class="columnText"><p>{$dependentPackage.packageVersion}</p></td>
										<td class="columnDate"><p>{@$dependentPackage.packageDate|date}</p></td>
									</tr>
								{/foreach}
							{/content}
						</tbody>
					</table>
				</div>
			{/hascontent}
		</div>
	{/if}
</div>

{assign var=noDependentIsActive value=true}
{foreach from=$dependentPackages item=dependentPackage}
	{if $dependentPackage.package != 'com.woltlab.wcf' && $dependentPackage.packageID == PACKAGE_ID}
		{assign var=noDependentIsActive value=false}
		{* TODO: maybe show users that this package can't be uninstalled because a dependent package is the active standalone application *}
	{/if}
{/foreach}

<div class="contentFooter">
	<nav class="largeButtons">
		<ul>
			{if PACKAGE_ID != $package->packageID}
				{if $package->standalone && $package->package != 'com.woltlab.wcf'}<li><a href="{@RELATIVE_WCF_DIR}{$package->packageDir}acp/index.php{@SID_ARG_1ST}" title="{lang}wcf.acp.package.view.button.makeActive{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/packageACP1.svg" alt="" /> <span>{lang}wcf.acp.package.view.button.makeActive{/lang}</span></a></li>{/if}
				{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $noDependentIsActive}<li><a href="{link controller='Package'}action=startUninstall&packageID={@$package->packageID}{/link}" onclick="return confirm('{lang}wcf.acp.package.view.button.uninstall.sure{/lang}')" title="{lang}wcf.acp.package.view.button.uninstall{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" /> <span>{lang}wcf.acp.package.view.button.uninstall{/lang}</span></a></li>{/if}
			{/if}
			{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}<li><a href="index.php/PackageStartInstall/{@$package->packageID}/?action=update{@SID_ARG_2ND}" title="{lang}wcf.acp.package.view.button.update{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" /> <span>{lang}wcf.acp.package.view.button.update{/lang}</span></a></li>{/if}
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

{include file='footer'}
