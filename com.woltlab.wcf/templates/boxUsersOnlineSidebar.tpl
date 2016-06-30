<ul class="inlineList commaSeparated small">
	{foreach from=$usersOnlineList->getObjects() item=userOnline}
		<li><a href="{link controller='User' object=$userOnline->getDecoratedObject()}{/link}" class="userLink" data-user-id="{@$userOnline->userID}">{@$userOnline->getFormattedUsername()}</a></li>
	{/foreach}
</ul>

<p><small>{lang}wcf.user.usersOnline.detail{/lang}{if USERS_ONLINE_RECORD && $__showRecord} <span class="separatorLeft">{lang}wcf.user.usersOnline.record{/lang}</span>{/if}</small></p>