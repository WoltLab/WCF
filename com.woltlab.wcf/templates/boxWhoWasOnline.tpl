{if $whoWasOnlineList|count < 29}
	<ul class="userAvatarList">
		{foreach from=$whoWasOnlineList item=userOnline}
			<li><a href="{$userOnline->getLink()}" title="{$userOnline->username} ({@$userOnline->lastActivityTime|date:$whoWasOnlineTimeFormat})" class="jsTooltip">{@$userOnline->getAvatar()->getImageTag(48)}</a></li>
		{/foreach}
	</ul>
{else}
	<ul class="inlineList commaSeparated">
		{foreach from=$whoWasOnlineList item=userOnline}
			<li>{user object=$userOnline} ({@$userOnline->lastActivityTime|date:$whoWasOnlineTimeFormat})</li>
		{/foreach}
	</ul>
{/if}
