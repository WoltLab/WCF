{include file='header' pageTitle='wcf.acp.bbcode.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.bbcode.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='BBCodeAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.bbcode.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="BBCodeList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\bbcode\BBCodeAction">
			<thead>
				<tr>
					<th class="columnID columnBBCodeID{if $sortField == 'bbcodeID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='BBCodeList'}pageNo={@$pageNo}&sortField=bbcodeID&sortOrder={if $sortField == 'bbcodeID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnBBCode{if $sortField == 'bbcodeTag'} active {@$sortOrder}{/if}"><a href="{link controller='BBCodeList'}pageNo={@$pageNo}&sortField=bbcodeTag&sortOrder={if $sortField == 'bbcodeTag' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.bbcode.bbcodeTag{/lang}</a></th>
					<th class="columnText columnClassName{if $sortField == 'className'} active {@$sortOrder}{/if}"><a href="{link controller='BBCodeList'}pageNo={@$pageNo}&sortField=className&sortOrder={if $sortField == 'className' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.bbcode.className{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=bbcode}
					<tr class="jsBBCodeRow jsObjectActionObject" data-object-id="{@$bbcode->getObjectID()}">
						<td class="columnIcon">
							<a href="{link controller='BBCodeEdit' object=$bbcode}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{if $bbcode->canDelete()}
								{objectAction action="delete" objectTitle=$bbcode->getTitle()}
							{else}
								<span class="icon icon16 fa-times disabled"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$bbcode->bbcodeID}</td>
						<td class="columnTitle columnBBCode"><a href="{link controller='BBCodeEdit' object=$bbcode}{/link}">[{$bbcode->bbcodeTag}]</a></td>
						<td class="columnText columnClassName">{$bbcode->className}</td>
						
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
				<li><a href="{link controller='BBCodeAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.bbcode.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
