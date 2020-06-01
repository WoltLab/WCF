<ul class="inlineList commaSeparated small">
	{foreach from=$usersOnlineList->getObjects() item=userOnline}
		<li>{user object=$userOnline}</li>
	{/foreach}
</ul>

<p><small>{lang}wcf.user.usersOnline.detail{/lang}{if USERS_ONLINE_RECORD && $__showRecord} <span class="separatorLeft">{lang}wcf.user.usersOnline.record{/lang}</span>{/if}</small></p>
