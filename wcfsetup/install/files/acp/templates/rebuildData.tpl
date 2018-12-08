{include file='header' pageTitle='wcf.acp.rebuildData'}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Worker'], function (Language, AcpUiWorker) {
		Language.add('wcf.acp.worker.abort.confirmMessage', '{lang}wcf.acp.worker.abort.confirmMessage{/lang}');
		
		elBySelAll('.jsRebuildDataWorker', undefined, function (button) {
			if (button.classList.contains('disabled')) return;
			
			button.addEventListener(WCF_CLICK_EVENT, function (event) {
				event.preventDefault();
				
				new AcpUiWorker({
					// dialog
					dialogId: 'cache',
					dialogTitle: button.textContent,
					
					// ajax
					className: elData(button, 'class-name'),
					loopCount: -1,
					parameters: { },
					
					// callbacks
					callbackAbort: null,
					callbackFailure: null,
					callbackSuccess: function() {
						{if $convertEncoding}
							var span = button.nextElementSibling;
							if (span && span.nodeName === 'SPAN') elRemove(span);
								
							span = elCreate('span');
							span.innerHTML = ' <span class="icon icon16 fa-check green"></span> {lang}wcf.acp.worker.success{/lang}';
							button.parentNode.insertBefore(span, button.nextElementSibling);
						{else}
							// force reload after converting the database encoding
							window.location.reload();
						{/if}
					}
				});
			});
		});
	});
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

{event name='afterContentHeader'}

<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.acp.rebuildData{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.acp.rebuildData.description{/lang}</p>
	</header>
	
	{foreach from=$objectTypes item=objectType}
		{assign var=_allowRebuild value=true}
		{if !$convertEncoding && $objectType->objectType != 'com.woltlab.wcf.databaseConvertEncoding'}
			{assign var=_allowRebuild value=false}
		{/if}
		
		<dl class="wide">
			<dd>
				<a href="#"
				   class="button small jsRebuildDataWorker{if !$_allowRebuild} disabled{/if}"
				   data-class-name="{$objectType->className}" data-object-type="{$objectType->objectType}"
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
