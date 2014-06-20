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
	<h1>{lang}wcf.user.notification.notifications{/lang} <span class="badge jsNotificationsBadge">{#$__wcf->getUserNotificationHandler()->countAllNotifications()}</span></h1>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='NotificationList' link="pageNo=%d"}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $__wcf->getUserNotificationHandler()->getNotificationCount()}<li class="jsOnly"><a class="button jsMarkAllAsConfirmed"><span class="icon icon16 fa-check"></span> <span>{lang}wcf.user.notification.markAllAsConfirmed{/lang}</span></a></li>{/if}
					
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if $notifications[notifications]}
	{assign var=lastPeriod value=''}
	
	{foreach from=$notifications[notifications] item=$notification}
		{if $notification[event]->getPeriod() != $lastPeriod}
			{if $lastPeriod}
					</ul>
				</div>
			{/if}
			{assign var=lastPeriod value=$notification[event]->getPeriod()}
			
			<header class="boxHeadline boxSubHeadline">
				<h2>{$lastPeriod}</h2>
			</header>
			
			<div class="container marginTop">
				<ul class="containerList"{* id="userNotificationItemList"*}>
		{/if}
				<li class="jsNotificationItem{if $notification[authors] > 1} groupedNotificationItem{/if}" data-notification-id="{@$notification[notificationID]}" data-link="{$notification[event]->getLink()}" data-is-grouped="{if $notification[authors] > 1}true{else}false{/if}">
					<div class="box48">
						{if $notification[authors] < 2}
							{if $notification[event]->getAuthor()->userID}
								<a href="{link controller='User' object=$notification[event]->getAuthor()}{/link}" title="{$notification[event]->getAuthor()->username}" class="framed">{@$notification[event]->getAuthor()->getAvatar()->getImageTag(48)}</a>
							{else}
								<span class="framed">{@$notification[event]->getAuthor()->getAvatar()->getImageTag(48)}</span>
							{/if}	
							
							<div class="details">
								<div class="containerHeadline">
									<h3>
										{if !$notification[confirmed]}<span class="badge label newContentBadge">{lang}wcf.message.new{/lang}</span>{/if}
										
										{if $notification[event]->getAuthor()->userID}
											<a href="{link controller='User' object=$notification[event]->getAuthor()}{/link}" class="userLink" data-user-id="{@$notification[event]->getAuthor()->userID}">{$notification[event]->getAuthor()->username}</a>
										{else}
											{$notification[event]->getAuthor()->username}
										{/if}
									</h3> 
									<small>{@$notification[time]|time}</small>
								</div>
								
								<p>{@$notification[event]->getMessage()}</p>
								
								{if !$notification[confirmed]}
									<nav class="jsMobileNavigation buttonGroupNavigation">
										<ul class="buttonList iconList jsOnly">
											<li><a class="jsMarkAsConfirmed jsTooltip" title="{lang}wcf.user.notification.markAsConfirmed{/lang}"><span class="icon icon16 fa-check"></span></a></li>
										</ul>
									</nav>
								{/if}
							</div>
						{else}
							<span class="icon icon48 fa-users"></span>
							
							<div class="details">
								<div class="containerHeadline">
									<h3>
										{if !$notification[confirmed]}<span class="badge label newContentBadge">{lang}wcf.message.new{/lang}</span>{/if}
										
										{$notification[event]->getTitle()}
									</h3> 
									<small>{@$notification[time]|time}</small>
								</div>
								
								<p>{@$notification[event]->getMessage()}</p>
								
								<ul style="margin-top: 4px">
									{foreach from=$notification[event]->getAuthors() item=author}
										<li style="display: inline-block" class="jsTooltip" title="{$author->username}"><a href="{link controller='User' object=$author}{/link}" class="framed">{@$author->getAvatar()->getImageTag(24)}</a></li>
									{/foreach}
								</ul>
								
								{if !$notification[confirmed]}
									<nav class="jsMobileNavigation buttonGroupNavigation">
										<ul class="buttonList iconList jsOnly">
											<li><a class="jsMarkAsConfirmed jsTooltip" title="{lang}wcf.user.notification.markAsConfirmed{/lang}"><span class="icon icon16 fa-check"></span></a></li>
										</ul>
									</nav>
								{/if}
							</div>
						{/if}
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
