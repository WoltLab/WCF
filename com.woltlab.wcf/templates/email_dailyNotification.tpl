{if $mimeType === 'text/plain'}
{capture assign='content'}
{lang}wcf.user.notification.mail.plaintext.intro{/lang}

{foreach from=$notifications item='notification'}
{assign var='event' value=$notification[event]}
{assign var='notificationContent' value=$notification[notificationContent]}
{assign var='notificationType' value=$notification[notificationType]}
{if $notificationContent|is_array}{include file=$notificationContent[template] application=$notificationContent[application] variables=$notificationContent[variables]}{*
*}{else}{@$notificationContent}{/if}
{/foreach}

{*lang}TODO: wcf.user.notification.mail.plaintext.outro{/lang*}
{/capture}
{include file='email_plaintext'}
{else}
	{capture assign='content'}
	{lang}wcf.user.notification.mail.html.intro{/lang}
	
	{foreach from=$notifications item='notification'}
	{assign var='event' value=$notification[event]}
	{assign var='notificationContent' value=$notification[notificationContent]}
	{assign var='notificationType' value=$notification[notificationType]}
	<div>
		{if $notificationContent|is_array}
			{include file=$notificationContent[template] application=$notificationContent[application] variables=$notificationContent[variables]}
		{else}
			{@$notificationContent}
		{/if}
	</div>
	{/foreach}

	{*lang}TODO: wcf.user.notification.mail.html.outro{/lang*}
	{/capture}
	{include file='email_html'}
{/if}
