<li class="nodeTreeView__item" data-object-id="{$node->getObjectID()}">
	<div class="nodeTreeView__item__content">
		<span class="nodeTreeView__item__handle">{icon name='grip-vertical'}</span>
		<a class="nodeTreeView__item__link" href="{$view->getNodeLink($node)}">{$node->getTitle()}</a>
		{if $view->hasInteractions()}
			<div class="nodeTreeView__item__buttons">
				{unsafe:$view->renderQuickInteractions($node)}
				{unsafe:$view->renderInteractionContextMenuButton($node)}
			</div>
		{/if}
	</div>

	<ol class="nodeTreeView__list" data-parent-object-id="{$node->getObjectID()}">
		{foreach from=$node item='child'}
			{unsafe:$view->renderItem($child)}
		{/foreach}
	</ol>
</li>
