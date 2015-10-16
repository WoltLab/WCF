{include file='header' pageTitle='wcf.acp.label.group.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\label\\group\\LabelGroupAction', '.jsLabelGroupRow');
		
		var options = { };
		{if $pages > 1}
			options.refreshPage = true;
			{if $pages == $pageNo}
				options.updatePageNumber = -1;
			{/if}
		{else}
			options.emptyMessage = '{lang}wcf.global.noItems{/lang}';
		{/if}
		
		new WCF.Table.EmptyTableHandler($('#labelGroupTableContainer'), 'jsLabelGroupRow', options);
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.label.group.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="LabelGroupList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='LabelGroupAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.label.group.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div id="labelGroupTableContainer" class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.label.group.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnLabelGroupID{if $sortField == 'groupID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='LabelGroupList'}pageNo={@$pageNo}&sortField=groupID&sortOrder={if $sortField == 'groupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnGroupName{if $sortField == 'groupName'} active {@$sortOrder}{/if}"><a href="{link controller='LabelGroupList'}pageNo={@$pageNo}&sortField=groupName&sortOrder={if $sortField == 'groupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.title{/lang}</a></th>
					<th class="columnText columnGroupDescription{if $sortField == 'groupDescription'} active {@$sortOrder}{/if}"><a href="{link controller='LabelGroupList'}pageNo={@$pageNo}&sortField=groupDescription&sortOrder={if $sortField == 'groupDescription' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.description{/lang}</a></th>
					<th class="columnDigits columnShowOrder{if $sortField == 'showOrder'} active {@$sortOrder}{/if}"><a href="{link controller='LabelGroupList'}pageNo={@$pageNo}&sortField=showOrder&sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.label.group.showOrder{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=group}
					<tr class="jsLabelGroupRow">
						<td class="columnIcon">
							<a href="{link controller='LabelGroupEdit' object=$group}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$group->groupID}" data-confirm-message="{lang}wcf.acp.label.group.delete.sure{/lang}"></span>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$group->groupID}</td>
						<td class="columnTitle columnGroupName"><a href="{link controller='LabelGroupEdit' object=$group}{/link}">{$group}</a></td>
						<td class="columnText columnGroupDescription">{$group->groupDescription}</td>
						<td class="columnDigits columnShowOrder">{@$group->showOrder}</td>
						
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
				<li><a href="{link controller='LabelGroupAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.label.group.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
