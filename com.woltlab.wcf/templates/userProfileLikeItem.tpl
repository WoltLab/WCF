{foreach from=$likeList item=like}
	<li>
		<div class="box48">
			<a href="{link controller='User' object=$like->getUserProfile()}{/link}" title="{$like->getUserProfile()->username}" aria-hidden="true">{@$like->getUserProfile()->getAvatar()->getImageTag(48)}</a>
			
			<div>
				<div class="containerHeadline">
					<h3>
						{user object=$like->getUserProfile()}
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
