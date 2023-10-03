{include file='header'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.global.acp{/lang}</h1>
</header>

{if !(80100 <= PHP_VERSION_ID && PHP_VERSION_ID <= 80399)}
	<woltlab-core-notice type="error">{lang}wcf.global.incompatiblePhpVersion{/lang}</woltlab-core-notice>
{/if}
{foreach from=$evaluationExpired item=$expiredApp}
	<woltlab-core-notice type="error">{lang packageName=$expiredApp[packageName] isWoltLab=$expiredApp[isWoltLab] pluginStoreFileID=$expiredApp[pluginStoreFileID]}wcf.acp.package.evaluation.expired{/lang}</woltlab-core-notice>
{/foreach}
{foreach from=$evaluationPending key=$evaluationEndDate item=$pendingApps}
	<woltlab-core-notice type="warning">{lang evaluationEndDate=$evaluationEndDate}wcf.acp.package.evaluation.pending{/lang}</woltlab-core-notice>
{/foreach}

{foreach from=$taintedApplications item=$taintedApplication}
	<woltlab-core-notice type="error">{lang}wcf.acp.package.application.isTainted{/lang}</woltlab-core-notice>
{/foreach}

{if $systemIdMismatch}
	{if $__wcf->session->getPermission('admin.configuration.package.canInstallPackage') && (!ENABLE_ENTERPRISE_MODE || $__wcf->user->hasOwnerAccess())}
		<woltlab-core-notice type="info">{lang}wcf.acp.index.systemIdMismatch{/lang}</woltlab-core-notice>
	{/if}
{/if}

{if !VISITOR_USE_TINY_BUILD}
	<woltlab-core-notice type="info">{lang}wcf.acp.index.tinyBuild{/lang}</woltlab-core-notice>
{/if}

{if $missingLanguageItemsMTime}
	<woltlab-core-notice type="warning">{lang}wcf.acp.index.missingLanguageItems{/lang}</woltlab-core-notice>
{/if}

{event name='userNotice'}

<div class="acpDashboard">
	{foreach from=$dashboard->getVisibleBoxes() item='box'}
		<div class="acpDashboardBox">
			<h2 class="acpDashboardBox__title">{$box->getTitle()}</h2>
			<div class="acpDashboardBox__content">
				{@$box->getContent()}
			</div>
		</div>
	{/foreach}
</div>

{include file='footer'}
