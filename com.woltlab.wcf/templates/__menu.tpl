<nav aria-label="{$menuTitle}">
	<ol class="boxMenu">
		{event name='menuBefore'}
		
		{foreach from=$menuItemNodeList item=menuItemNode}
			<li class="{if $menuItemNode->isActiveNode()}active{/if}{if $menuItemNode->hasChildren()} boxMenuHasChildren{/if}" data-identifier="{@$menuItemNode->identifier}">
				<a {anchorAttributes url=$menuItemNode->getURL() appendClassname=false} class="boxMenuLink"{if $menuItemNode->isActiveNode()} aria-current="page"{/if}>
					<span class="boxMenuLinkTitle">{$menuItemNode->getTitle()}</span>
					{if $menuItemNode->getOutstandingItems() > 0}
						<span class="boxMenuLinkOutstandingItems badge badgeUpdate" aria-label="{lang}wcf.page.menu.outstandingItems{/lang}">{#$menuItemNode->getOutstandingItems()}</span>
					{/if}
				</a>
				
				{if $menuItemNode->hasChildren()}<ol class="boxMenuDepth{@$menuItemNode->getDepth()}">{else}</li>{/if}
				
				{if !$menuItemNode->hasChildren() && $menuItemNode->isLastSibling()}
					{@"</ol></li>"|str_repeat:$menuItemNode->getOpenParentNodes()}
				{/if}
		{/foreach}
		
		{event name='menuAfter'}
	</ol>
</nav>
