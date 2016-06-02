{if $mimeType === 'text/plain'}
{capture assign='content'}{lang}wcf.user.register.needActivation.mail.plaintext{/lang}{/capture}
{include file='email_plaintext'}
{else}
	{capture assign='content'}
	<h1>{lang}wcf.user.register.needActivation.mail.html.headline{/lang}</h1>
	{lang}wcf.user.register.needActivation.mail.html.intro{/lang}
	<a href="{link controller='RegisterActivation' isEmail=true}u={@$mailbox->getUser()->userID}&a={@$mailbox->getUser()->activationCode}{/link}" class="button">
		{lang}wcf.user.register.needActivation.mail.html.activate{/lang}
	</a>
	{lang}wcf.user.register.needActivation.mail.html.outro{/lang}
	{/capture}
	{include file='email_html'}
{/if}
