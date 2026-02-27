{foreach from=$likeList item='like'}
	<div class="
		recentActivityListItem
		recentActivityListItem--withDescription
		{if $like->isIgnoredContent()}ignoredUserContent{/if}
	">
		<div class="recentActivityListItem__avatar">
			{user object=$like->getUserProfile() type='avatar48' ariaHidden='true' tabindex='-1'}
		</div>

		<h3 class="recentActivityListItem__title">
			{if $like->getLink()}
				<a href="{$like->getLink()}" class="recentActivityListItem__link">{unsafe:$like->getTitle()}</a>
			{else}
				{unsafe:$like->getTitle()}
			{/if}
		</h3>

		{if $like->getDescription()}
			<div class="recentActivityListItem__description">
				{unsafe:$like->getDescription()}
			</div>
		{/if}

		<div class="recentActivityListItem__time">
			{time time=$like->time}
		</div>
	</div>
{/foreach}
