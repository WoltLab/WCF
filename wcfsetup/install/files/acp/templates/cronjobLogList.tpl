{include file='header' pageTitle='wcf.acp.cronjob.log'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Api/Cronjobs/Logs/ClearLogs', 'WoltLabSuite/Core/Component/Snackbar', 'WoltLabSuite/Core/Component/Confirmation'], ({ clearLogs }, { showDefaultSuccessSnackbar }, { confirmationFactory }) => {
		document.querySelectorAll('.jsCronjobLogDelete').forEach((button) => {
			button.addEventListener('click', async () => {
				const result = await confirmationFactory()
					.custom('{jslang}wcf.acp.cronjob.log.clear.confirm{/jslang}')
					.withoutMessage();
					
				if (result) {
					await clearLogs();

					showDefaultSuccessSnackbar().addEventListener("snackbar:close", () => {
						window.location.reload();
					});
				}
			});
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.cronjob.log{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $gridView->countRows()}
						<li><a title="{lang}wcf.acp.cronjob.log.clear{/lang}" class="button jsCronjobLogDelete">{icon name='xmark'} <span>{lang}wcf.acp.cronjob.log.clear{/lang}</span></a></li>
					{/if}
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
