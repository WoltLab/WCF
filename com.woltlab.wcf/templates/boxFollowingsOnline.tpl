<ul class="sidebarBoxList">
	{foreach from=$usersOnlineList item=userOnline}
		<li class="box32">
			<a href="{link controller='User' object=$userOnline}{/link}">{@$userOnline->getAvatar()->getImageTag(32)}</a>
			
			<div class="sidebarBoxHeadline">
				<h3><a href="{link controller='User' object=$userOnline}{/link}" class="userLink" data-user-id="{@$userOnline->userID}">{$userOnline->username}</a></h3>
				<small>{@$userOnline->lastActivityTime|time}</small>
			</div>
		</li>
	{/foreach}
</ul>
