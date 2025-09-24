{include file='header' pageTitle='wcf.acp.user.rank.list'}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Component/Snackbar', 'WoltLabSuite/Core/Acp/Ui/Worker'], (Language, { showDefaultSuccessSnackbar }, AcpUiWorker) => {
		Language.add('wcf.acp.worker.abort.confirmMessage', '{jslang}wcf.acp.worker.abort.confirmMessage{/jslang}');
		
		document.getElementById('updateEvents').addEventListener('click', () => {
			new AcpUiWorker({
				dialogId: 'updateEvents',
				dialogTitle: '{jslang}wcf.acp.user.activityPoint.updateEvents{/jslang}',
				className: 'wcf\\system\\worker\\UserActivityPointUpdateEventsWorker',
				callbackSuccess: () => showDefaultSuccessSnackbar()
			});
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.rank.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><button type="button" id="updateEvents" class="button">{icon name='arrow-rotate-right'} <span>{lang}wcf.acp.user.activityPoint.updateEvents{/lang}</span></button></li>
			<li><a href="{link controller='UserRankAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.user.rank.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
