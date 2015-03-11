{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.notification.notifications{/lang} - {lang}wcf.user.usercp{/lang} - {PAGE_TITLE|language}</title>
	{include file='headInclude'}
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.user.notification.markAsConfirmed': '{lang}wcf.user.notification.markAsConfirmed{/lang}',
				'wcf.user.notification.markAllAsConfirmed.confirmMessage': '{lang}wcf.user.notification.markAllAsConfirmed.confirmMessage{/lang}'
			});
			
			new WCF.Notification.List();
		});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

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
				<ul class="containerList userNotificationItemList">
		{/if}
				<li class="jsNotificationItem notificationItem{if $notification[authors] > 1} groupedNotificationItem{/if}{if !$notification[event]->isConfirmed()} notificationUnconfirmed{/if}" data-link="{if $notification[event]->isConfirmed()}{$notification[event]->getLink()}{else}{link controller='NotificationConfirm' id=$notification[notificationID]}{/link}{/if}" data-link-replace-all="{if $notification[event]->isConfirmed()}false{else}true{/if}" data-object-id="{@$notification[notificationID]}" data-is-read="{if $notification[event]->isConfirmed()}true{else}false{/if}" data-is-grouped="{if $notification[authors] > 1}true{else}false{/if}">
					<div class="box32">
						{if $notification[authors] < 2}
							<div class="framed jsTooltip" title="{$notification[event]->getAuthor()->username}">
								{@$notification[event]->getAuthor()->getAvatar()->getImageTag(32)}
							</div>
							
							<div class="details">
								<p>
									{if !$notification[confirmed]}<span class="badge label newContentBadge">{lang}wcf.message.new{/lang}</span>{/if}
									{@$notification[event]->getMessage()}
								</p>
								<p><small>{@$notification[time]|time}</small></p>
							</div>
						{else}
							<div class="framed">
								<span class="icon icon32 fa-users"></span>
							</div>
							
							<div class="details">
								<p>
									{if !$notification[confirmed]}<span class="badge label newContentBadge">{lang}wcf.message.new{/lang}</span>{/if}
									{@$notification[event]->getMessage()}
								</p>
								<p><small>{@$notification[time]|time}</small></p>
								
								<ul class="marginTopTiny">
									{foreach from=$notification[event]->getAuthors() item=author}
										{if $author->userID}
											<li style="display: inline-block" class="jsTooltip" title="{$author->username}"><a href="{link controller='User' object=$author}{/link}" class="framed">{@$author->getAvatar()->getImageTag(24)}</a></li>
										{/if}
									{/foreach}
								</ul>
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
