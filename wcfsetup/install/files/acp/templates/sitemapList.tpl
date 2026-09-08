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

<woltlab-core-notice type="info">{lang}wcf.acp.sitemap.cliInfo{/lang}</woltlab-core-notice>

{if $sitemapObjects|count}
	<div class="section tabularBox">
		<table id="sitemapObjectList" class="table">
			<thead>
				<tr>
					<th class="columnTitle columnSitemap" colspan="2">{lang}wcf.acp.sitemap{/lang}</th>
					<th class="columnInteger columnPriority">{lang}wcf.acp.sitemap.priority{/lang}</th>
					<th class="columnText columnChangeFreq">{lang}wcf.acp.sitemap.changeFreq{/lang}</th>
					<th class="columnInteger columnRebuildTime">{lang}wcf.acp.sitemap.rebuildTime{/lang}</th>
					
					{event name='headColumns'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$sitemapObjects key=objectName item=object}
					<tr class="sitemapObjectRow">
						<td class="columnIcon">
							<woltlab-core-toggle-button
								data-interaction="sitemapObjectToggle"
								aria-label="{lang}wcf.global.button.enable{/lang}"
								data-enable-endpoint="{$sitemapData[$objectName]['enableEndpoint']}"
								data-disable-endpoint="{$sitemapData[$objectName]['disableEndpoint']}"
								{if !$sitemapData[$objectName]['isDisabled']}checked{/if}
							></woltlab-core-toggle-button>
							<a href="{link controller="SitemapEdit"}objectType={$objectName}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip">{icon name='pencil'}</a>
						</td>
						<td class="columnTitle columnSitemap"><a href="{link controller="SitemapEdit"}objectType={$objectName}{/link}">{$object->getName()}</a></td>
						<td class="columnInteger columnPriority">{$object->getPriority()}</td>
						<td class="columnText columnChangeFreq">{lang}wcf.acp.sitemap.changeFreq.{$sitemapData[$objectName]['changeFreq']}{/lang}</td>
						<td class="columnInteger columnRebuildTime">{dateInterval end=TIME_NOW+$sitemapData[$objectName]['rebuildTime'] full=true format='plain'}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}{event name='contentFooterNavigation'}{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
	
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Component/Interaction/Toggle'], ({ setup }) => {
			setup('sitemapObjectToggle', document.getElementById('sitemapObjectList'));
		});
	</script>
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

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
