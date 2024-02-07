<section class="section sectionContainerList dashboardBoxRecentActivity" id="boxRecentActivity{@$boxID}">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.user.recentActivity{/lang}</h2>
	</header>
	
	{assign var='__events' value=$eventList->getObjects()}
	{assign var='__lastEvent' value=$__events|end}
	<ul class="containerList recentActivityList"
		data-last-event-time="{@$lastEventTime}"
		data-last-event-id="{if $__lastEvent}{@$__lastEvent->eventID}{else}0{/if}"
		data-filtered-by-followed-users="{if $filteredByFollowedUsers}true{else}false{/if}"
		data-user-id="0"
		data-box-id="{@$boxID}"
	>
		{if $canFilterByFollowedUsers}
			<li class="containerListButtonGroup jsOnly jsRecentActivitySwitchContext">
				<ul class="buttonGroup">
					<li><a href="#" class="button small{if !$filteredByFollowedUsers} active{/if}">{lang}wcf.user.recentActivity.scope.all{/lang}</a></li>
					<li><a href="#" class="button small{if $filteredByFollowedUsers} active{/if}">{lang}wcf.user.recentActivity.scope.followedUsers{/lang}</a></li>
				</ul>
				
				{if $filteredByFollowedUsersOverride}
					<p class="info recentActivityFollowedNoResults">{lang}wcf.user.recentActivity.scope.followedUsers.noResults{/lang}</p>
				{/if}
			</li>
		{/if}
		
		{include file='recentActivityListItem'}
	</ul>
</section>

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Ui/User/Activity/Recent'], function (Language, UiUserActivityRecent) {
		Language.addObject({
			'wcf.user.recentActivity.more': '{jslang}wcf.user.recentActivity.more{/jslang}',
			'wcf.user.recentActivity.noMoreEntries': '{jslang}wcf.user.recentActivity.noMoreEntries{/jslang}'
		});
		
		new UiUserActivityRecent('boxRecentActivity{@$boxID}');
	});
</script>
