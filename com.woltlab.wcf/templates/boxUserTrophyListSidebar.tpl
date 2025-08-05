<ol class="sidebarList">
	{foreach from=$boxUserTrophyList item=boxUserTrophy}
		<li class="sidebarListItem">
			<div class="sidebarListItem__image">
				{unsafe:$boxUserTrophy->getTrophy()->renderTrophy(32)}
			</div>

			<div class="sidebarListItem__content">
				<h3 class="sidebarListItem__title">
					<a href="{$boxUserTrophy->getTrophy()->getLink()}" class="sidebarListItem__link">
						{$boxUserTrophy->getTrophy()->getTitle()}
					</a>
				</h3>
			</div>

			<div class="sidebarListItem__meta">
				<div class="sidebarListItem__meta__item sidebarListItem__meta__avatar">
					{unsafe:$boxUserTrophy->getUserProfile()->getAvatar()->getImageTag(16)}
				</div>
				<div class="sidebarListItem__meta__item sidebarListItem__meta__author">
					{user object=$boxUserTrophy->getUserProfile() tabindex='-1'}
				</div>
				<div class="sidebarListItem__meta__item sidebarListItem__meta__time">
					{time time=$boxUserTrophy->time}
				</div>
			</div>
		</li>
	{/foreach}
</ol>
