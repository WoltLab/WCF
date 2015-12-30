<nav id="mainMenu" class="mainMenu">
	<ol>
		{foreach from=$menuItemNodeList item=menuItemNode}
			<li{if $menuItemNode->hasChildren()} class="subMenuItems"{/if}>
				<a href="{$menuItemNode->getMenuItem()->getURL()}">{lang}{$menuItemNode->getMenuItem()->title}{/lang}</a>
				
				{if $menuItemNode->hasChildren()}<ol class="subMenu">{else}</li>{/if}
					
				{if !$menuItemNode->hasChildren() && $menuItemNode->isLastSibling()}
					{@"</ol></li>"|str_repeat:$menuItemNode->getOpenParentNodes()}
				{/if}
		{/foreach}
	</ol>
</nav>
