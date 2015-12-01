{include file='header' pageTitle='wcf.acp.page.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\page\\PageAction', '.jsPageRow');
		new WCF.Action.Toggle('wcf\\data\\page\\PageAction', '.jsPageRow');
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.page.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="PageList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='PageAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.page.add{/lang}</span></a></li>
			<li><a href="{link controller='PageAdd'}isMultilingual=1{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.page.addMultilingual{/lang}</span></a></li>
		
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.page.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnPageID{if $sortField == 'pageID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='PageList'}pageNo={@$pageNo}&sortField=pageID&sortOrder={if $sortField == 'pageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnName{if $sortField == 'name'} active {@$sortOrder}{/if}"><a href="{link controller='PageList'}pageNo={@$pageNo}&sortField=name&sortOrder={if $sortField == 'name' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnText columnURL">{lang}wcf.acp.page.url{/lang}</th>
					<th class="columnDate columnLastUpdateTime{if $sortField == 'lastUpdateTime'} active {@$sortOrder}{/if}"><a href="{link controller='PageList'}pageNo={@$pageNo}&sortField=lastUpdateTime&sortOrder={if $sortField == 'lastUpdateTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.page.lastUpdateTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=page}
					<tr class="jsPageRow">
						<td class="columnIcon">
							{if $page->canDisable()}
								<span class="icon icon16 fa-{if !$page->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$page->isDisabled}disable{else}enable{/if}{/lang}" data-object-id="{@$page->pageID}"></span>
							{else}
								<span class="icon icon16 fa-{if !$page->isDisabled}check-{/if}square-o disabled" title="{lang}wcf.global.button.{if !$page->isDisabled}disable{else}enable{/if}{/lang}"></span>
							{/if}
							<a href="{link controller='PageEdit' id=$page->pageID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{if $page->canDelete()}
								<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$page->pageID}" data-confirm-message="{lang}wcf.acp.page.delete.confirmMessage{/lang}"></span>
							{else}
								<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnPageID">{@$page->pageID}</td>
						<td class="columnTitle columnName"><a href="{link controller='PageEdit' id=$page->pageID}{/link}">{$page->name}</a></td>
						<td class="columnText columnURL">{$page->getURL()}</td>
						<td class="columnDate columnLastUpdateTime">{@$page->lastUpdateTime|time}</td>
						
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
				<li><a href="{link controller='PageAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.page.add{/lang}</span></a></li>
				<li><a href="{link controller='PageAdd'}isMultilingual=1{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.page.addMultilingual{/lang}</span></a></li>
		
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{/if}

{include file='footer'}
