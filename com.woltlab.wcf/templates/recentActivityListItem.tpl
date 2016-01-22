{foreach from=$eventList item=event}
	<li>
		<div class="box48">
			<a href="{link controller='User' object=$event->getUserProfile()}{/link}" title="{$event->getUserProfile()->username}" class="framed">{@$event->getUserProfile()->getAvatar()->getImageTag(48)}</a>
			
			<div>
				<div class="containerHeadline">
					<h3>
						<a href="{link controller='User' object=$event->getUserProfile()}{/link}" class="userLink" data-user-id="{@$event->getUserProfile()->userID}">{$event->getUserProfile()->username}</a>
						<small class="separatorLeft">{@$event->time|time}</small>
					</h3> 
					<p>{@$event->getTitle()}</p>
					<small class="containerContentType">{lang}wcf.user.recentActivity.{@$event->getObjectTypeName()}{/lang}</small>
				</div>
				
				<div>{@$event->getDescription()}</div>
			</div>
		</div>
	</li>
{/foreach}