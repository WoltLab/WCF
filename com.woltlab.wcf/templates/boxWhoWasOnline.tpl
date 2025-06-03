{if $whoWasOnlineList|count < 29}
	<ul class="userAvatarList">
		{foreach from=$whoWasOnlineList item=userOnline}
			<li><a href="{$userOnline->getLink()}" title="{$userOnline->username} ({time type='custom' time=$userOnline->lastActivityTime format=$whoWasOnlineTimeFormat})" class="jsTooltip">{unsafe:$userOnline->getAvatar()->getImageTag(48)}</a></li>
		{/foreach}
	</ul>
{else}
	<ul class="inlineList commaSeparated">
		{foreach from=$whoWasOnlineList item=userOnline}
			<li>{user object=$userOnline} ({time type='custom' time=$userOnline->lastActivityTime format=$whoWasOnlineTimeFormat})</li>
		{/foreach}
	</ul>
{/if}
