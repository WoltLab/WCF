{if $action == 'all'}
	{assign var='pageTitle' value='wcf.acp.user.sendMail.all'}
{elseif $action == 'group'}
	{assign var='pageTitle' value='wcf.acp.user.sendMail.group'}
{else}
	{assign var='pageTitle' value='wcf.acp.user.sendMail'}
{/if}

{include file='header'}

{if $mailID|isset}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Acp/Ui/Worker'], (AcpUiWorker) => {
			{jsphrase name='wcf.acp.worker.abort.confirmMessage'}
			
			new AcpUiWorker({
				dialogId: 'mail',
				dialogTitle: '{jslang}{$pageTitle}{/jslang}',
				className: 'wcf\\system\\worker\\MailWorker',
				parameters: {
					mailID: {$mailID},
				},
			});
		});
	</script>
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}{$pageTitle}{/lang}</h1>
	</div>
</header>

{unsafe:$form->getHtml()}

{include file='footer'}
