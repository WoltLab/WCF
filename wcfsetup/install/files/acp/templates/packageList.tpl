{include file='header' pageTitle='wcf.acp.package.list'}

<style>
	.success.upgradeAvailable::after { content: "\f01b" !important; }
</style>

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.package.searchForUpdates': '{lang}wcf.acp.package.searchForUpdates{/lang}',
			'wcf.acp.package.searchForUpdates.noResults': '{lang}wcf.acp.package.searchForUpdates.noResults{/lang}',
			'wcf.acp.package.uninstallation.title': '{lang}wcf.acp.package.uninstallation.title{/lang}',
			'wcf.acp.pluginStore.authorization': '{lang}wcf.acp.pluginStore.authorization{/lang}',
			'wcf.acp.pluginStore.purchasedItems': '{lang}wcf.acp.pluginStore.purchasedItems{/lang}',
			'wcf.acp.pluginStore.purchasedItems.button.search': '{lang}wcf.acp.pluginStore.purchasedItems.button.search{/lang}',
			'wcf.acp.pluginStore.purchasedItems.noResults': '{lang}wcf.acp.pluginStore.purchasedItems.noResults{/lang}'
		});
		
		{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage')}
			new WCF.ACP.Package.Uninstallation($('.jsPackageRow .jsUninstallButton'), {if PACKAGE_ID > 1}'{link controller='PackageList' forceWCF=true encode=false}packageID={literal}{packageID}{/literal}{/link}'{else}null{/if});
			{if $packageID}
				new WCF.PeriodicalExecuter(function(pe) {
					pe.stop();
					$('.jsUninstallButton[data-object-id={@$packageID}]').trigger('click');
				}, 250);
			{/if}
		{/if}
		
		{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
			new WCF.ACP.Package.Update.Search();
		{/if}
		
		{if $__wcf->session->getPermission('admin.system.package.canInstallPackage') && $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
			new WCF.ACP.PluginStore.PurchasedItems.Search();
		{/if}
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.package.list{/lang}</h1>
</header>

<p class="success upgradeAvailable">{lang}wcf.acp.package.upgradeAvailable{/lang}</p>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='PackageList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.system.package.canInstallPackage')}
						<li><a href="{link controller='PackageStartInstall'}action=install{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>
					{/if}
					
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.package.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
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
					<tr class="jsPackageRow">
						<td class="columnIcon">
							{if $package->canUninstall()}
								<span class="icon icon16 icon-remove pointer jsUninstallButton jsTooltip" title="{lang}wcf.acp.package.button.uninstall{/lang}" data-object-id="{@$package->packageID}" data-confirm-message="{lang}wcf.acp.package.uninstallation.confirm{/lang}" data-is-required="{if $package->isRequired()}true{else}false{/if}" data-is-application="{if $package->isApplication}true{else}false{/if}"></span>
							{else}
								<span class="icon icon16 icon-remove disabled" title="{lang}wcf.acp.package.button.uninstall{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$package->packageID}</td>
						<td id="packageName{@$package->packageID}" class="columnTitle" title="{$package->packageDescription|language}">
							<a href="{link controller='Package' id=$package->packageID}{/link}"><span>{$package}</span></a>
						</td>
						<td class="columnText">{if $package->authorURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$package->authorURL|rawurlencode}" class="externalURL">{$package->author}</a>{else}{$package->author}{/if}</td>
						<td class="columnText">{$package->packageVersion}</td>
						<td class="columnDate">{@$package->updateDate|time}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</div>
			
	<div class="contentNavigation">
		{@$pagesLinks}
		
		{hascontent}
			<script data-relocate="true">
				//<![CDATA[
				$(function() {
					new WCF.ACP.Package.Uninstallation($('.jsPluginContainer .jsUninstallButton'));
				});
				//]]>
			</script>
			
			<nav>
				<ul>
					{content}
						{if $__wcf->session->getPermission('admin.system.package.canInstallPackage')}
							<li><a href="{link controller='PackageStartInstall'}action=install{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>
						{/if}
						
						{event name='contentNavigationButtonsBottom'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</div>
{/if}

{include file='footer'}
