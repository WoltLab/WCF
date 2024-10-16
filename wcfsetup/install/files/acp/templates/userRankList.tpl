{include file='header' pageTitle='wcf.acp.user.rank.list'}

<script data-relocate="true">
	require(['Language', 'Ui/Notification', 'WoltLabSuite/Core/Acp/Ui/Worker'], function (Language, UiNotification, AcpUiWorker) {
		Language.add('wcf.acp.worker.abort.confirmMessage', '{jslang}wcf.acp.worker.abort.confirmMessage{/jslang}');
		
		document.getElementById('updateEvents').addEventListener('click', function (event) {
			event.preventDefault();
			
			new AcpUiWorker({
				dialogId: 'updateEvents',
				dialogTitle: '{jslang}wcf.acp.user.activityPoint.updateEvents{/jslang}',
				className: 'wcf\\system\\worker\\UserActivityPointUpdateEventsWorker',
				callbackSuccess: () => UiNotification.show()
			});
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.rank.list{/lang} <span class="badge badgeInverse">{#$gridView->countRows()}</span></h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a id="updateEvents" class="button">{icon name='arrow-rotate-right'} <span>{lang}wcf.acp.user.activityPoint.updateEvents{/lang}</span></a></li>
			<li><a href="{link controller='UserRankAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.user.rank.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{unsafe:$gridView->render()}

{include file='footer'}
