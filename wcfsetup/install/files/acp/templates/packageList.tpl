{include file='header' pageTitle='wcf.acp.package.list'}

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.package.searchForUpdates': '{jslang}wcf.acp.package.searchForUpdates{/jslang}',
			'wcf.acp.package.searchForUpdates.noResults': '{jslang}wcf.acp.package.searchForUpdates.noResults{/jslang}',
			'wcf.acp.package.uninstallation.title': '{jslang}wcf.acp.package.uninstallation.title{/jslang}',
			'wcf.acp.pluginStore.authorization': '{jslang}wcf.acp.pluginStore.authorization{/jslang}',
			'wcf.acp.pluginStore.purchasedItems': '{jslang}wcf.acp.pluginStore.purchasedItems{/jslang}',
			'wcf.acp.pluginStore.purchasedItems.button.search': '{jslang}wcf.acp.pluginStore.purchasedItems.button.search{/jslang}',
			'wcf.acp.pluginStore.purchasedItems.noResults': '{jslang}wcf.acp.pluginStore.purchasedItems.noResults{/jslang}'
		});
		
		{if $__wcf->session->getPermission('admin.configuration.package.canUninstallPackage')}
			new WCF.ACP.Package.Uninstallation($('.jsPackageRow .jsUninstallButton'));
		{/if}
		
		{if $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage')}
			new WCF.ACP.Package.Update.Search(true);
		{/if}
		
		{if $__wcf->session->getPermission('admin.configuration.package.canInstallPackage') && $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage')}
			new WCF.ACP.PluginStore.PurchasedItems.Search();
		{/if}
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.package.list{/lang} <span class="badge badgeInverse">{#$items}</span></h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage')}
						<li><a href="#" class="button jsButtonSearchForUpdates"><span class="icon icon16 fa-refresh"></span> <span>{lang}wcf.acp.package.searchForUpdates{/lang}</span></a></li>
					{/if}

					{if $__wcf->session->getPermission('admin.configuration.package.canInstallPackage')}
						<li><a href="{link controller='PackageStartInstall'}action=install{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>
					{/if}
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{if !(70200 <= PHP_VERSION_ID && PHP_VERSION_ID <= 80199)}
	<div class="error">{lang}wcf.global.incompatiblePhpVersion{/lang}</div>
{/if}
{foreach from=$taintedApplications item=$taintedApplication}
	<div class="error">{lang}wcf.acp.package.application.isTainted{/lang}</div>
{/foreach}

{if $recentlyDisabledCustomValues > 0}
	<p class="warning">{lang}wcf.acp.language.item.hasRecentlyDisabledCustomValues{/lang}</p>
{/if}

{if $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage')}
	{if $availableUpgradeVersion !== null}
		{if $upgradeOverrideEnabled}
			<p class="info">{lang version=$availableUpgradeVersion}wcf.acp.package.upgradeOverrideEnabled{/lang}</p>
		{else}
			<p class="info">{lang version=$availableUpgradeVersion}wcf.acp.package.availableUpgradeVersion{/lang}</p>
		{/if}
	{/if}
{/if}

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller='PackageList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th colspan="2" class="columnID{if $sortField == 'packageID'} active {@$sortOrder}{/if}"><a href="{link controller='PackageList'}pageNo={@$pageNo}&sortField=packageID&sortOrder={if $sortField == 'packageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle{if $sortField == 'packageName'} active {@$sortOrder}{/if}"><a href="{link controller='PackageList'}pageNo={@$pageNo}&sortField=packageName&sortOrder={if $sortField == 'packageName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.name{/lang}</a></th>
					<th class="columnText{if $sortField == 'author'} active {@$sortOrder}{/if}"><a href="{link controller='PackageList'}pageNo={@$pageNo}&sortField=author&sortOrder={if $sortField == 'author' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.author{/lang}</a></th>
					<th class="columnText">{lang}wcf.acp.package.version{/lang}</th>
					<th class="columnDate{if $sortField == 'updateDate'} active {@$sortOrder}{/if}"><a href="{link controller='PackageList'}pageNo={@$pageNo}&sortField=updateDate&sortOrder={if $sortField == 'updateDate' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.updateDate{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=$package}
					<tr class="jsPackageRow" data-package="{$package->package}">
						<td class="columnIcon">
							{if $package->canUninstall()}
								<span class="icon icon16 fa-times pointer jsUninstallButton jsTooltip" title="{lang}wcf.acp.package.button.uninstall{/lang}" data-object-id="{@$package->packageID}" data-confirm-message="{lang __encode=true}wcf.acp.package.uninstallation.confirm{/lang}" data-is-required="{if $package->isRequired()}true{else}false{/if}" data-is-application="{if $package->isApplication}true{else}false{/if}"></span>
							{else}
								<span class="icon icon16 fa-times disabled" title="{lang}wcf.acp.package.button.uninstall{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$package->packageID}</td>
						<td id="packageName{@$package->packageID}" class="columnTitle" title="{$package->getDescription()}">
							<a href="{link controller='Package' id=$package->packageID}{/link}"><span>{$package}</span></a>
							{if $taintedApplications[$package->packageID]|isset}
								<span
									class="icon icon16 fa-warning jsTooltip"
									title="{lang taintedApplication=null}wcf.acp.package.application.isTainted{/lang}"
								></span>
							{/if}
						</td>
						<td class="columnText">{if $package->authorURL}<a href="{$package->authorURL}" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank" rel="noopener"{/if}>{$package->author}</a>{else}{$package->author}{/if}</td>
						<td class="columnText">{$package->packageVersion}</td>
						<td class="columnDate">{@$package->updateDate|time}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}
						{if $__wcf->session->getPermission('admin.configuration.package.canInstallPackage')}
							<li><a href="{link controller='PackageStartInstall'}action=install{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>
						{/if}
						
						{event name='contentFooterNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{/if}

{include file='footer'}
