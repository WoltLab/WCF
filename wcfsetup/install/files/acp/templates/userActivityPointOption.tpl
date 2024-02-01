{include file='header' pageTitle='wcf.acp.user.activityPoint.option'}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Worker'], function (Language, AcpUiWorker) {
		Language.add('wcf.acp.worker.abort.confirmMessage', '{jslang}wcf.acp.worker.abort.confirmMessage{/jslang}');
		
		document.getElementById('updateEvents').addEventListener('click', () => {
			new AcpUiWorker({
				dialogId: 'events',
				dialogTitle: '{jslang}wcf.acp.user.activityPoint.updateEvents{/jslang}',
				className: 'wcf\\system\\worker\\UserActivityPointUpdateEventsWorker',
			});
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.activityPoint.option{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a id="updateEvents" class="button">{icon name='arrow-rotate-right'} <span>{lang}wcf.acp.user.activityPoint.updateEvents{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formError'}

{if $success|isset}
	<woltlab-core-notice type="success">{lang}wcf.global.success.edit{/lang}</woltlab-core-notice>
{/if}

<form method="post" action="{link controller='UserActivityPointOption'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.user.activityPoint.pointsPerObject{/lang}</h2>
		{foreach from=$objectTypes item='objectType'}
			<dl{if $errorField == $objectType->objectTypeID} class="formError"{/if}>
				<dt><label for="{@$objectType->objectType}">{lang}wcf.user.activityPoint.objectType.{$objectType->objectType}{/lang}</label></dt>
				<dd>
					<input type="number" id="{@$objectType->objectType}" name="points[{@$objectType->objectTypeID}]" value="{$points[$objectType->objectTypeID]}" required min="0" class="tiny">
					{if $errorField == $objectType->objectTypeID}
						<small class="innerError">
							{lang greaterThan=-1}wcf.global.form.error.greaterThan{/lang}
						</small>
					{/if}
				</dd>
			</dl>
		{/foreach}
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
