{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.menu.settings{/lang}: {lang}wcf.user.notification.notifications{/lang} - {lang}wcf.user.menu.settings{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
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
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='userMenuSidebar'}

{include file='header' sidebarOrientation='left'}

<header class="boxHeadline">
	<h1>{lang}wcf.user.menu.settings{/lang}: {lang}wcf.user.notification.notifications{/lang}</h1>
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
					{foreach from=$eventList item='event'}
						<dd>
							<label><input type="checkbox" name="settings[{@$event->eventID}][enabled]" value="1"{if $settings[$event->eventID][enabled]} checked="checked"{/if} /> {lang}wcf.user.notification.{$event->objectType}.{$event->eventName}{/lang}</label>
							{hascontent}<small>{content}{lang __optional=true}wcf.user.notification.{$event->objectType}.{$event->eventName}.description{/lang}{/content}</small>{/hascontent}
							<small class="jsMailNotificationType">
								<select name="settings[{@$event->eventID}][mailNotificationType]">
									<option value="none">{lang}wcf.user.notification.mailNotificationType.none{/lang}</option>
									<option value="instant"{if $settings[$event->eventID][mailNotificationType] == 'instant'} selected="selected"{/if}>{lang}wcf.user.notification.mailNotificationType.instant{/lang}</option>
									<option value="daily"{if $settings[$event->eventID][mailNotificationType] == 'daily'} selected="selected"{/if}>{lang}wcf.user.notification.mailNotificationType.daily{/lang}</option>
								</select>
							</small>
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
