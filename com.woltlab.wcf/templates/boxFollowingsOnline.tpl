<ol class="sidebarList">
	{foreach from=$usersOnlineList item=userOnline}
		<li class="sidebarListItem">
			<div class="sidebarListItem__image">
				{user object=$userOnline type='avatar32' ariaHidden='true' tabindex='-1'}
			</div>

			<div class="sidebarListItem__content">
				<h3 class="sidebarListItem__title">
					{user object=$userOnline class='sidebarListItem__link'}
				</h3>
			</div>

			<div class="sidebarListItem__meta">
				<div class="sidebarListItem__meta__item sidebarListItem__meta__time">
					{time time=$userOnline->lastActivityTime}
				</div>
			</div>
		</li>
	{/foreach}
</ol>
