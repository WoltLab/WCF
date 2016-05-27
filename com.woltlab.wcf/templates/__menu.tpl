<nav>
	<ol class="boxMenu">
		{foreach from=$menuItemNodeList item=menuItemNode}
			<li class="{if $menuItemNode->isActiveNode()}active{/if}{if $menuItemNode->hasChildren()} boxMenuHasChildren{/if}">
				<a href="{$menuItemNode->getURL()}" class="boxMenuLink">
					<span class="boxMenuLinkTitle">{lang}{$menuItemNode->title}{/lang}</span>
					{if $menuItemNode->getOutstandingItems() > 0}
						<span class="boxMenuLinkOutstandingItems badge badgeUpdate">{#$menuItemNode->getOutstandingItems()}</span>
					{/if}
				</a>
				
				{if $menuItemNode->hasChildren()}<ol class="boxMenuDepth{@$menuItemNode->getDepth()}">{else}</li>{/if}
					
				{if !$menuItemNode->hasChildren() && $menuItemNode->isLastSibling()}
					{@"</ol></li>"|str_repeat:$menuItemNode->getOpenParentNodes()}
				{/if}
		{/foreach}
	</ol>
</nav>
