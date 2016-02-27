{include file='header' pageTitle='wcf.acp.group.assignment.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\user\\group\\assignment\\UserGroupAssignmentAction', '.jsUserGroupAssignmentRow');
		new WCF.Action.Toggle('wcf\\data\\user\\group\\assignment\\UserGroupAssignmentAction', '.jsUserGroupAssignmentRow');
		
		var options = { };
		{if $pages > 1}
			options.refreshPage = true;
			{if $pages == $pageNo}
				options.updatePageNumber = -1;
			{/if}
		{else}
			options.emptyMessage = '{lang}wcf.global.noItems{/lang}';
		{/if}
		
		new WCF.Table.EmptyTableHandler($('#userGroupAssignmentTableContainer'), 'jsUserGroupAssignmentRow', options);
	});
	//]]>
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.group.assignment.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="UserGroupAssignmentList" link="pageNo=%d"}
	
	<nav>
		<ul>
			<li><a href="{link controller='UserGroupAssignmentAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.group.assignment.button.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

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
			
			<tbody>
				{foreach from=$objects item='assignment'}
					<tr class="jsUserGroupAssignmentRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-{if !$assignment->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $assignment->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$assignment->assignmentID}"></span>
							<a href="{link controller='UserGroupAssignmentEdit' object=$assignment}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$assignment->assignmentID}" data-confirm-message="{lang}wcf.acp.group.assignment.delete.confirmMessage{/lang}"></span>
							
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
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				<li><a href="{link controller='UserGroupAssignmentAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.group.assignment.button.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
