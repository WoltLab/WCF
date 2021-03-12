{include file='header' pageTitle='wcf.acp.group.assignment.list'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\user\\group\\assignment\\UserGroupAssignmentAction', '.jsUserGroupAssignmentRow');
		new WCF.Action.Toggle('wcf\\data\\user\\group\\assignment\\UserGroupAssignmentAction', '.jsUserGroupAssignmentRow');
	});
</script>

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
		<table class="table">
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
					<tr class="jsUserGroupAssignmentRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-{if !$assignment->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $assignment->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$assignment->assignmentID}"></span>
							<a href="{link controller='UserGroupAssignmentEdit' object=$assignment}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$assignment->assignmentID}" data-confirm-message-html="{lang __encode=true}wcf.acp.group.assignment.delete.confirmMessage{/lang}"></span>
							
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
