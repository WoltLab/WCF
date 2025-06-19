{include file='header' pageTitle='wcf.acp.menu.item.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.item.{$action}{/lang}</h1>
		<p class="contentHeaderDescription">{lang}wcf.acp.menu.item.action.description{/lang}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				{*
				Technically this dropdown should check whether the number of menu items is larger than one,
				but this is non-trivial with the iterator. It's unlikely that there's only a single menu item,
				thus we let this slip.
				*}
				<li class="dropdown">
					<a class="button dropdownToggle">{icon name='sort'} <span>{lang}wcf.acp.menu.item.button.choose{/lang}</span></a>
					<div class="dropdownMenu">
						<ul class="scrollableDropdownMenu">
							{foreach from=$menuItemNodeList item='menuItemNode'}
								<li{if $menuItemNode->itemID == $formObject->itemID} class="active"{/if}><a href="{link controller='MenuItemEdit' object=$menuItemNode}{/link}">{if $menuItemNode->getDepth() > 1}{unsafe:"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($menuItemNode->getDepth() - 1)}{/if}{$menuItemNode->getTitle()}</a></li>
							{/foreach}
						</ul>
					</div>
				</li>
			{/if}
			<li><a href="{link controller='MenuItemList' id=$menuID}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.item.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{unsafe:$form->getHtml()}

{include file='footer'}
