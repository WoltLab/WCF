{assign var='text' value="\x7B\$username\x7D"|str_replace:$mailbox->getUser()->username:$text}{if $mimeType === 'text/plain'}
{include file='email_plaintext' content=$text}
{else}
	{if $enableHTML}
		{include file='email_html' content=$text}
	{else}
		{include file='email_html' content=$text|newlineToBreak}
	{/if}
{/if}
