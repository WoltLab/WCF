<header class="boxHeadline boxSubHeadline">
	<h2>{lang}wcf.user.recentActivity{/lang}</h2>
	{if $filteredByFollowedUsers}<p>{lang}wcf.user.recentActivity.filteredByFollowedUsers{/lang}</p>{/if}
</header>

<div class="container marginTop">
	<ul id="recentActivities" class="containerList recentActivityList" data-last-event-time="{@$lastEventTime}">
		{include file='recentActivityListItem'}
	</ul>
</div>

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.user.recentActivity.more': '{lang}wcf.user.recentActivity.more{/lang}',
			'wcf.user.recentActivity.noMoreEntries': '{lang}wcf.user.recentActivity.noMoreEntries{/lang}'
		});
		
		new WCF.User.RecentActivityLoader(null, {if $filteredByFollowedUsers}true{else}false{/if});
	});
	//]]>
</script>