{include file='header' pageTitle='wcf.acp.label.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\label\\LabelAction', '.jsLabelRow');
		
		var options = { };
		{if $pages > 1}
			options.refreshPage = true;
			{if $pages == $pageNo}
				options.updatePageNumber = -1;
			{/if}
		{else}
			options.emptyMessage = '{lang}wcf.global.noItems{/lang}';
		{/if}
		
		new WCF.Table.EmptyTableHandler($('#labelTableContainer'), 'jsLabelRow', options);
		
		{if $labelGroup && !$labelSearch && !$cssClassName && $items > 1}
			new WCF.Sortable.List('labelTableContainer', 'wcf\\data\\label\\LabelAction', {@$startIndex}, {
				items: 'tr',
				toleranceElement: null
			}, true);
		{/if}
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.label.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='LabelAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.label.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $items || $labelSearch || $labelGroup || $cssClassName}
	<p class="info">{lang}wcf.acp.label.sortAfterGroupFiltering{/lang}</p>
	
	<form action="{link controller='LabelList'}{/link}" method="post">
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acp.label.filter{/lang}</h2>
			
			<div class="row rowColGap">
				<dl class="col-xs-12 col-md-4">
					<dt><label for="label">{lang}wcf.acp.label.label{/lang}</label></dt>
					<dd>
						<input type="text" id="label" name="label" value="{$labelSearch}" class="long" />
					</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-4">
					<dt><label for="groupID">{lang}wcf.acp.label.group{/lang}</label></dt>
					<dd>
						<select id="groupID" name="groupID">
							<option value="0">{lang}wcf.global.noSelection{/lang}</option>
							{foreach from=$labelGroupList item=group}
								<option value="{@$group->groupID}"{if $group->groupID == $groupID} selected="selected"{/if}>{$group}{if $group->groupDescription} / {$group->groupDescription}{/if}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-4">
					<dt><label for="cssClassName">{lang}wcf.acp.label.cssClassName{/lang}</label></dt>
					<dd>
						<input type="text" id="cssClassName" name="cssClassName" value="{$cssClassName}" class="long" />
					</dd>
				</dl>
			</div>
		</section>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</form>
{/if}

{hascontent}
	<div class="paginationTop">
		{content}
			{assign var='linkParameters' value=''}
			{if $labelSearch}
				{append var='linkParameters' value='&label='}
				{append var='linkParameters' value=$labelSearch|rawurlencode}
			{/if}
			{if $cssClassName}
				{append var='linkParameters' value='&cssClassName='}
				{append var='linkParameters' value=$cssClassName|rawurlencode}
			{/if}
			
			{pages print=true assign=pagesLinks controller="LabelList" object=$labelGroup link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div id="labelTableContainer" class="section tabularBox{if $labelGroup && !$labelSearch && !$cssClassName && $items > 1} sortableListContainer{/if}">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnLabelID{if $sortField == 'labelID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='LabelList' object=$labelGroup}pageNo={@$pageNo}&sortField=labelID&sortOrder={if $sortField == 'labelID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnLabel{if $sortField == 'label'} active {@$sortOrder}{/if}"><a href="{link controller='LabelList' object=$labelGroup}pageNo={@$pageNo}&sortField=label&sortOrder={if $sortField == 'label' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.label.label{/lang}</a></th>
					<th class="columnText columnGroup{if $sortField == 'groupName'} active {@$sortOrder}{/if}"><a href="{link controller='LabelList' object=$labelGroup}pageNo={@$pageNo}&sortField=groupName&sortOrder={if $sortField == 'groupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.label.group{/lang}</a></th>
					{if $labelGroup && !$labelSearch && !$cssClassName && $items > 1}
						<th class="columnDigits columnShowOrder{if $sortField == 'showOrder'} active {@$sortOrder}{/if}"><a href="{link controller='LabelList' object=$labelGroup}pageNo={@$pageNo}&sortField=showOrder&sortOrder={if $sortField == 'groupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.label.showOrder{/lang}</a></th>
					{/if}
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody{if $labelGroup && !$labelSearch && !$cssClassName && $items > 1} class="sortableList" data-object-id="{@$labelGroup->groupID}"{/if}>
				{foreach from=$objects item=label}
					<tr class="jsLabelRow{if $labelGroup && !$labelSearch && !$cssClassName && $items > 1} sortableNode" data-object-id="{@$label->labelID}{/if}">
						<td class="columnIcon">
							<a href="{link controller='LabelEdit' object=$label}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$label->labelID}" data-confirm-message="{lang}wcf.acp.label.delete.sure{/lang}"></span>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$label->labelID}</td>
						<td class="columnTitle columnLabel"><a href="{link controller='LabelEdit' object=$label}{/link}" title="{$label}" class="badge label{if $label->getClassNames()} {$label->getClassNames()}{/if}">{$label}</a></td>
						<td class="columnText columnGroup">{lang}{$label->groupName}{/lang}{if $label->groupDescription} / {$label->groupDescription}{/if}</td>
						{if $labelGroup && !$labelSearch && !$cssClassName && $items > 1}
							<td class="columnDigits columnShowOrder">{#$label->showOrder}</td>
						{/if}
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	{if $labelGroup && !$labelSearch && !$cssClassName && $items > 1}
		<div class="formSubmit">
			<button data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
		</div>
	{/if}
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="{link controller='LabelAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.label.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
