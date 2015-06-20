{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.menu.settings{/lang}: {lang}wcf.user.notification.notifications{/lang} - {lang}wcf.user.menu.settings{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
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
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{include file='userMenuSidebar'}

{include file='header' sidebarOrientation='left'}

<header class="boxHeadline">
	<h1>{lang}wcf.user.menu.settings{/lang}: {lang}wcf.user.notification.notifications{/lang}</h1>
	<p>{lang}wcf.user.notification.notifications.description{/lang}
</header>

{include file='userNotice'}

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form method="post" action="{link controller='NotificationSettings'}{/link}">
	<div class="container containerPadding marginTop" id="notificationSettings">
		{foreach from=$events key='eventCategory' item='eventList'}
			<fieldset>
				<legend>{lang}wcf.user.notification.{$eventCategory}{/lang}</legend>
				
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
			</fieldset>
		{/foreach}
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}

</body>
</html>
