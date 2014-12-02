{foreach from=$notifications[notifications] item=notification}
	<li class="jsNotificationItem notificationItem{if $notification[event]->getAuthors()|count > 1} groupedNotificationItem{/if}{if !$notification[event]->isConfirmed()} notificationUnconfirmed{/if}" data-link="{$notification[event]->getLink()}" data-confirm-link="{link controller='NotificationConfirm' id=$notification[notificationID]}{/link}" data-notification-id="{@$notification[notificationID]}" data-is-confirmed="{if $notification[event]->isConfirmed()}true{else}false{/if}">
		<span class="box24">
			<div class="framed">
				{if $notification[event]->getAuthors()|count < 2}
					{@$notification[event]->getAuthor()->getAvatar()->getImageTag(24)}
				{else}
					<span class="icon icon24 fa-users"></span>
				{/if}
			</div>
			
			<div>
				<h3>{if !$notification[event]->isConfirmed()}<span class="badge label newContentBadge">{lang}wcf.message.new{/lang}</span>{/if} {@$notification[event]->getMessage()}</h3>
				<small>{@$notification[time]|time}</small>
			</div>
		</span>
	</li>
{/foreach}