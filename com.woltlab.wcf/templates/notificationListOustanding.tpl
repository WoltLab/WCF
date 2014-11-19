{foreach from=$notifications[notifications] item=notification}
	<li class="jsNotificationItem notificationItem{if $notification[event]->getAuthors()|count > 1} groupedNotificationItem{/if}" data-link="{$notification[event]->getLink()}" data-notification-id="{@$notification[notificationID]}">
		<span class="box24">
			{if $notification[event]->getAuthors()|count < 2}
				<div class="framed">
					{@$notification[event]->getAuthor()->getAvatar()->getImageTag(24)}
				</div>
			{else}
				<div>
					<span class="icon icon24 fa-users"></span>
				</div>
			{/if}
			
			<div>
				<h3>{@$notification[event]->getMessage()}</h3>
				<small>{@$notification[time]|time}</small>
			</div>
		</span>
	</li>
{/foreach}