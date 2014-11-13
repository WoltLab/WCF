{include file='header' pageTitle='wcf.acp.user.notificationPresetSettings'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		$('#notificationSettings > fieldset > dl > dd > label > input').each(function(index, value) {
			var $input = $(value);
			$input.on('click', function(event) {
				var $input = $(event.currentTarget);
				$input.parents('dd').find('.jsMailNotificationType').toggle();
			});
			if (!$input.is(':checked')) {
				$input.parents('dd').find('.jsMailNotificationType').hide();
			}
		});
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.notificationPresetSettings{/lang}</h1>
	<p>{lang}wcf.acp.user.notificationPresetSettings.description{/lang}</p>
</header>

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

<form method="post" action="{link controller='NotificationPresetSettings'}{/link}">
	<div class="container containerPadding marginTop" id="notificationSettings">
		<fieldset>
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="applyChangesToExistingUsers" value="1" {if $applyChangesToExistingUsers}checked="checked" {/if}/> {lang}wcf.acp.user.notificationPresetSettings.applyChangesToExistingUsers{/lang}</label>
					<small>{lang}wcf.acp.user.notificationPresetSettings.applyChangesToExistingUsers.description{/lang}</small>	
				</dd>
			</dl>
		</fieldset>
		
		{foreach from=$events key='eventCategory' item='eventList'}
			<fieldset>
				<legend>{lang}wcf.user.notification.{$eventCategory}{/lang}</legend>
				
				<dl>
					{foreach from=$eventList item=event}
						<dd>
							<label><input type="checkbox" name="settings[{@$event->eventID}][enabled]" value="1"{if !$settings[$event->eventID][enabled]|empty} checked="checked"{/if} /> {lang}wcf.user.notification.{$event->objectType}.{$event->eventName}{/lang}</label>
							{hascontent}<small>{content}{lang __optional=true}wcf.user.notification.{$event->objectType}.{$event->eventName}.description{/lang}{/content}</small>{/hascontent}
							{if $event->supportsEmailNotification()}
								<small class="jsMailNotificationType">
									<select name="settings[{@$event->eventID}][mailNotificationType]">
										<option value="none">{lang}wcf.user.notification.mailNotificationType.none{/lang}</option>
										<option value="instant"{if $settings[$event->eventID][mailNotificationType] == 'instant'} selected="selected"{/if}>{lang}wcf.user.notification.mailNotificationType.instant{/lang}</option>
										<option value="daily"{if $settings[$event->eventID][mailNotificationType] == 'daily'} selected="selected"{/if}>{lang}wcf.user.notification.mailNotificationType.daily{/lang}</option>
									</select>
								</small>
							{else}
								<small class="jsMailNotificationType">{lang}wcf.user.notification.mailNotificationType.notSupported{/lang}</small>
							{/if}
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