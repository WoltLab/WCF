<dl class="plain dataList">
	{event name='stats'}
	
	<dt>{lang}wcf.user.members{/lang}</dt>
	<dd>{#$statistics[members]}</dd>
	
	{if USERS_ONLINE_RECORD}
		<dt>{lang}wcf.user.mostOnlineUsers{/lang}</dt>
		<dd title="{@USERS_ONLINE_RECORD_TIME|plainTime}" class="jsTooltip">{#USERS_ONLINE_RECORD}</dd>
	{/if}
	
	{if $statistics[newestMember]}
		<dt>{lang}wcf.user.newestMember{/lang}</dt>
		<dd><a href="{link controller='User' object=$statistics[newestMember]}{/link}" class="userLink" data-user-id="{$statistics[newestMember]->userID}">{$statistics[newestMember]}</a></dd>
	{/if}
</dl>
