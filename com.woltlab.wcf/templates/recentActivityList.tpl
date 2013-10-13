{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.recentActivity{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	<link rel="canonical" href="{link controller='RecentActivityList'}{/link}" />
	
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
</head>

<body id="tpl{$templateName|ucfirst}">

{capture assign='sidebar'}
	{@$__boxSidebar}
{/capture}

{include file='header' sidebarOrientation='right'}

<header class="boxHeadline">
	<h1>{lang}wcf.user.recentActivity{/lang}</h1>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if $eventList|count}
	<div class="container marginTop">
		<ul id="recentActivities" class="containerList recentActivityList" data-last-event-time="{@$lastEventTime}">
			{include file='recentActivityListItem'}
		</ul>
	</div>
	
	<div class="contentNavigation">
		{hascontent}
			<nav>
				<ul>
					{content}
						{event name='contentNavigationButtonsBottom'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</div>
{else}
	<p class="info">{lang}wcf.user.recentActivity.noEntries{/lang}</p>
{/if}

{include file='footer'}

</body>
</html>
