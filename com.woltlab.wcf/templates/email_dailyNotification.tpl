{* variable definition for full backwards-compatibility *}
{if !$notificationCount|isset}{assign var=notificationCount value=$notifications|count}{/if}
{if !$maximum|isset}{assign var=maximum value=$notificationCount}{/if}
{if !$remaining|isset}{assign var=remaining value=0}{/if}
{if $mimeType === 'text/plain'}
{capture assign='content'}
{lang}wcf.user.notification.mail.daily.plaintext.intro{/lang}

---------------

{implode from=$notifications item='notification' glue="\n---------------\n\n"}
{assign var='event' value=$notification[event]}
{assign var='notificationContent' value=$notification[notificationContent]}
{assign var='notificationType' value=$notification[notificationType]}
{if $notificationContent|is_array}{if !$notificationContent[variables]|empty}{foreach from=$notificationContent[variables] key='__key' item='__item'}{assign var=$__key value=$__item}{/foreach}{/if}{include file=$notificationContent[template] application=$notificationContent[application] variables=$notificationContent[variables]}{*
*}{else}{@$notificationContent}{/if}
{/implode}

---------------
{lang}wcf.user.notification.mail.daily.plaintext.outro{/lang}
{/capture}
{include file='email_plaintext'}
{else}
	{capture assign='content'}
	{lang}wcf.user.notification.mail.daily.html.intro{/lang}
	
	{foreach from=$notifications item='notification'}
	{assign var='event' value=$notification[event]}
	{assign var='notificationContent' value=$notification[notificationContent]}
	{assign var='notificationType' value=$notification[notificationType]}
	<div class="largeMarginTop">
		{if $notificationContent|is_array}
			{if !$notificationContent[variables]|empty}
				{foreach from=$notificationContent[variables] key='__key' item='__item'}
					{assign var=$__key value=$__item}
				{/foreach}
			{/if}
			
			{include file=$notificationContent[template] application=$notificationContent[application] variables=$notificationContent[variables]}
		{else}
			{@$notificationContent}
		{/if}
	</div>
	{/foreach}

	{lang}wcf.user.notification.mail.daily.html.outro{/lang}
	{/capture}
	{include file='email_html'}
{/if}
