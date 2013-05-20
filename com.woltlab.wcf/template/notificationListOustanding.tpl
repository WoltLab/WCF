{foreach from=$notifications[notifications] item=notification}
	<li class="jsNotificationItem" data-link="{$notification[event]->getLink()}" data-notification-id="{@$notification[notificationID]}">
		<a class="box24">
			<div class="framed">
				{@$notification[event]->getAuthor()->getAvatar()->getImageTag(24)}
			</div>
			<div>
				<h3>{$notification[event]->getMessage()}</h3>
				<small>{$notification[event]->getAuthor()->username} - {@$notification[time]|time}</small>
			</div>
		</a>
	</li>
{/foreach}