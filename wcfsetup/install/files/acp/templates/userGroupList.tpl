{include file='header' pageTitle='wcf.acp.group.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.group.list{/lang} <span class="badge badgeInverse">{#$items}</span></h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $__wcf->getSession()->getPermission('admin.user.canAddGroup')}
						<li><a href="{link controller='UserGroupAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
					{/if}
						
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="UserGroupList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

<div class="section tabularBox">
	<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\user\group\UserGroupAction">
		<thead>
			<tr>
				<th class="columnID columnGroupID{if $sortField == 'groupID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=groupID&sortOrder={if $sortField == 'groupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
				<th class="columnTitle columnGroupName{if $sortField == 'groupNameI18n'} active {@$sortOrder}{/if}"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=groupNameI18n&sortOrder={if $sortField == 'groupNameI18n' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.name{/lang}</a></th>
				<th class="columnDigits columnMembers{if $sortField == 'members'} active {@$sortOrder}{/if}"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=members&sortOrder={if $sortField == 'members' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.group.members{/lang}</a></th>
				<th class="columnDigits columnPriority{if $sortField == 'priority'} active {@$sortOrder}{/if}"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=priority&sortOrder={if $sortField == 'priority' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.group.priority{/lang}</a></th>
				
				{event name='columnHeads'}
			</tr>
		</thead>
		
		<tbody class="jsReloadPageWhenEmpty">
			{foreach from=$objects item=group}
				<tr id="groupContainer{@$group->groupID}" class="jsUserGroupRow jsObjectActionObject" data-object-id="{@$group->getObjectID()}">
					<td class="columnIcon">
						{if $group->isEditable()}
							<a href="{link controller='UserGroupEdit' id=$group->groupID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip">{icon name='pencil'}</a>
						{else}
							<span class="disabled" title="{lang}wcf.global.button.edit{/lang}">
								{icon name='pencil'}
							</span>
						{/if}
						{if $group->isDeletable()}
							{objectAction action="delete" objectTitle=$group->getTitle()}
						{else}
							<span class="disabled" title="{lang}wcf.global.button.delete{/lang}">
								{icon name='xmark'}
							</span>
						{/if}
						
						{event name='rowButtons'}
					</td>
					<td class="columnID columnGroupID">{@$group->groupID}</td>
					<td class="columnTitle columnGroupName">
						{if $group->isEditable()}
							<a title="{lang}wcf.acp.group.edit{/lang}" href="{link controller='UserGroupEdit' id=$group->groupID}{/link}">{$group->getTitle()}</a>
						{else}
							{$group->getTitle()}
						{/if}
						{if $group->isOwner()}
							<span class="jsTooltip" title="{lang}wcf.acp.group.type.owner{/lang}">
								{icon name='shield-halved'}
							</span>
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

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}
					{if $__wcf->getSession()->getPermission('admin.user.canAddGroup')}
						<li><a href="{link controller='UserGroupAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
					{/if}
					
					{event name='contentFooterNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
