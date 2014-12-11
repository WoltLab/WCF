{foreach from=$notifications[notifications] item=notification}
	<li class="notificationItem{if $notification[event]->getAuthors()|count > 1} groupedNotificationItem{/if}{if !$notification[event]->isConfirmed()} interactiveDropdownItemOutstanding{/if}" data-link="{if $notification[event]->isConfirmed()}{$notification[event]->getLink()}{else}{link controller='NotificationConfirm' id=$notification[notificationID]}{/link}{/if}" data-link-replace-all="{if $notification[event]->isConfirmed()}false{else}true{/if}" data-object-id="{@$notification[notificationID]}" data-is-read="{if $notification[event]->isConfirmed()}true{else}false{/if}">
		<div class="box32">
			<div class="framed">
				{if $notification[event]->getAuthors()|count < 2}
					{@$notification[event]->getAuthor()->getAvatar()->getImageTag(32)}
				{else}
					<span class="icon icon32 fa-users"></span>
				{/if}
			</div>
			
			<div>
				<h3>{@$notification[event]->getMessage()}</h3>
				<small>{@$notification[time]|time}</small>
			</div>
		</div>
	</li>
{/foreach}