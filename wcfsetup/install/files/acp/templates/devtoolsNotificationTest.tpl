{include file='header' pageTitle='wcf.acp.devtools.notificationTest'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.devtools.notificationTest{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<p class="warning">{lang}wcf.acp.devtools.notificationTest.contentCreationWarning{/lang}</p>

{foreach from=$events key='eventCategory' item='eventList'}
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.user.notification.{$eventCategory}{/lang}</h2>
		
		<dl>
			{foreach from=$eventList item=event}
				<dt>{lang}wcf.user.notification.{$event->objectType}.{$event->eventName}{/lang}</dt>
				<dd>
					<button class="small jsTestEventButton" data-event-id="{$event->eventID}" data-title="{lang}wcf.user.notification.{$event->objectType}.{$event->eventName}{/lang}">{lang}wcf.acp.devtools.notificationTest.button.test{/lang}</button>
				</dd>
			{/foreach}
		</dl>
	</section>
{/foreach}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Devtools/Notification/Test'], function(Language, AcpUiDevtoolsNotificationTest) {
		Language.addObject({
			'wcf.acp.devtools.notificationTest.button.test': '{jslang}wcf.acp.devtools.notificationTest.button.test{/jslang}'
		});
		
		AcpUiDevtoolsNotificationTest.init();
	});
</script>

{include file='footer'}
