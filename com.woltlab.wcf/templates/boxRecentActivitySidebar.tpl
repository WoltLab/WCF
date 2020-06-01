<ul class="sidebarItemList">
	{foreach from=$eventList item=event}
		<li class="box24{if $__wcf->getUserProfileHandler()->isIgnoredUser($event->getUserProfile()->userID)} ignoredUserContent{/if}">
			<a href="{link controller='User' object=$event->getUserProfile()}{/link}" title="{$event->getUserProfile()->username}">{@$event->getUserProfile()->getAvatar()->getImageTag(24)}</a>
			
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
