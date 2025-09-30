{include file='header' pageTitle='wcf.acp.package.list'}

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.package.searchForUpdates': '{jslang}wcf.acp.package.searchForUpdates{/jslang}',
			'wcf.acp.package.searchForUpdates.noResults': '{jslang}wcf.acp.package.searchForUpdates.noResults{/jslang}',
			'wcf.acp.package.uninstallation.title': '{jslang}wcf.acp.package.uninstallation.title{/jslang}',
		});
		
		{if $__wcf->session->getPermission('admin.configuration.package.canInstallPackage')}
			new WCF.ACP.Package.Uninstallation($('.jsPackageRow .jsUninstallButton'));
		{/if}
		
		{if $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage')}
			new WCF.ACP.Package.Update.Search(true);
		{/if}
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.package.list{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.configuration.package.canInstallPackage')}
						<li>
							<a href="{link controller='License'}{/link}" class="button">
								{icon name='cart-arrow-down'}
								<span>{lang}wcf.acp.license{/lang}</span>
							</a>
						</li>
					{/if}

					{if $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage')}
						<li><button type="button" class="button jsButtonSearchForUpdates">{icon name='arrows-rotate'} <span>{lang}wcf.acp.package.searchForUpdates{/lang}</span></button></li>
					{/if}

					{if $__wcf->session->getPermission('admin.configuration.package.canInstallPackage')}
						<li><a href="{link controller='PackageStartInstall'}action=install{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>
					{/if}
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{if !(80100 <= PHP_VERSION_ID && PHP_VERSION_ID <= 80399)}
	<woltlab-core-notice type="error">{lang}wcf.global.incompatiblePhpVersion{/lang}</woltlab-core-notice>
{/if}
{foreach from=$taintedApplications item=$taintedApplication}
	<woltlab-core-notice type="error">{lang}wcf.acp.package.application.isTainted{/lang}</woltlab-core-notice>
{/foreach}

{if $recentlyDisabledCustomValues > 0}
	<woltlab-core-notice type="warning">{lang}wcf.acp.language.item.hasRecentlyDisabledCustomValues{/lang}</woltlab-core-notice>
{/if}

{if $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage')}
	{if $availableUpgradeVersion !== null}
		{if $upgradeOverrideEnabled}
			<woltlab-core-notice type="info">{lang version=$availableUpgradeVersion}wcf.acp.package.upgradeOverrideEnabled{/lang}</woltlab-core-notice>
		{else}
			<woltlab-core-notice type="info">{lang version=$availableUpgradeVersion}wcf.acp.package.availableUpgradeVersion{/lang}</woltlab-core-notice>
		{/if}
	{/if}
{/if}

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
