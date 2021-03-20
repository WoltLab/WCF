{include file='header' pageTitle='wcf.acp.notice.list'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Sortable/List'], function (UiSortableList) {
		new UiSortableList({
			containerId: 'noticeList',
			className: 'wcf\\data\\notice\\NoticeAction',
			offset: {@$startIndex}
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.notice.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='NoticeAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.notice.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="NoticeList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section sortableListContainer" id="noticeList">
		<ol class="sortableList jsReloadPageWhenEmpty jsObjectActionContainer" data-object-action-class-name="wcf\data\notice\NoticeAction" data-object-id="0" start="{@($pageNo - 1) * $itemsPerPage + 1}">
			{foreach from=$objects item='notice'}
				<li class="sortableNode sortableNoNesting jsNotice jsObjectActionObject" data-object-id="{@$notice->getObjectID()}">
					<span class="sortableNodeLabel">
						<a href="{link controller='NoticeEdit' object=$notice}{/link}">{$notice->noticeName}</a>
						
						<span class="statusDisplay sortableButtonContainer">
							<span class="icon icon16 fa-arrows sortableNodeHandle"></span>
							{objectAction action="toggle" isDisabled=$notice->isDisabled}
							<a href="{link controller='NoticeEdit' object=$notice}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{objectAction action="delete" objectTitle=$notice->noticeName}
							
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
				<li><a href="{link controller='NoticeAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.notice.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
