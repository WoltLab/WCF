<dl class="plain dataList">
	<dt>{lang}wcf.user.members{/lang}</dt>
	<dd>{#$statistics[members]}</dd>
	
	{event name='stats'}
	
	{if $statistics[newestMember]}
		<dt>{lang}wcf.user.newestMember{/lang}</dt>
		<dd><a href="{link controller='User' object=$statistics[newestMember]}{/link}" class="userLink" data-user-id="{$statistics[newestMember]->userID}">{$statistics[newestMember]}</a></dd>
	{/if}
</dl>
