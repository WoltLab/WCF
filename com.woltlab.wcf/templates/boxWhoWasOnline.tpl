{if $whoWasOnlineList|count < 29}
	<ul class="userAvatarList">
		{foreach from=$whoWasOnlineList item=userOnline}
			<li><a href="{link controller='User' object=$userOnline}{/link}" title="{$userOnline->username} ({@$userOnline->lastActivityTime|date:$whoWasOnlineTimeFormat})" class="jsTooltip">{@$userOnline->getAvatar()->getImageTag(48)}</a></li>
		{/foreach}
	</ul>	
{else}
	<ul class="inlineList commaSeparated">
		{foreach from=$whoWasOnlineList item=userOnline}
			<li><a href="{link controller='User' object=$userOnline->getDecoratedObject()}{/link}" class="userLink" data-user-id="{@$userOnline->userID}">{@$userOnline->getFormattedUsername()}</a> ({@$userOnline->lastActivityTime|date:$whoWasOnlineTimeFormat})</li>
		{/foreach}
	</ul>
{/if}
