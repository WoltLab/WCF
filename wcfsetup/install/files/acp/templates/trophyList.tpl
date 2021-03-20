{include file='header' pageTitle='wcf.acp.menu.link.trophy.list'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Sortable/List'], function (UiSortableList) {
		new UiSortableList({
			containerId: 'trophyList',
			className: 'wcf\\data\\trophy\\TrophyAction',
			offset: {@$startIndex}
		});
	});
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
			{pages print=true assign=pagesLinks controller='TrophyList' link="pageNo=%d"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section sortableListContainer" id="trophyList">
		<ol class="sortableList jsReloadPageWhenEmpty jsObjectActionContainer" data-object-action-class-name="wcf\data\trophy\TrophyAction" data-object-id="0" start="{@($pageNo - 1) * $itemsPerPage + 1}">
			{foreach from=$objects item='trophy'}
				<li class="sortableNode sortableNoNesting trophyRow jsObjectActionObject" data-object-id="{@$trophy->getObjectID()}">
					<span class="sortableNodeLabel">
						{@$trophy->renderTrophy(32)}
						<a href="{link controller='TrophyEdit' object=$trophy}{/link}">{$trophy->getTitle()}</a>
						
						<span class="statusDisplay sortableButtonContainer">
							<span class="icon icon16 fa-arrows sortableNodeHandle"></span>
							{objectAction action="toggle" isDisabled=$trophy->isDisabled}
							<a href="{link controller='TrophyEdit' object=$trophy}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{objectAction action="delete" objectTitle=$trophy->getTitle()}
							
							{event name='itemButtons'}
						</span>
					</span>
				</li>
			{/foreach}
		</ol>
	</div>
	
	<div class="formSubmit">
		<button class="button buttonPrimary" data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
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
