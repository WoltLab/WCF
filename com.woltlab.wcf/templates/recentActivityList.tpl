{include file='header'}

{if $eventList|count}
	<div class="section sectionContainerList">
		<ul id="recentActivities" class="containerList recentActivityList" data-last-event-time="{@$lastEventTime}">
			{include file='recentActivityListItem'}
		</ul>
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
	$(function() {
		WCF.Language.addObject({
			'wcf.user.recentActivity.more': '{jslang}wcf.user.recentActivity.more{/jslang}',
			'wcf.user.recentActivity.noMoreEntries': '{jslang}wcf.user.recentActivity.noMoreEntries{/jslang}'
		});
		
		new WCF.User.RecentActivityLoader(null);
	});
</script>

{include file='footer'}
