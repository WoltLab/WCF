{include file='header' pageTitle='wcf.acp.menu.link.maintenance.sitemap'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Toggle('wcf\\data\\object\\type\\SitemapObjectTypeAction', '.sitemapObjectRow');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.maintenance.sitemap{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li><button id="sitemapRebuildButton"><span class="icon icon16 fa-refresh"></span> <span>{lang}wcf.acp.rebuildData.com.woltlab.wcf.sitemap{/lang}</span></button></li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="info">{lang}wcf.acp.sitemap.submitToSearchEngines{/lang}</div>

<p class="info">{lang}wcf.acp.sitemap.cliInfo{/lang}</p>

{if $sitemapObjectTypes|count}
	<div class="section tabularBox">
		<table class="table">
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
					<tr class="sitemapObjectRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-{if !$object->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$object->isDisabled}disable{else}enable{/if}{/lang}" data-object-id="{@$object->objectTypeID}"></span>
							<a href="{link controller="SitemapEdit"}objectType={$object->objectType}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
						</td>
						<td class="columnTitle columnSitemap"><a href="{link controller="SitemapEdit"}objectType={$object->objectType}{/link}">{lang}wcf.acp.sitemap.objectType.{$object->objectType}{/lang}</a></td>
						<td class="columnInteger columnPriority">{$object->priority}</td>
						<td class="columnText columnChangeFreq">{lang}wcf.acp.sitemap.changeFreq.{$object->changeFreq}{/lang}</td>
						<td class="columnInteger columnRebuildTime">{dateInterval end=TIME_NOW+$object->rebuildTime full=true format='plain'}</td>
						
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
	require(['Language'], function(Language) {
		Language.add('wcf.acp.worker.abort.confirmMessage', '{lang}wcf.acp.worker.abort.confirmMessage{/lang}');
		
		elById('sitemapRebuildButton').addEventListener(WCF_CLICK_EVENT, function () {
			new WCF.ACP.Worker('sitemapRebuild', 'wcf\\system\\worker\\SitemapRebuildWorker', '{lang}wcf.acp.rebuildData.com.woltlab.wcf.sitemap{/lang}', {
				forceRebuild: true
			});
		});
	});
</script>

{include file='footer'}
