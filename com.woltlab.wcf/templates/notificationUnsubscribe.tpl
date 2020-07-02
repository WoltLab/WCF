{include file='header' __disableAds=true}

<p class="info" role="status">{lang}wcf.user.notification.mail.unsubscribe.description{/lang}</p>

{include file='formError'}

<form method="post" action="{link controller='NotificationUnsubscribe' userID=$user->userID token=$token}{/link}">
	<div class="section">
		<dl{if $errorField == 'eventID'} class="formError"{/if}>
			<dt><label for="eventID">{lang}wcf.user.notification.mail.unsubscribe.event{/lang}</label></dt>
			<dd>
				{if $event !== null}
					<label><input type="radio" id="eventID" name="eventID" value="{$event->eventID}" checked> {lang}wcf.user.notification.{$event->getObjectType()->objectType}.{$event->eventName}{/lang}</label>
				{/if}
				<label><input type="radio" name="eventID" value="0"{if $event === null} checked{/if}> {lang}wcf.user.notification.mail.unsubscribe.event.all{/lang}</label>
			</dd>
		</dl>
		
		{event name='fields'}
	</div>

	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.user.notification.mail.unsubscribe.confirm{/lang}" accesskey="s">
		{* The tag is not technically required, but the POST data would be empty otherwise. *}
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer' __disableAds=true}
