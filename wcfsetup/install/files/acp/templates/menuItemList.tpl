{include file='header' pageTitle='wcf.acp.menu.item.list'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Sortable/List'], function (UiSortableList) {
		new UiSortableList({
			containerId: 'menuItemList',
			className: 'wcf\\data\\menu\\item\\MenuItemAction',
			options: {
				protectRoot: true
			},
			additionalParameters: {
				menuID: '{@$menuID}'
			}
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.item.list{/lang}</h1>
		<p class="contentHeaderDescription">{$menu->getTitle()}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='MenuEdit' id=$menuID}{/link}" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.acp.menu.edit{/lang}</span></a></li>
			<li><a href="{link controller='MenuItemAdd'}menuID={@$menuID}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.item.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div id="menuItemList" class="section sortableListContainer">
		<ol class="sortableList jsReloadPageWhenEmpty jsObjectActionContainer" data-object-action-class-name="wcf\data\menu\item\MenuItemAction" data-object-id="0">
			{content}
				{foreach from=$menuItemNodeList item=menuItemNode}
					<li class="sortableNode jsObjectActionObject" data-object-id="{@$menuItemNode->getObjectID()}">
						<span class="sortableNodeLabel">
							<a href="{link controller='MenuItemEdit' id=$menuItemNode->itemID}{/link}">{$menuItemNode->getTitle()}</a>
							<span class="statusDisplay sortableButtonContainer">
								<span class="icon icon16 fa-arrows sortableNodeHandle"></span>
								{if $menuItemNode->canDisable()}
									{objectAction action="toggle" isDisabled=$menuItemNode->isDisabled}
								{else}
									<span class="icon icon16 fa-{if !$menuItemNode->isDisabled}check-{/if}square-o disabled" title="{lang}wcf.global.button.{if $menuItemNode->isDisabled}enable{else}disable{/if}{/lang}"></span>
								{/if}
								<a href="{link controller='MenuItemEdit' id=$menuItemNode->itemID}{/link}" class="jsTooltip" title="{lang}wcf.global.button.edit{/lang}"><span class="icon icon16 fa-pencil"></span></a>
								{if $menuItemNode->canDelete()}
									{objectAction action="delete" objectTitle=$menuItemNode->getTitle()}
								{else}
									<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
								{/if}
								
								{event name='itemButtons'}
							</span>
						</span>
					
						<ol class="sortableList jsObjectActionObjectChildren" data-object-id="{@$menuItemNode->itemID}">{if !$menuItemNode->hasChildren()}</ol></li>{/if}
						
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
