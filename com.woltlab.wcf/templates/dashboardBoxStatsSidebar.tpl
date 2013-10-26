<dl class="plain inlineDataList">
	<dt>{lang}wcf.user.members{/lang}</dt>
	<dd>{#$dashboardStats[members]}</dd>
	
	{event name='stats'}
	
	{if $dashboardStats[newestMember]}
		<dt>{lang}wcf.user.newestMember{/lang}</dt>
		<dd><a href="{link controller='User' object=$dashboardStats[newestMember]}{/link}" class="userLink" data-user-id="{$dashboardStats[newestMember]->userID}">{$dashboardStats[newestMember]}</a></dd>
	{/if}
</dl>