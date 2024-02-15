{foreach from=$eventList item=event}
	<div class="
		recentActivityListItem
		{if $event->isIgnoredContent()}ignoredUserContent{/if}
		{if !$event->getDescription()}recentActivityListItem--compact{/if}
	">
		<div class="recentActivityListItem__avatar">
			{user object=$event->getUserProfile() type='avatar48' ariaHidden='true' tabindex='-1'}
		</div>

		<h3 class="recentActivityListItem__title">
			{if $event->getLink()}
				<a href="{$event->getLink()}" class="recentActivityListItem__link">{unsafe:$event->getTitle()}</a>
			{else}
				{unsafe:$event->getTitle()}
			{/if}
		</h3>

		{if $event->getDescription()}
			<div class="recentActivityListItem__description{if !$event->isRawHtml()} htmlContent{/if}">
				{unsafe:$event->getDescription()}
			</div>
		{/if}

		<div class="recentActivityListItem__time">
			{time time=$event->time}
		</div>
	</div>
{/foreach}
