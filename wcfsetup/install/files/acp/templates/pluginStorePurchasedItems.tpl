{include file='header' pageTitle='wcf.acp.pluginStore.purchasedItems'}

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.package.install.title': '{jslang}wcf.acp.package.install.title{/jslang}',
			'wcf.acp.package.searchForUpdates': '{jslang}wcf.acp.package.searchForUpdates{/jslang}',
			'wcf.acp.package.searchForUpdates.noResults': '{jslang}wcf.acp.package.searchForUpdates.noResults{/jslang}',
			'wcf.acp.package.update.unauthorized': '{jslang}wcf.acp.package.update.unauthorized{/jslang}'
		});
		
		var $installer = new WCF.ACP.Package.Server.Installation();
		$installer.bind();
		
		new WCF.ACP.Package.Update.Search(true);
	});
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.pluginStore.purchasedItems{/lang}</h1>
</header>

{if !$fetchedPackageServers}
	<p class="warning">{lang}wcf.acp.pluginStore.purchasedItems.updateServer.requireUpdate{/lang}</p>
{/if}

<section class="section tabularBox">
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
			{foreach from=$productData item=product}
				<tr>
					<td class="columnIcon">
						{if $product[status] == 'install'}
							<button class="jsButtonPackageInstall jsTooltip" title="{lang}wcf.acp.package.button.installPackage{/lang}" data-confirm-message="{lang __encode=true}wcf.acp.pluginStore.purchasedItems.status.install.confirmMessage{/lang}" data-package="{$product[package]}" data-package-version="{$product[version][available]}">
								{icon name='plus'}
							</button>
						{elseif $product[status] == 'update'}
							<button class="jsButtonPackageUpdate jsTooltip" title="{lang}wcf.acp.pluginStore.purchasedItems.status.update{/lang}">
								{icon name='arrows-rotate'}
							</button>
						{elseif $product[status] == 'upToDate'}
							<span class="jsTooltip" title="{lang}wcf.acp.pluginStore.purchasedItems.status.upToDate{/lang}">
								{icon name='check'}
							</span>
						{elseif $product[status] == 'requireUpdate'}
							<span class="jsTooltip" title="{lang}wcf.acp.pluginStore.purchasedItems.status.requireUpdate{/lang}">
								{icon name='ban'}
							</span>
						{else}
							<span class="jsTooltip" title="{lang}wcf.acp.pluginStore.purchasedItems.status.unavailable{/lang}">
								{icon name='ban'}
							</span>
						{/if}
					</td>
					<td class="columnText"><a href="{$product[pluginStoreURL]}" class="externalURL">{$product[packageName]}</a></td>
					<td class="columnText">{if $product[authorURL]}<a href="{$product[authorURL]}" class="externalURL">{$product[author]}</a>{else}{$product[author]}{/if}</td>
					<td class="columnText">{$product[version][available]}</td>
					<td class="columnText">{if $product[version][installed]}{$product[version][installed]}{else}-{/if}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</section>

{include file='footer'}
