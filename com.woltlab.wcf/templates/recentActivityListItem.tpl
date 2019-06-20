{foreach from=$eventList item=event}
	<li>
		<div class="box48{if $__wcf->getUserProfileHandler()->isIgnoredUser($event->getUserProfile()->userID)} ignoredUserContent{/if}">
			<a href="{link controller='User' object=$event->getUserProfile()}{/link}" title="{$event->getUserProfile()->username}">{@$event->getUserProfile()->getAvatar()->getImageTag(48)}</a>
			
			<div>
				<div class="containerHeadline">
					{event name='containerHeadline'}
					
					<h3>
						<a href="{link controller='User' object=$event->getUserProfile()}{/link}" class="userLink" data-user-id="{@$event->getUserProfile()->userID}">{$event->getUserProfile()->username}</a>
						<small class="separatorLeft">{@$event->time|time}</small>
					</h3>
					<div>{@$event->getTitle()}</div>
					<small class="containerContentType">{lang}wcf.user.recentActivity.{@$event->getObjectTypeName()}{/lang}</small>
				</div>
				
				{if $event->getDescription()}
					<div class="containerContent{if !$event->isRawHtml()} htmlContent{/if}">{@$event->getDescription()}</div>
				{/if}
			</div>
		</div>
	</li>
{/foreach}
