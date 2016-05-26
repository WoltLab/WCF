{include file='header' pageTitle='wcf.acp.pluginStore.purchasedItems'}

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.package.install.title': '{lang}wcf.acp.package.install.title{/lang}',
			'wcf.acp.package.searchForUpdates': '{lang}wcf.acp.package.searchForUpdates{/lang}',
			'wcf.acp.package.searchForUpdates.noResults': '{lang}wcf.acp.package.searchForUpdates.noResults{/lang}',
			'wcf.acp.package.update.unauthorized': '{lang}wcf.acp.package.update.unauthorized{/lang}'
		});
		
		var $installer = new WCF.ACP.Package.Server.Installation();
		$installer.bind();
		
		new WCF.ACP.Package.Update.Search(true);
	});
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.pluginStore.purchasedItems{/lang}</h1>
</header>

{foreach from=$wcfMajorReleases item=wcfMajorRelease}
	{if !$productData[$wcfMajorRelease]|empty}
		{if !$updateServers[$wcfMajorRelease]|isset}
			<p class="warning">{lang}wcf.acp.pluginStore.purchasedItems.updateServer.missing{/lang}</p>
		{else if $updateServers[$wcfMajorRelease]->isDisabled}
			<p class="warning">{lang}wcf.acp.pluginStore.purchasedItems.updateServer.disabled{/lang}</p>
		{else if $updateServers[$wcfMajorRelease]->lastUpdateTime == 0}
			<p class="warning">{lang}wcf.acp.pluginStore.purchasedItems.updateServer.requireUpdate{/lang}</p>
		{/if}
		
		<div class="section tabularBox">
			<table class="table">
				<thead>
					<tr>
						<th class="columnText" colspan="2">{lang}wcf.acp.package.name{/lang}</th>
						<th class="columnText">{lang}wcf.acp.package.author{/lang}</th>
						<th class="columnText">{lang}wcf.acp.package.version{/lang}</th>
						<th class="columnText">{lang}wcf.acp.package.installedVersion{/lang}</th>
					</tr>
				</thead>
				
				<tbody>
					{foreach from=$productData[$wcfMajorRelease] item=product}
						<tr>
							<td class="columnIcon">
								{if $product[status] == 'install'}
									<a class="jsButtonPackageInstall" data-confirm-message="{lang __encode=true}wcf.acp.pluginStore.purchasedItems.status.install.confirmMessage{/lang}" data-package="{$product[package]}" data-package-version="{$product[version][available]}"><span class="icon icon16 fa-plus jsTooltip" title="{lang}wcf.acp.package.button.installPackage{/lang}"></span></a>
								{else if $product[status] == 'update'}
									<a class="jsButtonPackageUpdate"><span class="icon icon16 fa-refresh jsTooltip" title="{lang}wcf.acp.pluginStore.purchasedItems.status.update{/lang}"></span></a>
								{else if $product[status] == 'upToDate'}
									<span class="icon icon16 fa-check green jsTooltip" title="{lang}wcf.acp.pluginStore.purchasedItems.status.upToDate{/lang}"></span>
								{else if $product[status] == 'requireUpdate'}
									<span class="icon icon16 fa-ban red jsTooltip" title="{lang}wcf.acp.pluginStore.purchasedItems.status.requireUpdate{/lang}"></span>
								{else}
									<span class="icon icon16 fa-ban red jsTooltip" title="{lang}wcf.acp.pluginStore.purchasedItems.status.unavailable{/lang}"></span>
								{/if}
							</td>
							<td class="columnText"><a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$product[pluginStoreURL]|rawurlencode}" class="externalURL">{$product[packageName]}</a></td>
							<td class="columnText">{if $product[authorURL]}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$product[authorURL]|rawurlencode}" class="externalURL">{$product[author]}</a>{else}{$product[author]}{/if}</td>
							<td class="columnText">{$product[version][available]}</td>
							<td class="columnText">{if $product[version][installed]}{$product[version][installed]}{else}-{/if}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	{/if}
{/foreach}

{include file='footer'}
