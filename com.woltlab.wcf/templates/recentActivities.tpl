{hascontent}
	<script data-relocate="true">
		$(function() {
			WCF.Language.addObject({
				'wcf.user.recentActivity.more': '{jslang}wcf.user.recentActivity.more{/jslang}',
				'wcf.user.recentActivity.noMoreEntries': '{jslang}wcf.user.recentActivity.noMoreEntries{/jslang}'
			});
			
			new WCF.User.RecentActivityLoader({@$userID});
		});
	</script>
	
	<ul id="recentActivities" class="containerList recentActivityList" data-last-event-time="{@$lastEventTime}">
		{content}
			{include file='recentActivityListItem'}
		{/content}
	</ul>
{hascontentelse}
	<div class="section">
		{if $placeholder|isset}{$placeholder}{/if}
	</div>
{/hascontent}
