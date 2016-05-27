{include file='header' pageTitle='wcf.acp.rebuildData'}

<script data-relocate="true">
	//<![CDATA[
		$(function() {
			WCF.Language.add('wcf.acp.worker.abort.confirmMessage', '{lang}wcf.acp.worker.abort.confirmMessage{/lang}');
		});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.rebuildData{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{if $showInnoDBWarning}
	<p class="warning">{lang}wcf.acp.index.innoDBWarning{/lang}</p>
{/if}

<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.acp.rebuildData{/lang}</h2>
		<small class="sectionDescription">{lang}wcf.acp.rebuildData.description{/lang}</small>
	</header>
	
	{foreach from=$objectTypes item=objectType}
		<dl class="wide">
			<dd>
				<a class="button small" id="rebuildData{@$objectType->objectTypeID}">{lang}wcf.acp.rebuildData.{@$objectType->objectType}{/lang}</a>
				<small>{lang}wcf.acp.rebuildData.{@$objectType->objectType}.description{/lang}</small>
				
				<script data-relocate="true">
					//<![CDATA[
					$(function() {
						$('#rebuildData{@$objectType->objectTypeID}').click(function () {
							new WCF.ACP.Worker('cache', '{@$objectType->className|encodeJS}', '{lang}wcf.acp.rebuildData.{@$objectType->objectType}{/lang}');
						});
					});
					//]]>
				</script>
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
