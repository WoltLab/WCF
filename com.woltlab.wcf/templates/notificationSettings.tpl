{capture assign='pageTitle'}{lang}wcf.user.menu.settings{/lang}: {lang}wcf.user.notification.notifications{/lang} - {lang}wcf.user.menu.settings{/lang}{/capture}

{capture assign='contentTitle'}{lang}wcf.user.menu.settings{/lang}: {lang}wcf.user.notification.notifications{/lang}{/capture}

{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{include file='shared_formError'}

{if $success|isset}
	<woltlab-core-notice type="success">{lang}wcf.global.success.edit{/lang}</woltlab-core-notice>
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
								{icon size=24 name='bell' type='solid'}
								{icon size=24 name='bell-slash'}
							</label>
						</div>
						<div class="notificationSettingsEmail">
							{if $event->supportsEmailNotification()}
								<input type="hidden" id="settings_{$event->eventID}_mailNotificationType" name="settings[{@$event->eventID}][mailNotificationType]" value="{$settings[$event->eventID][mailNotificationType]}">
								<button type="button" class="notificationSettingsEmailType jsTooltip{if $settings[$event->eventID][enabled]|empty} disabled{/if}" title="{lang}wcf.user.notification.mailNotificationType.{@$settings[$event->eventID][mailNotificationType]}{/lang}" data-object-id="{@$event->eventID}">
									<span class="jsIconNotificationSettingsEmailType">
										{if $settings[$event->eventID][mailNotificationType] === 'none'}
											{icon size=24 name='xmark'}
										{else if $settings[$event->eventID][mailNotificationType] === 'instant'}
											{icon size=24 name='bolt'}
										{else}
											{icon size=24 name='clock'}
										{/if}
									</span>
									{icon name='caret-down' type='solid'}
								</button>
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
		{csrfToken}
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
