{include file='header' pageTitle='wcf.acp.group.assignment.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.group.assignment.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='UserGroupAssignmentAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.group.assignment.button.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="UserGroupAssignmentList" link="pageNo=%d"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox" id="userGroupAssignmentTableContainer">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\user\group\assignment\UserGroupAssignmentAction">
			<thead>
				<tr>
					<th class="columnID columnAssignmentID" colspan="2"><span>{lang}wcf.global.objectID{/lang}</span></th>
					<th class="columnTitle columnAssignmentName"><span>{lang}wcf.global.name{/lang}</span></th>
					<th class="columnTitle columnGroupName"><span>{lang}wcf.acp.group.assignment.userGroup{/lang}</span></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item='assignment'}
					<tr class="jsUserGroupAssignmentRow jsObjectActionObject" data-object-id="{@$assignment->getObjectID()}">
						<td class="columnIcon">
							{objectAction action="toggle" isDisabled=$assignment->isDisabled}
							<a href="{link controller='UserGroupAssignmentEdit' object=$assignment}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{objectAction action="delete" objectTitle=$assignment->getTitle()}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnAssignmentID">{@$assignment->assignmentID}</td>
						<td class="columnTitle columnAssignmentName">
							<a href="{link controller='UserGroupAssignmentEdit' object=$assignment}{/link}">{$assignment->title}</a>
						</td>
						<td class="columnDigits columnGroupName">
							{$assignment->getUserGroup()->getName()}
						</td>
						
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
				<li><a href="{link controller='UserGroupAssignmentAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.group.assignment.button.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
