{include file='header' pageTitle='wcf.acp.user.notificationPresetSettings'}

<script data-relocate="true">
	require(['Language', 'WoltLab/WCF/Controller/User/Notification/Settings'], function(Language, ControllerUserNotificationSettings) {
		Language.addObject({
			'wcf.user.notification.mailNotificationType.daily': '{lang}wcf.user.notification.mailNotificationType.daily{/lang}',
			'wcf.user.notification.mailNotificationType.instant': '{lang}wcf.user.notification.mailNotificationType.instant{/lang}',
			'wcf.user.notification.mailNotificationType.none': '{lang}wcf.user.notification.mailNotificationType.none{/lang}'
		});
		
		ControllerUserNotificationSettings.setup();
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.notificationPresetSettings{/lang}</h1>
		<p class="contentHeaderDescription">{lang}wcf.acp.user.notificationPresetSettings.description{/lang}</p>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<form method="post" action="{link controller='NotificationPresetSettings'}{/link}">
	<div id="notificationSettings">
		<div class="section">
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="applyChangesToExistingUsers" value="1" {if $applyChangesToExistingUsers}checked="checked" {/if}/> {lang}wcf.acp.user.notificationPresetSettings.applyChangesToExistingUsers{/lang}</label>
					<small>{lang}wcf.acp.user.notificationPresetSettings.applyChangesToExistingUsers.description{/lang}</small>	
				</dd>
			</dl>
		</div>
		
		{foreach from=$events key='eventCategory' item='eventList'}
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.user.notification.{$eventCategory}{/lang}</h2>
				
				<dl>
					{foreach from=$eventList item=event}
						<dt>{lang}wcf.user.notification.{$event->objectType}.{$event->eventName}{/lang}</dt>
						<dd>
							<ol class="flexibleButtonGroup" data-object-id="{@$event->eventID}">
								<li>
									<input type="radio" id="settings_{@$event->eventID}_disabled" name="settings[{@$event->eventID}][enabled]" value="0"{if $settings[$event->eventID][enabled]|empty} checked="checked"{/if}>
									<label for="settings_{@$event->eventID}_disabled" class="red">
										<span class="icon icon16 fa-times"></span>
										{lang}wcf.user.notification.notifications.disabled{/lang}
									</label>
								</li>
								<li class="spaceAfter">
									<input type="radio" id="settings_{@$event->eventID}_enabled" name="settings[{@$event->eventID}][enabled]" value="1"{if !$settings[$event->eventID][enabled]|empty} checked="checked"{/if}>
									<label for="settings_{@$event->eventID}_enabled" class="green">
										<span class="icon icon16 fa-bell"></span>
										{lang}wcf.user.notification.notifications.enabled{/lang}
									</label>
								</li>
								{if $event->supportsEmailNotification()}
									<li class="notificationSettingsEmail{if !$settings[$event->eventID][enabled]|empty} active{/if}">
										<input type="hidden" id="settings_{$event->eventID}_mailNotificationType" name="settings[{@$event->eventID}][mailNotificationType]" value="{$settings[$event->eventID][mailNotificationType]}">
										<a{if $settings[$event->eventID][mailNotificationType] !== 'none'} class="active yellow"{/if}>
											<span class="icon icon16 fa-envelope-o"></span>
											<span class="title">{lang}wcf.user.notification.mailNotificationType.{$settings[$event->eventID][mailNotificationType]}{/lang}</span>
											<span class="icon icon16 fa-caret-down"></span>
										</a>
									</li>
								{/if}
							</ol>
						</dd>
					{/foreach}
				</dl>
			</section>
		{/foreach}
		
		{event name='sections'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}