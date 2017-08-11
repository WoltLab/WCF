{include file='header' pageTitle='wcf.acp.menu.link.trophy.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\trophy\\TrophyAction', '.trophyRow');
		new WCF.Action.Toggle('wcf\\data\\trophy\\TrophyAction', '.trophyRow');
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.trophy.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='TrophyAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.trophy.add{/lang}</span></a></li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>
{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign=pagesLinks controller='TrophyList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">

		<table class="table">
			<thead>
			<tr>
				<th class="columnID columnTrophyID{if $sortField == 'trophyID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='TrophyList'}pageNo={@$pageNo}&sortField=trophyID&sortOrder={if $sortField == 'trophyID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
				<th class="columnTitle{if $sortField == 'title'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='TrophyList'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.title{/lang}</a></th>
				<th class="columnText columnCategory{if $sortField == 'categoryID'} active {@$sortOrder}{/if}"><a href="{link controller='TrophyList'}pageNo={@$pageNo}&sortField=categoryID&sortOrder={if $sortField == 'categoryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.trophy.category{/lang}</a></th>

				{event name='columnHeads'}
			</tr>
			</thead>

			<tbody>
			{foreach from=$objects item=trophy}
				<tr class="trophyRow">
					<td class="columnIcon">
						<span class="icon icon16 fa-{if !$trophy->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$trophy->isDisabled}disable{else}enable{/if}{/lang}" data-object-id="{@$trophy->getObjectID()}"></span>
						<a href="{link controller='TrophyEdit' id=$trophy->getObjectID()}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
						<span class="icon icon16 fa-times pointer jsDeleteButton jsTooltip" data-confirm-message-html="{lang __encode=true}wcf.acp.trophy.delete.confirmMessage{/lang}" data-object-id="{@$trophy->getObjectID()}" title="{lang}wcf.global.button.delete{/lang}"></span>
					</td>
					<td class="columnID columnTrophyID">{@$trophy->trophyID}</td>
					<td class="columnIcon">{@$trophy->renderTrophy(32)}</td>
					<td class="columnTitle columnTrophyTitle"><a href="{link controller='TrophyEdit' id=$trophy->getObjectID()}{/link}" title="{lang}wcf.global.button.edit{/lang}">{$trophy->getTitle()}</a></td>
					<td class="columnText columnCategory">{$trophy->getCategory()->getTitle()}</td>
					
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
				<li><a href="{link controller='TrophyAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.trophy.add{/lang}</span></a></li>

				{event name='contentHeaderNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
