<dl class="plain inlineDataList userStats">
	{event name='statistics'}
	
	{if MODULE_LIKE && $user->likesReceived}
		<dt>{lang}wcf.like.likesReceived{/lang}</dt>
		<dd>{#$user->likesReceived}</dd>
	{/if}
	
	{if $user->activityPoints}
		<dt>{lang}wcf.user.activityPoint{/lang}</dt>
		<dd>{#$user->activityPoints}</dd>
	{/if}
</dl>