<ul class="sidebarItemList">
	{foreach from=$usersOnlineList item=userOnline}
		<li class="box32">
			<a href="{link controller='User' object=$userOnline}{/link}" aria-hidden="true">{@$userOnline->getAvatar()->getImageTag(32)}</a>
			
			<div class="sidebarItemTitle">
				<h3>{user object=$userOnline}</h3>
				<small>{@$userOnline->lastActivityTime|time}</small>
			</div>
		</li>
	{/foreach}
</ul>
