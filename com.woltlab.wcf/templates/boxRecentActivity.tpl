<section class="section dashboardBoxRecentActivity">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.user.recentActivity{/lang}</h2>
	</header>
	
	{assign var='__events' value=$eventList->getObjects()}
	{assign var='__lastEvent' value=$__events|end}
	<div class="recentActivityList"
		id="boxRecentActivity{$boxID}"
		data-last-event-time="{$lastEventTime}"
		data-last-event-id="{if $__lastEvent}{$__lastEvent->eventID}{else}0{/if}"
		data-filtered-by-followed-users="{if $filteredByFollowedUsers}true{else}false{/if}"
		data-box-id="{$boxID}"
	>
		{if $canFilterByFollowedUsers}
			<div class="recentActivityList__switchContext">
				<ul class="buttonGroup">
					<li>
						<button type="button" class="recentActivityList__switchContextButton button small{if !$filteredByFollowedUsers} active{/if}">
							{lang}wcf.user.recentActivity.scope.all{/lang}
						</button>
					</li>
					<li>
						<button type="button" class="recentActivityList__switchContextButton button small{if $filteredByFollowedUsers} active{/if}">
							{lang}wcf.user.recentActivity.scope.followedUsers{/lang}
						</button>
					</li>
				</ul>
			</div>

			{if $filteredByFollowedUsers && !$__events|count}
				<div class="recentActivityList__showMoreButton">
					<small>{lang}wcf.user.recentActivity.scope.followedUsers.noResults{/lang}</small>
				</div>
			{/if}
		{/if}
		
		{include file='recentActivityListItem'}
	</div>
</section>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/User/RecentActivity/Loader'], ({ setup }) => {
		{jsphrase name='wcf.user.recentActivity.more'}
		{jsphrase name='wcf.user.recentActivity.noMoreEntries'}

		setup(document.getElementById('boxRecentActivity{unsafe:$boxID|encodeJS}'));
	});
</script>
