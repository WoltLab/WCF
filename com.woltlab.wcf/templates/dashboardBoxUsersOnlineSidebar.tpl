<ul class="dataList userList">
	{foreach from=$usersOnlineList->getObjects() item=userOnline}
		<li><a href="{link controller='User' object=$userOnline->getDecoratedObject()}{/link}" class="userLink" data-user-id="{@$userOnline->userID}">{@$userOnline->getFormattedUsername()}</a></li>
	{/foreach}
</ul>

<p><small>{lang}wcf.user.usersOnline.detail{/lang}{if USERS_ONLINE_RECORD} - {lang}wcf.user.usersOnline.record{/lang}{/if}</small></p>