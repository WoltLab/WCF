{if $mimeType === 'text/plain'}
{capture assign='content'}{lang}wcf.contact.mail.plaintext{/lang}{/capture}
{include file='email_plaintext'}
{else}
	{capture assign='content'}
	{lang}wcf.contact.mail.html{/lang}
	{/capture}
	{include file='email_html'}
{/if}
