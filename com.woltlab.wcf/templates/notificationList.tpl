{capture assign='contentTitleBadge'}<span class="badge jsNotificationsBadge">{#$__wcf->getUserNotificationHandler()->countAllNotifications()}</span>{/capture}

{capture assign='headContent'}
	<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='NotificationRssFeed'}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}">
{/capture}

{capture assign='contentInteractionPagination'}
	{pages print=true assign=pagesLinks controller='NotificationList' link="pageNo=%d"}
{/capture}

{capture assign='contentInteractionButtons'}
	{if $__wcf->getUserNotificationHandler()->getNotificationCount()}
		<button type="button" class="jsMarkAllAsConfirmed contentInteractionButton button small jsOnly">{icon name='check'} <span>{lang}wcf.global.button.markAllAsRead{/lang}</span></button>
	{/if}
{/capture}

{capture assign='contentInteractionDropdownItems'}
	<li><a rel="alternate" href="{link controller='NotificationRssFeed'}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}">{lang}wcf.global.button.rss{/lang}</a></li>
{/capture}

{include file='header'}

{if $notifications[notifications]}
	{assign var=lastPeriod value=''}
	
	{foreach from=$notifications[notifications] item=$notification}
		{if $notification[event]->getPeriod() != $lastPeriod}
			{if $lastPeriod}
					</ul>
				</section>
			{/if}
			{assign var=lastPeriod value=$notification[event]->getPeriod()}
			
			<section class="section sectionContainerList">
				<h2 class="sectionTitle">{$lastPeriod}</h2>
			
				<ul class="containerList userNotificationItemList">
		{/if}
				{capture assign='__notificationLink'}{if $notification[event]->isConfirmed()}{$notification[event]->getLink()}{else}{link controller='NotificationConfirm' id=$notification[notificationID]}{/link}{/if}{/capture}
				<li class="jsNotificationItem notificationItem{if $notification[authors] > 1} groupedNotificationItem{/if}{if !$notification[event]->isConfirmed()} notificationUnconfirmed{/if}" data-link="{@$__notificationLink}" data-link-replace-all="{if $notification[event]->isConfirmed()}false{else}true{/if}" data-object-id="{@$notification[notificationID]}" data-is-read="{if $notification[event]->isConfirmed()}true{else}false{/if}" data-is-grouped="{if $notification[authors] > 1}true{else}false{/if}">
					<div class="box32">
						{if $notification[authors] < 2}
							<div class="jsTooltip" title="{$notification[event]->getAuthor()->username}">
								{@$notification[event]->getAuthor()->getAvatar()->getImageTag(32)}
							</div>
							
							<div class="details">
								<p>
									{if !$notification[confirmed]}<span class="badge label newContentBadge">{lang}wcf.message.new{/lang}</span>{/if}
									<a href="{@$__notificationLink}" class="userNotificationItemLink">{@$notification[event]->getMessage()}</a>
								</p>
								<p><small>{@$notification[time]|time}</small></p>
							</div>
						{else}
							<div>
								{icon size=32 name='users'}
							</div>
							
							<div class="details">
								<p>
									{if !$notification[confirmed]}<span class="badge label newContentBadge">{lang}wcf.message.new{/lang}</span>{/if}
									<a href="{@$__notificationLink}" class="userNotificationItemLink">{@$notification[event]->getMessage()}</a>
								</p>
								<p><small>{@$notification[time]|time}</small></p>
								
								<ul class="userAvatarList small">
									{foreach from=$notification[event]->getAuthors() item=author}
										{if $author->userID}
											<li class="jsTooltip" title="{$author->username}">{user object=$author type='avatar24'}</li>
										{/if}
									{/foreach}
								</ul>
							</div>
						{/if}
					</div>
				</li>
	{/foreach}
		</ul>
	</section>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
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
	$(function() {
		WCF.Language.addObject({
			'wcf.user.notification.markAllAsConfirmed.confirmMessage': '{jslang}wcf.user.notification.markAllAsConfirmed.confirmMessage{/jslang}'
		});
		
		new WCF.Notification.List();
	});
</script>

{include file='footer'}
