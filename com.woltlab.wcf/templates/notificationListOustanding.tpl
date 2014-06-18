{foreach from=$notifications[notifications] item=notification}
	<li class="jsNotificationItem notificationItem{if $notification[event]->getAuthors()|count > 1} groupedNotificationItem{/if}" data-link="{$notification[event]->getLink()}" data-notification-id="{@$notification[notificationID]}">
		<a class="box24">
			{if $notification[event]->getAuthors()|count < 2}
				<div class="framed">
					{@$notification[event]->getAuthor()->getAvatar()->getImageTag(24)}
				</div>
				<div>
					<h3>{@$notification[event]->getMessage()}</h3>
					<small>{$notification[event]->getAuthor()->username} - {@$notification[time]|time}</small>
				</div>
			{else}
				<div>
					<span class="icon icon24 fa-users"></span>
				</div>
				<div>
					<h3>{$notification[event]->getTitle()}</h3>
					<small>{@$notification[time]|time}</small>
				</div>
			{/if}
		</a>
	</li>
{/foreach}