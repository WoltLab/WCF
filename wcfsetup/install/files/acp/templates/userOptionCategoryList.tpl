{include file='header' pageTitle='wcf.acp.user.option.category.list'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.option.category.list{/lang}</h1>
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.Action.Delete('wcf\\data\\user\\option\\category\\UserOptionCategoryAction', '.jsCategoryRow');
		});
		//]]>
	</script>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="UserOptionCategoryList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='UserOptionCategoryAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.user.option.category.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.user.option.category.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnCategoryID{if $sortField == 'categoryID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='UserOptionCategoryList'}pageNo={@$pageNo}&sortField=categoryID&sortOrder={if $sortField == 'categoryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnCategoryName{if $sortField == 'categoryName'} active {@$sortOrder}{/if}"><a href="{link controller='UserOptionCategoryList'}pageNo={@$pageNo}&sortField=categoryName&sortOrder={if $sortField == 'categoryName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnDigits columnOptions{if $sortField == 'options'} active {@$sortOrder}{/if}"><a href="{link controller='UserOptionCategoryList'}pageNo={@$pageNo}&sortField=options&sortOrder={if $sortField == 'options' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.option.category.options{/lang}</a></th>
					<th class="columnDigits columnShowOrder{if $sortField == 'showOrder'} active {@$sortOrder}{/if}"><a href="{link controller='UserOptionCategoryList'}pageNo={@$pageNo}&sortField=showOrder&sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.option.category.showOrder{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=category}
					<tr class="jsCategoryRow">
						<td class="columnIcon">
							<a href="{link controller='UserOptionCategoryEdit' id=$category->categoryID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 icon-pencil"></span></a>
							<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$category->categoryID}" data-confirm-message="{lang}wcf.acp.user.option.category.delete.sure{/lang}"></span>
							
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
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				<li><a href="{link controller='UserOptionCategoryAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.user.option.category.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
