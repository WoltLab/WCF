{include file='header' pageTitle='wcf.acp.rebuildData'}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Worker'], function (Language, AcpUiWorker) {
		Language.add('wcf.acp.worker.abort.confirmMessage', '{jslang}wcf.acp.worker.abort.confirmMessage{/jslang}');
		
		elBySelAll('.jsRebuildDataWorker', undefined, function (button) {
			if (button.classList.contains('disabled')) return;
			
			button.addEventListener('click', function (event) {
				event.preventDefault();
				
				new AcpUiWorker({
					// dialog
					dialogId: 'cache',
					dialogTitle: button.textContent,
					
					// ajax
					className: elData(button, 'class-name'),
					
					// callbacks
					callbackAbort: null,
					callbackSuccess: () => {
						var span = button.nextElementSibling;
						if (span && span.nodeName === 'SPAN') elRemove(span);
							
						span = elCreate('span');
						span.innerHTML = ' <span class="icon icon16 fa-check green"></span> {lang}wcf.acp.worker.success{/lang}';
						button.parentNode.insertBefore(span, button.nextElementSibling);
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
