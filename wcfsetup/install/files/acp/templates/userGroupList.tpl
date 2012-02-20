{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\user\\group\\UserGroupAction', $('.jsUserGroupRow'));
	});
	//]]>
</script>

<header class="wcf-container wcf-mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/users1.svg" alt="" class="wcf-containerIcon" />
	<hgroup class="wcf-containerContent">
		<h1>{lang}wcf.acp.group.list{/lang}</h1>
	</hgroup>
</header>

<div class="wcf-contentHeader">
	{pages print=true assign=pagesLinks controller="UserGroupList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	<nav>
		<ul class="wcf-largeButtons">
			<li><a href="{link controller='UserGroupAdd'}{/link}" title="{lang}wcf.acp.group.add{/lang}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

{hascontent}
	<div class="wcf-border wcf-boxTitle">
		<hgroup>
			<h1>{lang}wcf.acp.group.list{/lang} <span class="wcf-badge" title="{lang}wcf.acp.group.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table class="wcf-table big">
			<thead>
				<tr>
					<th class="columnID columnGroupID{if $sortField == 'groupID'} active{/if}" colspan="2"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=groupID&sortOrder={if $sortField == 'groupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}{if $sortField == 'groupID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnGroupName{if $sortField == 'groupName'} active{/if}"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=groupName&sortOrder={if $sortField == 'groupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.group.groupName{/lang}{if $sortField == 'groupName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDigits columnMembers{if $sortField == 'members'} active{/if}"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=members&sortOrder={if $sortField == 'members' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.group.members{/lang}{if $sortField == 'members'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{event name='headColumns'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=group}
						<tr id="groupContainer{@$group->groupID}" class="jsUserGroupRow">
							<td class="columnIcon">
								{if $group->isEditable()}
									<a href="{link controller='UserGroupEdit' id=$group->groupID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/edit1.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip" /></a>
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/edit1D.svg" alt="" title="{lang}wcf.acp.group.edit{/lang}" />
								{/if}
								{if $group->isDeletable()}
									<img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="jsDeleteButton jsTooltip" data-object-id="{@$group->groupID}" data-confirm-message="{lang}wcf.acp.group.delete.sure{/lang}" />
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/delete1D.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" />
								{/if}
								
								{event name='buttons'}
							</td>
							<td class="columnID columnGroupID"><p>{@$group->groupID}</p></td>
							<td class="columnTitle columnGroupName">{if $group->isEditable()}<p><a title="{lang}wcf.acp.group.edit{/lang}" href="{link controller='UserGroupEdit' id=$group->groupID}{/link}">{lang}{$group->groupName}{/lang}</a>{else}{lang}{$group->groupName}{/lang}</p>{/if}</td>
							<td class="columnDigits columnMembers"><p><a title="{lang}wcf.acp.group.showMembers{/lang}" href="{link controller='UserSearch'}groupID={@$group->groupID}{/link}">{#$group->members}</p></a></td>
						
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
		
	</div>
	
	<div class="wcf-contentFooter">
		{@$pagesLinks}
		<nav>
			<ul class="wcf-largeButtons">
				<li><a href="{link controller='UserGroupAdd'}{/link}" title="{lang}wcf.acp.group.add{/lang}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
				
				{event name='largeButtons'}
			</ul>
		</nav>
	</div>
{/hascontent}

{include file='footer'}
