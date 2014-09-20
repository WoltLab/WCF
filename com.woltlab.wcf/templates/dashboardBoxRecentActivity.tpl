<header class="boxHeadline boxSubHeadline">
	<h2>{lang}wcf.user.recentActivity{/lang}</h2>
	{if $filteredByFollowedUsers}<p>{lang}wcf.user.recentActivity.filteredByFollowedUsers{/lang}</p>{/if}
	{* TODO: styling *}
	{if $canFilterByFollowedUsers}<a class="jsOnly jsRecentActivitySwitchContext" style="float: right">{lang}wcf.user.recentActivity.scope.{if $filteredByFollowedUsers}all{else}followedUsers{/if}{/lang}</a>{/if}
</header>

<div class="container marginTop">
	{assign var='__events' value=$eventList->getObjects()}
	{assign var='__lastEvent' value=$__events|end}
	<ul id="recentActivities" class="containerList recentActivityList" data-last-event-time="{@$lastEventTime}" data-last-event-id="{@$__lastEvent->eventID}">
		{include file='recentActivityListItem'}
	</ul>
</div>

<script data-relocate="true">
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
