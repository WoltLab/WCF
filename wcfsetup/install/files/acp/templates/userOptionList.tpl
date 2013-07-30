{include file='header' pageTitle='wcf.acp.user.option.list'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.option.list{/lang}</h1>
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.Action.Delete('wcf\\data\\user\\option\\UserOptionAction', '.jsOptionRow');
			new WCF.Action.Toggle('wcf\\data\\user\\option\\UserOptionAction', $('.jsOptionRow'));
		});
		//]]>
	</script>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="UserOptionList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='UserOptionAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.user.option.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.user.option.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnOptionID{if $sortField == 'optionID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='UserOptionList'}pageNo={@$pageNo}&sortField=optionID&sortOrder={if $sortField == 'optionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnOptionName{if $sortField == 'optionName'} active {@$sortOrder}{/if}"><a href="{link controller='UserOptionList'}pageNo={@$pageNo}&sortField=optionName&sortOrder={if $sortField == 'optionName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnText columnCategoryName{if $sortField == 'categoryName'} active {@$sortOrder}{/if}"><a href="{link controller='UserOptionList'}pageNo={@$pageNo}&sortField=categoryName&sortOrder={if $sortField == 'categoryName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.option.categoryName{/lang}</a></th>
					<th class="columnText columnOptionType{if $sortField == 'optionType'} active {@$sortOrder}{/if}"><a href="{link controller='UserOptionList'}pageNo={@$pageNo}&sortField=optionType&sortOrder={if $sortField == 'optionType' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.option.optionType{/lang}</a></th>
					<th class="columnDigits columnShowOrder{if $sortField == 'showOrder'} active {@$sortOrder}{/if}"><a href="{link controller='UserOptionList'}pageNo={@$pageNo}&sortField=showOrder&sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.option.showOrder{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=option}
					<tr class="jsOptionRow">
						<td class="columnIcon">
							<span class="icon icon16 icon-check{if $option->isDisabled}-empty{/if} jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $option->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$option->optionID}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}"></span>
							<a href="{link controller='UserOptionEdit' id=$option->optionID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 icon-pencil"></span></a>
							<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$option->optionID}" data-confirm-message="{lang}wcf.acp.user.option.delete.sure{/lang}"></span>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$option->optionID}</td>
						<td class="columnTitle columnOptionName"><a href="{link controller='UserOptionEdit' id=$option->optionID}{/link}">{lang}wcf.user.option.{$option->optionName}{/lang}</a></td>
						<td class="columnText columnCategoryName">{lang}wcf.user.option.category.{$option->categoryName}{/lang}</td>
						<td class="columnText columnOptionType">{$option->optionType}</td>
						<td class="columnDigits columnShowOrder">{#$option->showOrder}</td>
						
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
				<li><a href="{link controller='UserOptionAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.user.option.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
