{include file='header' pageTitle='wcf.acp.menu.item.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\menu\\item\\MenuItemAction', '.sortableNode', '> .sortableNodeLabel .jsDeleteButton');
		new WCF.Action.Toggle('wcf\\data\\menu\\item\\MenuItemAction', '.sortableNode', '> .sortableNodeLabel .jsToggleButton');
		new WCF.Sortable.List('menuItemList', 'wcf\\data\\menu\\item\\MenuItemAction', undefined, { protectRoot: true }, false, { menuID: '{@$menuID}' });
	});
	//]]>
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.menu.item.list{/lang}</h1>
</header>

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='MenuItemAdd'}menuID={@$menuID}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.item.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{hascontent}
	<div id="menuItemList" class="section sortableListContainer">
		<ol class="sortableList" data-object-id="0">
			{content}
				{foreach from=$menuItemNodeList item=menuItemNode}
					<li class="sortableNode" data-object-id="{@$menuItemNode->getMenuItem()->itemID}">
						<span class="sortableNodeLabel">
							<a href="{link controller='MenuItemEdit' id=$menuItemNode->getMenuItem()->itemID}{/link}">{lang}{$menuItemNode->getMenuItem()->title}{/lang}</a>
							<span class="statusDisplay sortableButtonContainer">
								{if $menuItemNode->getMenuItem()->canDisable()}
									<span class="icon icon16 fa-{if !$menuItemNode->getMenuItem()->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $menuItemNode->getMenuItem()->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$menuItemNode->getMenuItem()->itemID}"></span>
								{else}
									<span class="icon icon16 fa-{if !$menuItemNode->getMenuItem()->isDisabled}check-{/if}square-o disabled" title="{lang}wcf.global.button.{if $menuItemNode->getMenuItem()->isDisabled}enable{else}disable{/if}{/lang}"></span>
								{/if}
								<a href="{link controller='MenuItemEdit' id=$menuItemNode->getMenuItem()->itemID}{/link}" class="jsTooltip" title="{lang}wcf.global.button.edit{/lang}"><span class="icon icon16 fa-pencil"></span></a>
								{if $menuItemNode->getMenuItem()->canDelete()}
									<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$menuItemNode->getMenuItem()->itemID}" data-confirm-message="{lang menuItem=$menuItemNode->getMenuItem()}wcf.acp.menu.item.delete.confirmMessage{/lang}"></span>
								{else}
									<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
								{/if}
								
								{event name='itemButtons'}
							</span>
						</span>
					
						<ol class="sortableList" data-object-id="{@$menuItemNode->getMenuItem()->itemID}">{if !$menuItemNode->hasChildren()}</ol></li>{/if}
						
						{if !$menuItemNode->hasChildren() && $menuItemNode->isLastSibling()}
							{@"</ol></li>"|str_repeat:$menuItemNode->getOpenParentNodes()}
						{/if}
				{/foreach}
			{/content}
		</ol>
	</div>
	
	<div class="formSubmit">
		<button class="button buttonPrimary" data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/hascontent}

{include file='footer'}
