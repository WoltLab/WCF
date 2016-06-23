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
	<p class="info">{lang}wcf.user.recentActivity.noEntries{/lang}</p>
{/if}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.user.recentActivity.more': '{lang}wcf.user.recentActivity.more{/lang}',
			'wcf.user.recentActivity.noMoreEntries': '{lang}wcf.user.recentActivity.noMoreEntries{/lang}'
		});
		
		new WCF.User.RecentActivityLoader(null);
	});
	//]]>
</script>

{include file='footer'}
