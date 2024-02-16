{include file='header'}

{if $eventList|count}
	<div id="recentActivities" class="section recentActivityList" data-last-event-time="{@$lastEventTime}">
		{include file='recentActivityListItem'}
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}{event name='contentFooterNavigation'}{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<woltlab-core-notice type="info">{lang}wcf.user.recentActivity.noEntries{/lang}</woltlab-core-notice>
{/if}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/User/RecentActivity/Loader'], ({ setup }) => {
		{jsphrase name='wcf.user.recentActivity.more'}
		{jsphrase name='wcf.user.recentActivity.noMoreEntries'}

		setup(document.getElementById('recentActivities'));
	});
</script>

{include file='footer'}
