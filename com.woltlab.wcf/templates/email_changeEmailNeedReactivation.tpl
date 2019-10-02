{if $mimeType === 'text/plain'}
{capture assign='content'}{lang}wcf.user.changeEmail.needReactivation.mail.plaintext{/lang}{/capture}
{include file='email_plaintext'}
{else}
	{capture assign='content'}
	<h2>{lang}wcf.user.changeEmail.needReactivation.mail.html.headline{/lang}</h2>
	{lang}wcf.user.changeEmail.needReactivation.mail.html.intro{/lang}

	{capture assign=button}
	<a href="{link controller='EmailActivation' isHtmlEmail=true}u={@$mailbox->getUser()->userID}&a={@$mailbox->getUser()->reactivationCode}{/link}">
		{lang}wcf.user.changeEmail.needReactivation.mail.html.activate{/lang}
	</a>
	{/capture}
	{include file='email_paddingHelper' class='button' content=$button sandbox=true}

	{lang}wcf.user.changeEmail.needReactivation.mail.html.outro{/lang}
	{/capture}
	{include file='email_html'}
{/if}
