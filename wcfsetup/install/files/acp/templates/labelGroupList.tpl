{include file='header' pageTitle='wcf.acp.label.group.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.label.group.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='LabelGroupAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.label.group.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<form action="{link controller='LabelGroupList'}{/link}" method="post">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.label.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="groupName" name="groupName" value="{$groupName}" placeholder="{lang}wcf.global.title{/lang}" class="long">
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="groupDescription" name="groupDescription" value="{$groupDescription}" placeholder="{lang}wcf.global.description{/lang}"  class="long">
				</dd>
			</dl>
		</div>
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{hascontent}
	<div class="paginationTop">
		{content}
			{assign var='linkParameters' value=''}
			{if $groupName}
				{append var='linkParameters' value='&groupName='}
				{append var='linkParameters' value=$groupName|rawurlencode}
			{/if}
			{if $groupDescription}
				{append var='linkParameters' value='&groupDescription='}
				{append var='linkParameters' value=$groupDescription|rawurlencode}
			{/if}
			
			{pages print=true assign=pagesLinks controller="LabelGroupList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div id="labelGroupTableContainer" class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\label\group\LabelGroupAction">
			<thead>
				<tr>
					<th class="columnID columnLabelGroupID{if $sortField == 'groupID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='LabelGroupList'}pageNo={@$pageNo}&sortField=groupID&sortOrder={if $sortField == 'groupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnGroupName{if $sortField == 'groupName'} active {@$sortOrder}{/if}"><a href="{link controller='LabelGroupList'}pageNo={@$pageNo}&sortField=groupName&sortOrder={if $sortField == 'groupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.title{/lang}</a></th>
					<th class="columnText columnGroupDescription{if $sortField == 'groupDescription'} active {@$sortOrder}{/if}"><a href="{link controller='LabelGroupList'}pageNo={@$pageNo}&sortField=groupDescription&sortOrder={if $sortField == 'groupDescription' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.description{/lang}</a></th>
					<th class="columnDigits columnLabels{if $sortField == 'labels'} active {@$sortOrder}{/if}"><a href="{link controller='LabelGroupList'}pageNo={@$pageNo}&sortField=labels&sortOrder={if $sortField == 'labels' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.label.list{/lang}</a></th>
					<th class="columnDigits columnShowOrder{if $sortField == 'showOrder'} active {@$sortOrder}{/if}"><a href="{link controller='LabelGroupList'}pageNo={@$pageNo}&sortField=showOrder&sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.showOrder{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=group}
					<tr class="jsLabelGroupRow jsObjectActionObject" data-object-id="{@$group->getObjectID()}">
						<td class="columnIcon">
							<a href="{link controller='LabelGroupEdit' object=$group}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip">{icon name='pencil'}</a>
							{objectAction action="delete" objectTitle=$group->getTitle()}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$group->groupID}</td>
						<td class="columnTitle columnGroupName"><a href="{link controller='LabelGroupEdit' object=$group}{/link}">{$group}</a></td>
						<td class="columnText columnGroupDescription">{$group->groupDescription}</td>
						<td class="columnDigits columnLabels"><a href="{link controller='LabelList' id=$group->groupID}{/link}">{#$group->labels}</a></td>
						<td class="columnDigits columnShowOrder">{@$group->showOrder}</td>
						
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
				<li><a href="{link controller='LabelGroupAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.label.group.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}
