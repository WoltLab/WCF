{capture assign='contentTitleBadge'}<span class="badge jsNotificationsBadge">{#$__wcf->getUserNotificationHandler()->countAllNotifications()}</span>{/capture}

{capture assign='headContent'}
	<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='NotificationRssFeed'}at={$__wcf->getUser()->userID}-{$__wcf->getUser()->accessToken}{/link}">
{/capture}

{capture assign='contentInteractionPagination'}
	{if $pages > 1}
		<woltlab-core-pagination page="{$pageNo}" count="{$pages}" url="{link controller='NotificationList'}{/link}"></woltlab-core-pagination>
	{/if}
{/capture}

{capture assign='contentInteractionButtons'}
	{if $__wcf->getUserNotificationHandler()->getNotificationCount()}
		<button type="button" class="jsMarkAllAsConfirmed contentInteractionButton button small jsOnly">{icon name='check'} <span>{lang}wcf.global.button.markAllAsRead{/lang}</span></button>
	{/if}
{/capture}

{capture assign='contentInteractionDropdownItems'}
	<li><a rel="alternate" href="{link controller='NotificationRssFeed'}at={$__wcf->getUser()->userID}-{$__wcf->getUser()->accessToken}{/link}">{lang}wcf.global.button.rss{/lang}</a></li>
{/capture}

{include file='header'}

{if $notifications[notifications]}
	{assign var=lastPeriod value=''}
	
	{foreach from=$notifications[notifications] item=$notification}
		{if $notification[event]->getPeriod() != $lastPeriod}
			{if $lastPeriod}
					</div>
				</section>
			{/if}
			{assign var=lastPeriod value=$notification[event]->getPeriod()}
			
			<section class="section sectionContainerList">
				<h2 class="sectionTitle">{$lastPeriod}</h2>
			
				<div class="notificationList">
		{/if}
				{capture assign='__notificationLink'}{if $notification[event]->isConfirmed()}{$notification[event]->getLink()}{else}{link controller='NotificationConfirm' id=$notification[notificationID]}{/link}{/if}{/capture}
				
				<div 
					class="notificationListItem"
					data-object-id="{$notification[notificationID]}"
					data-is-read="{if $notification[event]->isConfirmed()}true{else}false{/if}"
				>
					<div class="notificationListItem__avatar">
						{if $notification[authors] < 2}
							{user object=$notification[event]->getAuthor() type='avatar48' ariaHidden='true' tabindex='-1'}
						{else}
							{icon size=48 name='users'}
						{/if}
					</div>

					<h3 class="notificationListItem__title">
						<a href="{unsafe:$__notificationLink}" class="notificationListItem__link">{unsafe:$notification[event]->getMessage()}</a>
					</h3>

					<div class="notificationListItem__time">
						{time time=$notification[time]}
					</div>

					{if $notification[authors] > 1}
						<div class="notificationListItem__authors">
							<ul class="userAvatarList small">
								{foreach from=$notification[event]->getAuthors() item=author}
									{if $author->userID}
										<li class="jsTooltip" title="{$author->username}">{user object=$author type='avatar24'}</li>
									{/if}
								{/foreach}
							</ul>
						</div>
					{/if}

					{if !$notification[event]->isConfirmed()}
						<div class="notificationListItem__unread">
							<button type="button" class="notificationListItem__markAsRead jsTooltip" title="{lang}wcf.global.button.markAsRead{/lang}">
								{icon name='check'}
							</button>
						</div>
					{/if}
				</div>
	{/foreach}
		</ul>
	</section>
	
	<footer class="contentFooter">
		{if $pages > 1}
			<div class="paginationBottom">
				<woltlab-core-pagination page="{$pageNo}" count="{$pages}" url="{link controller='NotificationList'}{/link}"></woltlab-core-pagination>
			</div>
		{/if}
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}{event name='contentFooterNavigation'}{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<woltlab-core-notice type="info">{lang}wcf.user.notification.noNotifications{/lang}</woltlab-core-notice>
{/if}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Controller/User/Notification/List'], ({ setup }) => {
		{jsphrase name='wcf.user.notification.markAllAsConfirmed.confirmMessage'}
		
		setup();
	});
</script>

{include file='footer'}
