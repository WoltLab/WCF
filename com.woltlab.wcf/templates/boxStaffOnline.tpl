<ol class="sidebarList">
	{foreach from=$usersOnlineList item=userOnline}
		<li class="sidebarListItem">
			<div class="sidebarListItem__avatar">
				{user object=$userOnline type='avatar32' ariaHidden='true' tabindex='-1'}
			</div>

			<div class="sidebarListItem__content">
				<h3 class="sidebarListItem__title">
					{event name='beforeUsername'}
					{user object=$userOnline class='sidebarListItem__link'}
				</h3>
			</div>

			{if MODULE_USER_RANK}
				<div class="sidebarListItem__meta">
					<div class="sidebarListItem__meta__userRank">
						{if $userOnline->getUserTitle()}
							<p><span class="badge userTitleBadge{if $userOnline->getRank() && $userOnline->getRank()->cssClassName} {$userOnline->getRank()->cssClassName}{/if}">{$userOnline->getUserTitle()}</span></p>
						{/if}
						{if $userOnline->getRank() && $userOnline->getRank()->rankImage}
							<p><span class="userRankImage">{unsafe:$userOnline->getRank()->getImage()}</span></p>
						{/if}
					</div>
				</div>
			{/if}
		</li>
	{/foreach}
</ol>
