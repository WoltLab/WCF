<ol class="sidebarList">
	{foreach from=$eventList item=event}
		<li class="sidebarListItem{if $__wcf->getUserProfileHandler()->isIgnoredUser($event->getUserProfile()->userID, 2)} ignoredUserContent{/if}">
			<div class="sidebarListItem__image">
				{unsafe:$event->getUserProfile()->getAvatar()->getImageTag(24)}
			</div>

			<div class="sidebarListItem__content">
				<h3 class="sidebarListItem__title">
					{if $event->getLink()}
						<a href="{$event->getLink()}" class="sidebarListItem__link">{unsafe:$event->getTitle()}</a>
					{else}
						{unsafe:$event->getTitle()}
					{/if}
				</h3>
			</div>

			<div class="sidebarListItem__meta">
				<div class="sidebarListItem__meta__item sidebarListItem__meta__author">
					{unsafe:$event->getUserProfile()->getFormattedUsername()}
				</div>
				<div class="sidebarListItem__meta__item sidebarListItem__meta__time">
					{time time=$event->time}
				</div>
			</div>
		</li>
	{/foreach}
</ol>
