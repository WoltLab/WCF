<ul class="sidebarItemList">
	{foreach from=$usersOnlineList item=userOnline}
		<li class="box32">
			{user object=$userOnline type='avatar32' ariaHidden='true' tabindex='-1'}
			
			<div class="sidebarItemTitle">
				<h3>{user object=$userOnline}</h3>
				<small>{@$userOnline->lastActivityTime|time}</small>
			</div>
		</li>
	{/foreach}
</ul>
