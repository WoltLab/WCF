{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/RemoveObjectAction.class.js"></script>

<script type="text/javascript">
	//<![CDATA[
	var removeGroup = new RemoveObjectAction();
	removeGroup.setOptions({
		actionClass: 'UserGroup',
		containerPrefix: 'groupContainer',
		iconPrefix: 'groupDeleteIcon',
		langDeleteSure: '{lang}wcf.acp.group.delete.sure{/lang}',
		url: 'index.php?action=RemoveObject'
	});
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/userGroupL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.group.list{/lang}</h1>
	</hgroup>
</header>

{if $deletedGroups}
	<p class="success">{lang}wcf.acp.group.delete.success{/lang}</p>
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=UserGroupList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:SID_ARG_2ND_NOT_ENCODED}
	<nav class="largeButtons">
		<ul>
			<li><a href="index.php?form=UserGroupAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.group.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/userGroupAddM.png" alt="" /> <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}			
		</ul>
	</nav>
</div>

{if $groups|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.group.list.data{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnGroupID{if $sortField == 'groupID'} active{/if}" colspan="2"><p><a href="index.php?page=UserGroupList&amp;pageNo={@$pageNo}&amp;sortField=groupID&amp;sortOrder={if $sortField == 'groupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.group.groupID{/lang}{if $sortField == 'groupID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></p></th>
					<th class="columnGroupName{if $sortField == 'groupName'} active{/if}"><p><a href="index.php?page=UserGroupList&amp;pageNo={@$pageNo}&amp;sortField=groupName&amp;sortOrder={if $sortField == 'groupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.group.groupName{/lang}{if $sortField == 'groupName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></p></th>
					<th class="columnMembers{if $sortField == 'members'} active{/if}"><p><a href="index.php?page=UserGroupList&amp;pageNo={@$pageNo}&amp;sortField=members&amp;sortOrder={if $sortField == 'members' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.group.members{/lang}{if $sortField == 'members'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></p></th>
					
					{if $additionalHeadColumns|isset}{@$additionalHeadColumns}{/if}
				</tr>
			</thead>
			<tbody>
				{foreach from=$groups item=group}
					<tr id="groupContainer{@$group->groupID}">
						<td class="columnIcon">
							{if $group->isEditable()}
								<a href="index.php?form=UserGroupEdit&amp;groupID={@$group->groupID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.group.edit{/lang}" /></a>
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.group.edit{/lang}" />
							{/if}
							{if $group->isDeletable()}
								<img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.group.delete{/lang}" id="groupDeleteIcon{@$group->groupID}" />
								<script type="text/javascript">
									//<![CDATA[
									removeGroup.registerObject({@$group->groupID}, {
										objectID: {@$group->groupID}
									});
									//]]>
								</script>
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.group.delete{/lang}" />
							{/if}
							
							{if $additionalButtons[$group->groupID]|isset}{@$additionalButtons[$group->groupID]}{/if}
						</td>
						<td class="columnGroupID columnID"><p>{@$group->groupID}</p></td>
						<td class="columnGroupName columnText">{if $group->isEditable()}<p><a title="{lang}wcf.acp.group.edit{/lang}" href="index.php?form=UserGroupEdit&amp;groupID={@$group->groupID}{@SID_ARG_2ND}">{$group->groupName}</a>{else}{$group->groupName}</p>{/if}</td>
						<td class="columnMembers columnNumbers"><p><a title="{lang}wcf.acp.group.showMembers{/lang}" href="index.php?form=UserSearch&amp;groupID={@$group->groupID}{@SID_ARG_2ND}">{#$group->members}</p></a></td>
						
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
			<li><a href="index.php?form=UserGroupAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.group.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/userGroupAddM.png" alt="" /> <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

{include file='footer'}
