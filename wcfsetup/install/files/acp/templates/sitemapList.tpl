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

<div class="info">{lang}wcf.acp.sitemap.submitToSearchEngines{/lang}</div>

<p class="info">{lang}wcf.acp.sitemap.cliInfo{/lang}</p>

{if $sitemapObjectTypes|count}
	<div class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\object\type\SitemapObjectTypeAction">
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
				{foreach from=$sitemapObjectTypes item=object}
					<tr class="sitemapObjectRow jsObjectActionObject" data-object-id="{@$object->getObjectID()}">
						<td class="columnIcon">
							{if $sitemapData[$object->objectType]['isDisabled']}
								{objectAction action="toggle" isDisabled=true}
							{else}
								{objectAction action="toggle" isDisabled=false}
							{/if}
							<a href="{link controller="SitemapEdit"}objectType={$object->objectType}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip">{icon name='pencil'}</a>
						</td>
						<td class="columnTitle columnSitemap"><a href="{link controller="SitemapEdit"}objectType={$object->objectType}{/link}">{lang}wcf.acp.sitemap.objectType.{$object->objectType}{/lang}</a></td>
						<td class="columnInteger columnPriority">{$object->priority}</td>
						<td class="columnText columnChangeFreq">{lang}wcf.acp.sitemap.changeFreq.{$sitemapData[$object->objectType]['changeFreq']}{/lang}</td>
						<td class="columnInteger columnRebuildTime">{dateInterval end=TIME_NOW+$sitemapData[$object->objectType]['rebuildTime'] full=true format='plain'}</td>
						
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
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Worker'], function (Language, AcpUiWorker) {
		Language.add('wcf.acp.worker.abort.confirmMessage', '{jslang}wcf.acp.worker.abort.confirmMessage{/jslang}');
		
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
