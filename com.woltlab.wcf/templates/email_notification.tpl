{if $mimeType === 'text/plain'}
{capture assign='content'}
{lang}wcf.user.notification.mail.plaintext.intro{/lang}

{@$notificationContent}

{lang}wcf.user.notification.mail.plaintext.outro{/lang}
{/capture}
{include file='email_plaintext'}
{else}
	{capture assign='content'}
	<h1>{lang}wcf.user.notification.mail.html.headline{/lang}</h1>
	{lang}wcf.user.notification.mail.html.intro{/lang}

	{@$notificationContent}

	{lang}wcf.user.notification.mail.html.outro{/lang}
	{/capture}
	{include file='email_html'}
{/if}
