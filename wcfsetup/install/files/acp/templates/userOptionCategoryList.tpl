{include file='header' pageTitle='wcf.acp.user.option.category.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.option.category.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='UserOptionCategoryAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.user.option.category.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>

</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="UserOptionCategoryList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\user\option\category\UserOptionCategoryAction">
			<thead>
				<tr>
					<th class="columnID columnCategoryID{if $sortField == 'categoryID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='UserOptionCategoryList'}pageNo={@$pageNo}&sortField=categoryID&sortOrder={if $sortField == 'categoryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnCategoryName{if $sortField == 'categoryName'} active {@$sortOrder}{/if}"><a href="{link controller='UserOptionCategoryList'}pageNo={@$pageNo}&sortField=categoryName&sortOrder={if $sortField == 'categoryName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnDigits columnOptions{if $sortField == 'options'} active {@$sortOrder}{/if}"><a href="{link controller='UserOptionCategoryList'}pageNo={@$pageNo}&sortField=options&sortOrder={if $sortField == 'options' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.option.category.options{/lang}</a></th>
					<th class="columnDigits columnShowOrder{if $sortField == 'showOrder'} active {@$sortOrder}{/if}"><a href="{link controller='UserOptionCategoryList'}pageNo={@$pageNo}&sortField=showOrder&sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.showOrder{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=category}
					<tr class="jsCategoryRow jsObjectActionObject" data-object-id="{@$category->getObjectID()}">
						<td class="columnIcon">
							<a href="{link controller='UserOptionCategoryEdit' id=$category->categoryID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{if $category->userOptions > 0}
								<span class="icon icon16 fa-times disabled"></span>
							{else}
								{objectAction action="delete" objectTitle=$category->getTitle()}
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$category->categoryID}</td>
						<td class="columnTitle columnCategoryName"><a href="{link controller='UserOptionCategoryEdit' id=$category->categoryID}{/link}">{lang}wcf.user.option.category.{$category->categoryName}{/lang}</a></td>
						<td class="columnDigits columnOptions">{#$category->userOptions}</td>
						<td class="columnDigits columnShowOrder">{#$category->showOrder}</td>
						
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
				<li><a href="{link controller='UserOptionCategoryAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.user.option.category.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
