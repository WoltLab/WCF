{include file='header' pageTitle='wcf.acp.template.group.list'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.template.group.list{/lang}</h1>
	</hgroup>
	
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			new WCF.Action.Delete('wcf\\data\\template\\group\\TemplateGroupAction', '.jsTemplateGroupRow');
		});
		//]]>
	</script>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="TemplateGroupList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='TemplateGroupAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.template.group.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<hgroup>
			<h1>{lang}wcf.acp.template.group.list{/lang} <span class="badge badgeInverse jsDataCount">{#$items}</span></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnTemplateGroupID{if $sortField == 'templateGroupID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='TemplateGroupList'}pageNo={@$pageNo}&sortField=templateGroupID&sortOrder={if $sortField == 'templateGroupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnTemplateGroupName{if $sortField == 'templateGroupName'} active {@$sortOrder}{/if}"><a href="{link controller='TemplateGroupList'}pageNo={@$pageNo}&sortField=templateGroupName&sortOrder={if $sortField == 'templateGroupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnText columnTemplateGroupFolderName{if $sortField == 'templateGroupFolderName'} active {@$sortOrder}{/if}"><a href="{link controller='TemplateGroupList'}pageNo={@$pageNo}&sortField=templateGroupFolderName&sortOrder={if $sortField == 'templateGroupFolderName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.template.group.folderName{/lang}</a></th>
					<th class="columnDigits columnTemplates{if $sortField == 'templates'} active {@$sortOrder}{/if}"><a href="{link controller='TemplateGroupList'}pageNo={@$pageNo}&sortField=templates&sortOrder={if $sortField == 'templates' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.template.group.templates{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=templateGroup}
					<tr class="jsTemplateGroupRow">
						<td class="columnIcon">
							<a href="{link controller='TemplateGroupEdit' id=$templateGroup->templateGroupID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 icon-pencil"></span></a>
							<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$templateGroup->templateGroupID}" data-confirm-message="{lang}wcf.acp.template.group.delete.sure{/lang}"></span>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$templateGroup->templateGroupID}</td>
						<td class="columnTitle columnTemplateGroupName"><a href="{link controller='TemplateGroupEdit' id=$templateGroup->templateGroupID}{/link}">{$templateGroup->templateGroupName}</a></td>
						<td class="columnText columnTemplateGroupFolderName">{$templateGroup->templateGroupFolderName}</td>
						<td class="columnDigits columnTemplates">{#$templateGroup->templates}</td>
						
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
				<li><a href="{link controller='TemplateGroupAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.template.group.add{/lang}</span></a></li>
			
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<p class="info">{lang}wcf.acp.template.group.noItems{/lang}</p>
{/if}

{include file='footer'}
