{hascontent}
	<div id="recentActivities" class="recentActivityList recentActivityList--userProfileContent" data-last-event-time="{$lastEventTime}" data-user-id="{$userID}">
		{content}
			{include file='recentActivityListItem'}
		{/content}
	</div>

	<script data-relocate="true">
		require(['WoltLabSuite/Core/Component/User/RecentActivity/Loader'], ({ setup }) => {
			{jsphrase name='wcf.user.recentActivity.more'}
			{jsphrase name='wcf.user.recentActivity.noMoreEntries'}

			setup(document.getElementById('recentActivities'));
		});
	</script>
{hascontentelse}
	<div class="section">
		{if $placeholder|isset}{$placeholder}{/if}
	</div>
{/hascontent}
