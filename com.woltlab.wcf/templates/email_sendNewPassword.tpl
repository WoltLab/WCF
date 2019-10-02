{if $mimeType === 'text/plain'}
{capture assign='content'}{lang}wcf.acp.user.sendNewPassword.mail.plaintext{/lang}{/capture}
{include file='email_plaintext'}
{else}
	{capture assign='content'}
	<h2>{lang}wcf.acp.user.sendNewPassword.mail.html.headline{/lang}</h2>
	{lang}wcf.acp.user.sendNewPassword.mail.html.intro{/lang}

	{capture assign=button}
	<a href="{link controller='NewPassword' object=$mailbox->getUser() isHtmlEmail=true}k={@$mailbox->getUser()->lostPasswordKey}{/link}">
		{lang}wcf.acp.user.sendNewPassword.mail.html.reset{/lang}
	</a>
	{/capture}
	{include file='email_paddingHelper' class='button' content=$button sandbox=true}

	{lang}wcf.acp.user.sendNewPassword.mail.html.outro{/lang}
	{/capture}
	{include file='email_html'}
{/if}
