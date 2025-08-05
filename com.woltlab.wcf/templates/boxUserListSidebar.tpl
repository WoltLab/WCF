<ol class="sidebarList">
	{foreach from=$boxUsers item=boxUser}
		<li class="sidebarListItem">
			<div class="sidebarListItem__image">
				{user object=$boxUser type='avatar32' ariaHidden='true' tabindex='-1'}
			</div>

			<div class="sidebarListItem__content">
				<h3 class="sidebarListItem__title">
					{user object=$boxUser class='sidebarListItem__link'}
				</h3>
			</div>

			<div class="sidebarListItem__meta">
				{if $boxSortField == 'activityPoints'}
					<div class="sidebarListItem__meta__item sidebarListItem__meta__points">
						{lang}wcf.user.boxList.description.activityPoints{/lang}
					</div>
				{elseif $boxSortField == 'likesReceived'}
					<div class="sidebarListItem__meta__item sidebarListItem__meta__likes">
						{lang}wcf.user.boxList.description.likesReceived{/lang}
					</div>
				{elseif $boxSortField == 'registrationDate'}
					<div class="sidebarListItem__meta__item sidebarListItem__meta__time">
						{time time=$boxUser->registrationDate}
					</div>
				{/if}
			</div>
		</li>
	{/foreach}
</ol>
