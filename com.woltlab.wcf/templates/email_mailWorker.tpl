{assign var='text' value=$mailbox->getPersonalizedText($text)}{if $mimeType === 'text/plain'}
{include file='email_plaintext' content=$text}
{else}
	{if $enableHTML}
		{include file='email_html' content=$text}
	{else}
		{include file='email_html' content=$text|newlineToBreak}
	{/if}
{/if}
