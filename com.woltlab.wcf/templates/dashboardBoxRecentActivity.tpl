<section class="section sectionContainerList dashboardBoxRecentActivity" id="dashboardBoxRecentActivity">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.user.recentActivity{/lang}</h2>
		
		{if $canFilterByFollowedUsers}{*todo*}
			<nav class="jsMobileNavigation buttonGroupNavigation jsOnly jsRecentActivitySwitchContext">
				<ul class="buttonGroup">
					<li><a href="#" class="button small{if !$filteredByFollowedUsers} active{/if}">{lang}wcf.user.recentActivity.scope.all{/lang}</a></li>
					<li><a href="#" class="button small{if $filteredByFollowedUsers} active{/if}">{lang}wcf.user.recentActivity.scope.followedUsers{/lang}</a></li>
				</ul>
			</nav>
		{/if}
	</header>
	
	{assign var='__events' value=$eventList->getObjects()}
	{assign var='__lastEvent' value=$__events|end}
	<ul id="recentActivities" class="containerList recentActivityList" data-last-event-time="{@$lastEventTime}" data-last-event-id="{if $__lastEvent}{@$__lastEvent->eventID}{else}0{/if}">
		{include file='recentActivityListItem'}
	</ul>
</section>

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
