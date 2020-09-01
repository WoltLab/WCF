{capture assign='pageTitle'}{lang}wcf.user.menu.settings{/lang}: {lang}wcf.user.notification.notifications{/lang} - {lang}wcf.user.menu.settings{/lang}{/capture}

{capture assign='contentTitle'}{lang}wcf.user.menu.settings{/lang}: {lang}wcf.user.notification.notifications{/lang}{/capture}

{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{include file='formError'}

{if $success|isset}
	<p class="success" role="status">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<form method="post" action="{link controller='NotificationSettings'}{/link}" id="notificationSettings">
	<div class="section">
		{foreach from=$events key='eventCategory' item='eventList'}
			<div class="notificationSettings">
				<div class="notificationSettingsCategory">
					<div class="notificationSettingsEvent">{lang}wcf.user.notification.{$eventCategory}{/lang}</div>
					<div class="notificationSettingsState">{lang}wcf.user.notification.status.active{/lang}</div>
					<div class="notificationSettingsEmail">{lang}wcf.user.notification.status.email{/lang}</div>
				</div>
				{foreach from=$eventList item=event}
					<div class="notificationSettingsItem">
						<div class="notificationSettingsEvent">
							<label for="settings_{@$event->eventID}">{lang}wcf.user.notification.{$event->objectType}.{$event->eventName}{/lang}</label>
						</div>
						<div class="notificationSettingsState">
							<label>
								<input type="checkbox" id="settings_{@$event->eventID}" name="settings[{@$event->eventID}][enabled]" class="jsCheckboxNotificationSettingsState" value="1" data-object-id="{@$event->eventID}"{if !$settings[$event->eventID][enabled]|empty} checked{/if}>
								<span class="icon icon24 fa-bell green pointer"></span>
								<span class="icon icon24 fa-bell-slash red pointer"></span>
							</label>
						</div>
						<div class="notificationSettingsEmail">
							{if $event->supportsEmailNotification()}
								<input type="hidden" id="settings_{$event->eventID}_mailNotificationType" name="settings[{@$event->eventID}][mailNotificationType]" value="{$settings[$event->eventID][mailNotificationType]}">
								<a href="#" class="notificationSettingsEmailType jsTooltip{if $settings[$event->eventID][enabled]|empty} disabled{/if}" role="button" title="{lang}wcf.user.notification.mailNotificationType.{@$settings[$event->eventID][mailNotificationType]}{/lang}" data-object-id="{@$event->eventID}">
									{if $settings[$event->eventID][mailNotificationType] === 'none'}
										<span class="icon icon24 fa-times red jsIconNotificationSettingsEmailType"></span>
									{else}
										<span class="icon icon24 {if $settings[$event->eventID][mailNotificationType] === 'instant'}fa-flash{else}fa-clock-o{/if} green jsIconNotificationSettingsEmailType"></span>
									{/if}
									<span class="icon icon16 fa-caret-down"></span>
								</a>
							{/if}
						</div>
					</div>
				{/foreach}
			</div>
		{/foreach}
	</div>
	
	{event name='sections'}
	
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Controller/User/Notification/Settings'], function(Language, ControllerUserNotificationSettings) {
		Language.addObject({
			'wcf.user.notification.mailNotificationType.daily': '{jslang}wcf.user.notification.mailNotificationType.daily{/jslang}',
			'wcf.user.notification.mailNotificationType.instant': '{jslang}wcf.user.notification.mailNotificationType.instant{/jslang}',
			'wcf.user.notification.mailNotificationType.none': '{jslang}wcf.user.notification.mailNotificationType.none{/jslang}'
		});
		
		ControllerUserNotificationSettings.init();
	});
</script>

{include file='footer'}
