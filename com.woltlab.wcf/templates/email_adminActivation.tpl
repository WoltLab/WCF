{if $mimeType === 'text/plain'}
{capture assign='content'}{lang}wcf.acp.user.activation.mail.plaintext{/lang}{/capture}
{include file='email_plaintext'}
{else}
	{capture assign='content'}
	<h1>{lang}wcf.acp.user.activation.mail.html.headline{/lang}</h1>
	{lang}wcf.acp.user.activation.mail.html.text{/lang}
	{/capture}
	
	{include file='email_html'}
{/if}
