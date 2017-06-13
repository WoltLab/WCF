{assign var=subscription value=$notificationContent[variables][subscription]}{assign var=notification value=$notificationContent[variables][notification]}
{if $mimeType === 'text/plain'}
{lang}wcf.paidSubscription.expiringSubscription.notification.mail.plaintext{/lang}
{else}
	{lang}wcf.paidSubscription.expiringSubscription.notification.mail.html{/lang}
{/if}
