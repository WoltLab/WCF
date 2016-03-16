{hascontent}
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.user.recentActivity.more': '{lang}wcf.user.recentActivity.more{/lang}',
				'wcf.user.recentActivity.noMoreEntries': '{lang}wcf.user.recentActivity.noMoreEntries{/lang}'
			});
			
			new WCF.User.RecentActivityLoader({@$userID});
		});
		//]]>
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
