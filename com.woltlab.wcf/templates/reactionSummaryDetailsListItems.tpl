{foreach from=$view->getItems() item='reaction'}
	{assign var='user' value=$reaction->getUserProfile()}
	<div class="simpleUserList__item listView__item" data-object-id="{$reaction->getObjectID()}">
		<div class="simpleUserList__item__avatar">
			{unsafe:$user->getAvatar()->getImageTag(96)}
		</div>

		<div class="simpleUserList__item__content">
			<div class="simpleUserList__item__title">
				<h3 class="simpleUserList__item__username">
					<a href="{$user->getLink()}" class="simpleUserList__item__link userLink" data-object-id="{$user->userID}">{unsafe:$user->getFormattedUsername()}</a>
				</h3>
				{if MODULE_USER_RANK && $user->getUserTitle()}
					<span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>
				{/if}
			</div>
			<div class="simpleUserList__item__description">
				{time time=$reaction->time}
			</div>
		</div>

		<div class="simpleUserList__item__extra">
			{unsafe:$reaction->render()}
			
			<div class="simpleUserList__item__interactions">
				{unsafe:$view->renderInteractionContextMenuButton($reaction)}
			</div>
		</div>
	</div>
{/foreach}
