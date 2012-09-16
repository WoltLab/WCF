{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\user\\group\\UserGroupAction', $('.jsUserGroupRow'));
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.group.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="UserGroupList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	<nav>
		<ul>
			<li><a href="{link controller='UserGroupAdd'}{/link}" title="{lang}wcf.acp.group.add{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

{hascontent}
	<div class="tabularBox tabularBoxTitle marginTop shadow">
		<hgroup>
			<h1>{lang}wcf.acp.group.list{/lang} <span class="badge badgeInverse" title="{lang}wcf.acp.group.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnGroupID{if $sortField == 'groupID'} active{/if}" colspan="2"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=groupID&sortOrder={if $sortField == 'groupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}{if $sortField == 'groupID'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnGroupName{if $sortField == 'groupName'} active{/if}"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=groupName&sortOrder={if $sortField == 'groupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.group.groupName{/lang}{if $sortField == 'groupName'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDigits columnMembers{if $sortField == 'members'} active{/if}"><a href="{link controller='UserGroupList'}pageNo={@$pageNo}&sortField=members&sortOrder={if $sortField == 'members' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.group.members{/lang}{if $sortField == 'members'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{event name='headColumns'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=group}
						<tr id="groupContainer{@$group->groupID}" class="jsUserGroupRow">
							<td class="columnIcon">
								{if $group->isEditable()}
									<a href="{link controller='UserGroupEdit' id=$group->groupID}{/link}"><img src="{@$__wcf->getPath()}icon/edit.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="icon16 jsTooltip" /></a>
								{else}
									<img src="{@$__wcf->getPath()}icon/edit.svg" alt="" title="{lang}wcf.acp.group.edit{/lang}" class="icon16 disabled" />
								{/if}
								{if $group->isDeletable()}
									<img src="{@$__wcf->getPath()}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 jsDeleteButton jsTooltip" data-object-id="{@$group->groupID}" data-confirm-message="{lang}wcf.acp.group.delete.sure{/lang}" />
								{else}
									<img src="{@$__wcf->getPath()}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 disabled" />
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
	
	<div class="contentNavigation">
		{@$pagesLinks}
		<nav>
			<ul>
				<li><a href="{link controller='UserGroupAdd'}{/link}" title="{lang}wcf.acp.group.add{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
				
				{event name='largeButtons'}
			</ul>
		</nav>
	</div>
{/hascontent}

{include file='footer'}
