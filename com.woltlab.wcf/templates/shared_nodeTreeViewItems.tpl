{foreach from=$view->getNodes() item='node'}
	<li class="nodeTreeView__item" data-object-id="{$node->getObjectID()}">
		<div class="nodeTreeView__item__content">
			{if $view->getSetPositionsEndpoint()}
				<span class="nodeTreeView__item__handle">{icon name='grip-vertical'}</span>
			{/if}
			<a class="nodeTreeView__item__link" href="{$view->getNodeLink($node)}">{$node->getTitle()}</a>
			{if $view->hasInteractions()}
				<div class="nodeTreeView__item__buttons">
					{unsafe:$view->renderQuickInteractions($node)}
					{unsafe:$view->renderInteractionContextMenuButton($node)}
				</div>
			{/if}
		</div>
		
		<ol class="nodeTreeView__list" data-parent-object-id="{$node->getObjectID()}">{if !$node->hasChildren()}</ol></li>{/if}
		
		{if !$node->hasChildren() && $node->isLastSibling()}
			{unsafe:"</ol></li>"|str_repeat:$node->getOpenParentNodes()}
		{/if}
{/foreach}
