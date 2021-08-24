{include file='header' pageTitle='wcf.acp.rebuildData'}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Maintenance/RebuildData'], (Language, RebuildData) => {
		Language.addObject({
			'wcf.acp.worker.abort.confirmMessage': '{jslang}wcf.acp.worker.abort.confirmMessage{/jslang}',
			'wcf.acp.worker.success': '{jslang}wcf.acp.worker.success{/jslang}',
		});
		
		document.querySelectorAll('.jsRebuildDataWorker').forEach((button) => {
			RebuildData.register(button);
		});
		document.querySelectorAll('.jsRebuildAll').forEach((button) => {
			button.addEventListener('click', (ev) => {
				ev.preventDefault();
				void RebuildData.runAllWorkers();
			});
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.rebuildData{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="#" class="button jsRebuildAll"><span class="icon icon16 fa-long-arrow-down"></span> <span>{lang}wcf.acp.rebuildData.rebuildAll{/lang}</span></a></li>
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if !OFFLINE}
	<p class="warning">{lang}wcf.acp.rebuildData.offline{/lang}</p>
{/if}

{event name='afterContentHeader'}

<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.acp.rebuildData{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.acp.rebuildData.description{/lang}</p>
	</header>
	
	{foreach from=$objectTypes item=objectType}
		<dl class="wide">
			<dd>
				<a href="#"
				   class="button small jsRebuildDataWorker"
				   data-class-name="{$objectType->className}"
				   data-object-type="{$objectType->objectType}"
				   data-nicevalue="{if $objectType->nicevalue}{$objectType->nicevalue}{else}0{/if}"
				>{lang}wcf.acp.rebuildData.{@$objectType->objectType}{/lang}</a>
				<small>{lang}wcf.acp.rebuildData.{@$objectType->objectType}.description{/lang}</small>
			</dd>
		</dl>
	{/foreach}
</section>

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
