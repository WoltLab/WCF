{if $mimeType === 'text/plain'}
{capture assign='content'}{lang}wcf.user.lostPassword.mail.plaintext{/lang}{/capture}
{include file='email_plaintext'}
{else}
	{capture assign='content'}
	<h2>{lang}wcf.user.lostPassword.mail.html.headline{/lang}</h2>
	{lang}wcf.user.lostPassword.mail.html.intro{/lang}

	{capture assign=button}
	<a href="{link controller='NewPassword' object=$mailbox->getUser() isHtmlEmail=true}k={@$mailbox->getUser()->lostPasswordKey}{/link}">
		{lang}wcf.user.lostPassword.mail.html.reset{/lang}
	</a>
	{/capture}
	{include file='email_paddingHelper' class='button' content=$button sandbox=true}

	{lang}wcf.user.lostPassword.mail.html.outro{/lang}
	{/capture}
	{include file='email_html'}
{/if}
