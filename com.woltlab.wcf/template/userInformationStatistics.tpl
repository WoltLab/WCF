<dl class="plain inlineDataList userStats">
	{event name='statistics'}
	
	{if MODULE_LIKE}
		<dt>{lang}wcf.like.likesReceived{/lang}</dt>
		<dd>{#$user->likesReceived}</dd>
	{/if}
	
	<dt>{lang}wcf.user.activityPoint{/lang}</dt>
	<dd>{#$user->activityPoints}</dd>
</dl>