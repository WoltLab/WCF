{include file='header' pageTitle='wcf.acp.menu.link.maintenance.sitemap'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.maintenance.sitemap{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li><button type="button" class="button" id="sitemapRebuildButton">{icon name='arrows-rotate'} <span>{lang}wcf.acp.rebuildData.wcf_system_worker_SitemapRebuildWorker{/lang}</span></button></li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<woltlab-core-notice type="info">{lang}wcf.acp.sitemap.submitToSearchEngines{/lang}</woltlab-core-notice>

<div class="section">
	{unsafe:$gridView->render()}
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Acp/Ui/Worker'], function (AcpUiWorker) {
		{jsphrase name='wcf.acp.worker.abort.confirmMessage'}
		
		document.getElementById('sitemapRebuildButton').addEventListener('click', () => {
			new AcpUiWorker({
				dialogId: 'sitemapRebuild',
				dialogTitle: '{jslang}wcf.acp.rebuildData.wcf_system_worker_SitemapRebuildWorker{/jslang}',
				className: 'wcf\\system\\worker\\SitemapRebuildWorker',
				parameters: {
					forceRebuild: true,
				},
			});
		});
	});
</script>

{include file='footer'}
