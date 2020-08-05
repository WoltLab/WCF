{if $mimeType === 'text/plain'}
{capture assign='content'}
{lang}wcf.user.notification.mail.plaintext.intro{/lang}

{if $notificationContent|is_array}{include file=$notificationContent[template] application=$notificationContent[application]}{*
*}{else}{@$notificationContent}{/if}

{lang}wcf.user.notification.mail.plaintext.outro{/lang}
{/capture}
{include file='email_plaintext'}
{else}
	{capture assign='content'}
	{lang}wcf.user.notification.mail.html.intro{/lang}

	{if $notificationContent|is_array}
		{include file=$notificationContent[template] application=$notificationContent[application]}
	{else}
		{@$notificationContent}
	{/if}

	{capture assign=button}
	<a href="{link controller='NotificationConfirm' isHtmlEmail=true id=$event->getNotification()->notificationID}{/link}">
		{lang}wcf.user.notification.mail.jumpToContent{/lang}
	</a>
	{/capture}
	{include file='email_paddingHelper' class='button' outerClass='paragraphMargin' content=$button sandbox=true}

	{lang}wcf.user.notification.mail.html.outro{/lang}
	{/capture}
	{include file='email_html'}
{/if}
