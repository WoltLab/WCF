<ul class="sidebarItemList">
	{foreach from=$eventList item=event}
		<li class="box24{if $__wcf->getUserProfileHandler()->isIgnoredUser($event->getUserProfile()->userID, 2)} ignoredUserContent{/if}">
			{user object=$event->getUserProfile() type='avatar24' ariaHidden='true' tabindex='-1'}
			
			<div class="sidebarItemTitle">
				<h3>
					{user object=$event->getUserProfile()}
					<small class="separatorLeft">{@$event->time|time}</small>
				</h3>
				<small>{@$event->getTitle()}</small>
			</div>
		</li>
	{/foreach}
</ul>
