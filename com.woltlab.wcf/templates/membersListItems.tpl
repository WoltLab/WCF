{foreach from=$view->getItems() item='user'}
	<div class="listView__item userCardList__item" data-object-id="{$user->getObjectID()}">
		{include file='userCard' quickInteractions=$view->renderQuickInteractions($user) contextMenuButton=$view->renderInteractionContextMenuButton($user)}
	</div>
{/foreach}
