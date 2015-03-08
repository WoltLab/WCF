<ul class="sidebarBoxList">
	{foreach from=$usersOnlineList item=userOnline}
		<li class="box32">
			<a href="{link controller='User' object=$userOnline}{/link}" class="framed">{@$userOnline->getAvatar()->getImageTag(32)}</a>
			
			<div class="sidebarBoxHeadline">
				<h3><a href="{link controller='User' object=$userOnline}{/link}" class="userLink" data-user-id="{@$userOnline->userID}">{$userOnline->username}</a></h3>
				{if MODULE_USER_RANK}
					<small>
						{if $userOnline->getUserTitle()}
							<span class="badge userTitleBadge{if $userOnline->getRank() && $userOnline->getRank()->cssClassName} {@$userOnline->getRank()->cssClassName}{/if}">{$userOnline->getUserTitle()}</span>
						{/if}
						{if $userOnline->getRank() && $userOnline->getRank()->rankImage}
							<span class="userRankImage">{@$userOnline->getRank()->getImage()}</span>
						{/if}
					</small>
				{/if}
			</div>
		</li>
	{/foreach}
</ul>
