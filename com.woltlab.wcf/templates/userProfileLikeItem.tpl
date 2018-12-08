{foreach from=$likeList item=like}
	<li>
		<div class="box48">
			<a href="{link controller='User' object=$like->getUserProfile()}{/link}" title="{$like->getUserProfile()->username}">{@$like->getUserProfile()->getAvatar()->getImageTag(48)}</a>
			
			<div>
				<div class="containerHeadline">
					<h3>
						<a href="{link controller='User' object=$like->getUserProfile()}{/link}" class="userLink" data-user-id="{@$like->getUserProfile()->userID}">{$like->getUserProfile()->username}</a>
						<small class="separatorLeft">{@$like->time|time}</small>
					</h3>
					<div>{@$like->getTitle()}</div>
					<small class="containerContentType">{$like->getObjectTypeDescription()}</small>
				</div>
				
				<div class="containerContent">{@$like->getDescription()}</div>
			</div>
		</div>
	</li>
{/foreach}
