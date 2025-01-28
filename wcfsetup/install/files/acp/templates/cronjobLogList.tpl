{include file='header' pageTitle='wcf.acp.cronjob.log'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Api/Cronjobs/Logs/ClearLogs', 'Ui/Notification', 'WoltLabSuite/Core/Component/Confirmation'], ({ clearLogs }, UiNotification, { confirmationFactory }) => {
		document.querySelectorAll('.jsCronjobLogDelete').forEach((button) => {
			button.addEventListener('click', async () => {
				const result = await confirmationFactory()
					.custom('{jslang}wcf.acp.cronjob.log.clear.confirm{/jslang}')
					.withoutMessage();
					
				if (result) {
					const response = await clearLogs();
					if (response.ok) {
						UiNotification.show(undefined, () => {
							window.location.reload();
						});
					}
				}
			});
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.cronjob.log{/lang}{if $gridView->countRows()} <span class="badge badgeInverse">{#$gridView->countRows()}</span>{/if}</h1>
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
