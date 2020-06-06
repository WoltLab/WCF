<ul class="sidebarItemList">
	{foreach from=$usersOnlineList item=userOnline}
		<li class="box32">
			{user object=$userOnline type='avatar32' ariaHidden='true'}
			
			<div class="sidebarItemTitle">
				<h3>
					{event name='beforeUsername'}
					{user object=$userOnline}
				</h3>
				{if MODULE_USER_RANK}
					{if $userOnline->getUserTitle()}
						<p><span class="badge userTitleBadge{if $userOnline->getRank() && $userOnline->getRank()->cssClassName} {@$userOnline->getRank()->cssClassName}{/if}">{$userOnline->getUserTitle()}</span></p>
					{/if}
					{if $userOnline->getRank() && $userOnline->getRank()->rankImage}
						<p><span class="userRankImage">{@$userOnline->getRank()->getImage()}</span></p>
					{/if}
				{/if}
			</div>
		</li>
	{/foreach}
</ul>
