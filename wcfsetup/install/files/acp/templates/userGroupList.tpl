{include file='header' pageTitle='wcf.acp.group.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\user\\group\\UserGroupAction', '.jsUserGroupRow');
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.group.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="UserGroupList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $__wcf->getSession()->getPermission('admin.user.canAddGroup')}
						<li><a href="{link controller='UserGroupAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
					{/if}
					
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<div class="tabularBox tabularBoxTitle marginTop">
	<header>
		<h2>{lang}wcf.acp.group.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
	</header>
	
	<table class="table">
		<thead>
			<tr>
				<th class="columnID columnGroupID{if $sortField == 'groupID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=groupID&sortOrder={if $sortField == 'groupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
				<th class="columnTitle columnGroupName{if $sortField == 'groupName'} active {@$sortOrder}{/if}"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=groupName&sortOrder={if $sortField == 'groupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.name{/lang}</a></th>
				<th class="columnDigits columnMembers{if $sortField == 'members'} active {@$sortOrder}{/if}"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=members&sortOrder={if $sortField == 'members' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.group.members{/lang}</a></th>
				<th class="columnDigits columnPriority{if $sortField == 'priority'} active {@$sortOrder}{/if}"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=priority&sortOrder={if $sortField == 'priority' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.group.priority{/lang}</a></th>
				
				{event name='columnHeads'}
			</tr>
		</thead>
		
		<tbody>
			{foreach from=$objects item=group}
				<tr id="groupContainer{@$group->groupID}" class="jsUserGroupRow">
					<td class="columnIcon">
						{if $group->isEditable()}
							<a href="{link controller='UserGroupEdit' id=$group->groupID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 icon-pencil"></span></a>
						{else}
							<span class="icon icon16 icon-pencil disabled" title="{lang}wcf.global.button.edit{/lang}"></span>
						{/if}
						{if $group->isDeletable()}
							<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$group->groupID}" data-confirm-message="{lang}wcf.acp.group.delete.sure{/lang}"></span>
						{else}
							<span class="icon icon16 icon-remove disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
						{/if}
						
						{event name='rowButtons'}
					</td>
					<td class="columnID columnGroupID">{@$group->groupID}</td>
					<td class="columnTitle columnGroupName">
						{if $group->isEditable()}
							<a title="{lang}wcf.acp.group.edit{/lang}" href="{link controller='UserGroupEdit' id=$group->groupID}{/link}">{lang}{$group->groupName}{/lang}</a>
						{else}
							{lang}{$group->groupName}{/lang}
						{/if}
					</td>
					<td class="columnDigits columnMembers">
						{if $group->groupType == 1 ||$group->groupType == 2}
							{* dont't show search links for the everybody and the guest user group *}
							{#$group->members}
						{else}
							<a class="jsTooltip" title="{lang}wcf.acp.group.showMembers{/lang}" href="{link controller='UserSearch'}groupID={@$group->groupID}{/link}">{#$group->members}</a>
						{/if}
					</td>
					<td class="columnDigits columnPriority">{#$group->priority}</td>
					
					{event name='columns'}
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>

<div class="contentNavigation">
	{@$pagesLinks}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $__wcf->getSession()->getPermission('admin.user.canAddGroup')}
						<li><a href="{link controller='UserGroupAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
					{/if}
					
					{event name='contentNavigationButtonsBottom'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{include file='footer'}
