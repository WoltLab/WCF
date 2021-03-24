{include file='header' pageTitle='wcf.acp.menu.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.list{/lang} <span class="badge badgeInverse">{#$items}</span></h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='MenuAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="MenuList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\menu\MenuAction">
			<thead>
				<tr>
					<th class="columnID columnMenuID{if $sortField == 'menuID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='MenuList'}pageNo={@$pageNo}&sortField=menuID&sortOrder={if $sortField == 'menuID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle{if $sortField == 'title'} active {@$sortOrder}{/if}"><a href="{link controller='MenuList'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnDigits columnItems{if $sortField == 'items'} active {@$sortOrder}{/if}"><a href="{link controller='MenuList'}pageNo={@$pageNo}&sortField=items&sortOrder={if $sortField == 'items' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.menu.item.list{/lang}</a></th>
					<th class="columnText columnPosition{if $sortField == 'position'} active {@$sortOrder}{/if}"><a href="{link controller='MenuList'}pageNo={@$pageNo}&sortField=position&sortOrder={if $sortField == 'position' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.box.position{/lang}</a></th>
					<th class="columnDigits columnShowOrder{if $sortField == 'showOrder'} active {@$sortOrder}{/if}"><a href="{link controller='MenuList'}pageNo={@$pageNo}&sortField=showOrder&sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.showOrder{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=menu}
					<tr class="jsMenuRow jsObjectActionObject" data-object-id="{@$menu->getObjectID()}">
						<td class="columnIcon">
							<a href="{link controller='MenuEdit' id=$menu->menuID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{if $menu->canDelete()}
								{objectAction action="delete" objectTitle=$menu->getTitle()}
							{else}
								<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{/if}
							<a href="{link controller='MenuItemList' id=$menu->menuID}{/link}" title="{lang}wcf.acp.menu.item.list{/lang}" class="jsTooltip"><span class="icon icon16 fa-list"></span></a>
							<a href="{link controller='MenuItemAdd'}menuID={@$menu->menuID}{/link}" title="{lang}wcf.acp.menu.item.add{/lang}" class="jsTooltip"><span class="icon icon16 fa-plus"></span></a>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnMenuID">{@$menu->menuID}</td>
						<td class="columnTitle"><a href="{link controller='MenuEdit' id=$menu->menuID}{/link}">{$menu->getTitle()}</a></td>
						<td class="columnDigits columnItems"><a href="{link controller='MenuItemList' id=$menu->menuID}{/link}">{#$menu->items}</a></td>
						<td class="columnText columnPosition">{lang}wcf.acp.box.position.{@$menu->position}{/lang}</td>
						<td class="columnDigits columnShowOrder">{#$menu->showOrder}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="{link controller='MenuAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
