<dl class="plain {if !$__userStatsClassname|empty}{@$__userStatsClassname}{else}inlineDataList{/if} userStats">
	{event name='statistics'}
	
	{if MODULE_LIKE && $user->likesReceived}
		<dt><a href="{link controller='User' object=$user}{/link}#likes" class="jsTooltip" title="{lang}wcf.like.showLikesReceived{/lang}">{lang}wcf.like.likesReceived{/lang}</a></dt>
		<dd>{#$user->likesReceived}</dd>
	{/if}
	
	{if $user->activityPoints}
		<dt>{lang}wcf.user.activityPoint{/lang}</dt>
		<dd>{#$user->activityPoints}</dd>
	{/if}
</dl>