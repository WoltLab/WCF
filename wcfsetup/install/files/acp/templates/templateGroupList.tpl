{include file='header' pageTitle='wcf.acp.template.group.list'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\template\\group\\TemplateGroupAction', '.jsTemplateGroupRow');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.template.group.list{/lang} <span class="badge badgeInverse">{#$items}</span></h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='TemplateGroupAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.template.group.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="TemplateGroupList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div id="templateGroupTableContainer" class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnTemplateGroupID{if $sortField == 'templateGroupID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='TemplateGroupList'}pageNo={@$pageNo}&sortField=templateGroupID&sortOrder={if $sortField == 'templateGroupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnTemplateGroupName{if $sortField == 'templateGroupName'} active {@$sortOrder}{/if}"><a href="{link controller='TemplateGroupList'}pageNo={@$pageNo}&sortField=templateGroupName&sortOrder={if $sortField == 'templateGroupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnText columnTemplateGroupFolderName{if $sortField == 'templateGroupFolderName'} active {@$sortOrder}{/if}"><a href="{link controller='TemplateGroupList'}pageNo={@$pageNo}&sortField=templateGroupFolderName&sortOrder={if $sortField == 'templateGroupFolderName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.template.group.folderName{/lang}</a></th>
					<th class="columnDigits columnTemplates{if $sortField == 'templates'} active {@$sortOrder}{/if}"><a href="{link controller='TemplateGroupList'}pageNo={@$pageNo}&sortField=templates&sortOrder={if $sortField == 'templates' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.template.group.templates{/lang}</a></th>
					<th class="columnDigits columnStyles{if $sortField == 'styles'} active {@$sortOrder}{/if}"><a href="{link controller='TemplateGroupList'}pageNo={@$pageNo}&sortField=styles&sortOrder={if $sortField == 'styles' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.template.group.styles{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=templateGroup}
					<tr class="jsTemplateGroupRow">
						<td class="columnIcon">
							{if $templateGroup->isImmutable()}
								<span class="icon icon16 fa-pencil disabled" title="{lang}wcf.global.button.edit{/lang}"></span>
							{else}
								<a href="{link controller='TemplateGroupEdit' id=$templateGroup->templateGroupID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{/if}
							
							<a href="{link controller='TemplateList' templateGroupID=$templateGroup->templateGroupID}{/link}" title="{lang}wcf.acp.template.list{/lang}" class="jsTooltip"><span class="icon icon16 fa-list"></span></a>
							
							{if $templateGroup->isImmutable()}
								<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{else}
								<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$templateGroup->templateGroupID}" data-confirm-message-html="{lang __encode=true}wcf.acp.template.group.delete.sure{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$templateGroup->templateGroupID}</td>
						<td class="columnTitle columnTemplateGroupName">
							{if !$templateGroup->isImmutable()}
								<a href="{link controller='TemplateGroupEdit' id=$templateGroup->templateGroupID}{/link}">
									{$templateGroup->getName()}
								</a>
							{else}
								{$templateGroup->getName()}
							{/if}
						</td>
						<td class="columnText columnTemplateGroupFolderName">{$templateGroup->templateGroupFolderName}</td>
						<td class="columnDigits columnTemplates">{#$templateGroup->templates}</td>
						<td class="columnDigits columnStyles">{#$templateGroup->styles}</td>
						
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
				<li><a href="{link controller='TemplateGroupAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.template.group.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
