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
			<li><button class="button jsRebuildAll">{icon name='down-long'} <span>{lang}wcf.acp.rebuildData.rebuildAll{/lang}</span></button></li>
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
	
	{assign var='offset' value=0}
	{foreach from=$workers item=worker}
		<dl class="wide">
			<dd>
				<button
				   class="button small jsRebuildDataWorker"
				   data-nicevalue="{$offset}"
				   data-class-name="{$worker->getClassName()}"
				>{$worker->getName()}</button>
				<small>{$worker->getDescription()}</small>
			</dd>
		</dl>
		{assign var='offset' value=$offset+1}
	{/foreach}
</section>

<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.acp.rebuildData.cli{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.acp.rebuildData.cli.description{/lang}</p>
	</header>

	<textarea class="monospace" cols="40" rows="15">{implode from=$workers item='worker' glue="\n"}worker {$worker->getEncodedCliClassName()}{/implode}</textarea>
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
