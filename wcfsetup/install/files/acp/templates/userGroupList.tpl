{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/users1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.group.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=UserGroupList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"|concat:SID_ARG_2ND_NOT_ENCODED}
	<nav class="largeButtons">
		<ul>
			<li><a href="index.php?form=UserGroupAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.group.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}			
		</ul>
	</nav>
</div>

{if $groups|count}
	<div class="border boxTitle">
		<hgroup>
			<h1>{lang}wcf.acp.group.list{/lang} <span class="badge" title="{lang}wcf.acp.group.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table class="bigList">
			<thead>
				<tr class="tableHead">
					<th class="columnID columnGroupID{if $sortField == 'groupID'} active{/if}" colspan="2"><a href="index.php?page=UserGroupList&amp;pageNo={@$pageNo}&amp;sortField=groupID&amp;sortOrder={if $sortField == 'groupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.global.objectID{/lang}{if $sortField == 'groupID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnGroupName{if $sortField == 'groupName'} active{/if}"><a href="index.php?page=UserGroupList&amp;pageNo={@$pageNo}&amp;sortField=groupName&amp;sortOrder={if $sortField == 'groupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.group.groupName{/lang}{if $sortField == 'groupName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDigits columnMembers{if $sortField == 'members'} active{/if}"><a href="index.php?page=UserGroupList&amp;pageNo={@$pageNo}&amp;sortField=members&amp;sortOrder={if $sortField == 'members' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.group.members{/lang}{if $sortField == 'members'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{if $additionalHeadColumns|isset}{@$additionalHeadColumns}{/if}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$groups item=group}
					<tr id="groupContainer{@$group->groupID}">
						<td class="columnIcon">
							{if $group->isEditable()}
								<a href="index.php?form=UserGroupEdit&amp;groupID={@$group->groupID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/edit1.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="balloonTooltip" /></a>
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/edit1D.svg" alt="" title="{lang}wcf.acp.group.edit{/lang}" />
							{/if}
							{if $group->isDeletable()}
								<img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" data-objectID="{@$group->groupID}" data-confirmMessage="{lang}wcf.acp.group.delete.sure{/lang}" title="{lang}wcf.global.button.delete{/lang}" class="deleteButton balloonTooltip" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/delete1D.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" />
							{/if}
							
							{if $additionalButtons[$group->groupID]|isset}{@$additionalButtons[$group->groupID]}{/if}
						</td>
						<td class="columnID columnGroupID"><p>{@$group->groupID}</p></td>
						<td class="columnTitle columnGroupName">{if $group->isEditable()}<p><a title="{lang}wcf.acp.group.edit{/lang}" href="index.php?form=UserGroupEdit&amp;groupID={@$group->groupID}{@SID_ARG_2ND}">{$group->groupName}</a>{else}{$group->groupName}</p>{/if}</td>
						<td class="columnDigits columnMembers"><p><a title="{lang}wcf.acp.group.showMembers{/lang}" href="index.php?form=UserSearch&amp;groupID={@$group->groupID}{@SID_ARG_2ND}">{#$group->members}</p></a></td>
						
						{if $additionalColumns[$group->groupID]|isset}{@$additionalColumns[$group->groupID]}{/if}
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</div>
{/if}

<div class="contentFooter">
	{@$pagesLinks}
	<nav class="largeButtons">
		<ul>
			<li><a href="index.php?form=UserGroupAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.group.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

{include file='footer'}
