<ol>
	{foreach from=$menuItemNodeList item=menuItemNode}
		<li>
			<a href="{$menuItemNode->getMenuItem()->getURL()}">{lang}{$menuItemNode->getMenuItem()->title}{/lang}</a>
			
			{if $menuItemNode->hasChildren()}<ol>{else}</li>{/if}
				
			{if !$menuItemNode->hasChildren() && $menuItemNode->isLastSibling()}
				{@"</ol></li>"|str_repeat:$menuItemNode->getOpenParentNodes()}
			{/if}
	{/foreach}
</ol>
