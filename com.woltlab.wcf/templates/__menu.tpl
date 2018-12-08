<nav>
	<ol class="boxMenu">
		{event name='menuBefore'}
		
		{foreach from=$menuItemNodeList item=menuItemNode}
			<li class="{if $menuItemNode->isActiveNode()}active{/if}{if $menuItemNode->hasChildren()} boxMenuHasChildren{/if}" data-identifier="{@$menuItemNode->identifier}">
				<a href="{$menuItemNode->getURL()}" class="boxMenuLink"{if $menuItemNode->isExternalLink()}{if EXTERNAL_LINK_REL_NOFOLLOW} rel="nofollow"{/if}{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}{/if}>
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
					
		{event name='menuAfter'}
	</ol>
</nav>
