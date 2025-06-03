<dl class="plain dataList">
	{event name='stats'}
	
	<dt>{lang}wcf.user.members{/lang}</dt>
	<dd>{#$statistics->members}</dd>
	
	{if USERS_ONLINE_RECORD}
		<dt>{lang}wcf.user.mostOnlineUsers{/lang}</dt>
		<dd title="{time type='plainTime' time=USERS_ONLINE_RECORD_TIME}" class="jsTooltip">{#USERS_ONLINE_RECORD}</dd>
	{/if}

	<dt>{lang}wcf.user.newestMember{/lang}</dt>
	<dd>{user object=$statistics->newestMember}</dd>
</dl>
