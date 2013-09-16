{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.notification.notifications{/lang} - {lang}wcf.user.usercp{/lang} - {PAGE_TITLE|language}</title>
	{include file='headInclude'}
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.user.notification.markAsConfirmed': '{lang}wcf.user.notification.markAsConfirmed{/lang}'
			});
			
			new WCF.Notification.List();
		});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='userMenuSidebar'}

{include file='header' sidebarOrientation='left'}

<header class="boxHeadline">
	<h1>{lang}wcf.user.notification.notifications{/lang} <span class="badge jsNotificationsBadge">{#$__wcf->getUserNotificationHandler()->getNotificationCount()}</span></h1>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='NotificationList' link="pageNo=%d"}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $notifications[notifications]}<li class="jsOnly"><a class="button jsMarkAllAsConfirmed"><span class="icon icon16 icon-remove"></span> <span>{lang}wcf.user.notification.markAllAsConfirmed{/lang}</span></a></li>{/if}
					
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if $notifications[notifications]}
	<div class="container marginTop">
		<ul class="containerList">
			{foreach from=$notifications[notifications] item=$notification}
				<li class="jsNotificationItem" data-notification-id="{@$notification[notificationID]}" data-link="{$notification[event]->getLink()}">
					<div class="box48">
						{if $notification[event]->getAuthor()->userID}
							<a href="{link controller='User' object=$notification[event]->getAuthor()}{/link}" title="{$notification[event]->getAuthor()->username}" class="framed">{@$notification[event]->getAuthor()->getAvatar()->getImageTag(48)}</a>
						{else}
							<span class="framed">{@$notification[event]->getAuthor()->getAvatar()->getImageTag(48)}</span>
						{/if}	
						
						<div class="details">
							<div class="containerHeadline">
								<h3>
									{if $notification[event]->getAuthor()->userID}
										<a href="{link controller='User' object=$notification[event]->getAuthor()}{/link}" class="userLink" data-user-id="{@$notification[event]->getAuthor()->userID}">{$notification[event]->getAuthor()->username}</a>
									{else}
										{$notification[event]->getAuthor()->username}
									{/if}
								</h3> 
								<small>{@$notification[time]|time}</small>
							</div>
							
							<p>{@$notification[event]->getMessage()}</p>
							
							<nav class="jsMobileNavigation buttonGroupNavigation">
								<ul class="buttonList iconList jsOnly">
									<li><a class="jsMarkAsConfirmed jsTooltip" title="{lang}wcf.user.notification.markAsConfirmed{/lang}"><span class="icon icon16 icon-remove"></span></a></li>
								</ul>
							</nav>
						</div>
					</div>
				</li>
			{/foreach}
		</ul>
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
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
	<p class="info">{lang}wcf.user.notification.noNotifications{/lang}</p>
{/if}

{include file='footer'}

</body>
</html>
