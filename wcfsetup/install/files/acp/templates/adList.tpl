{include file='header' pageTitle='wcf.acp.ad.list'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Sortable/List'], function (UiSortableList) {
		new UiSortableList({
			containerId: 'adList',
			className: 'wcf\\data\\ad\\AdAction',
			offset: {@$startIndex}
		});
	});
	
	$(function() {
		new WCF.Action.Delete('wcf\\data\\ad\\AdAction', '.jsAd');
		new WCF.Action.Toggle('wcf\\data\\ad\\AdAction', '.jsAd');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.ad.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='AdAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.ad.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="AdList" link="pageNo=%d"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section sortableListContainer" id="adList">
		<ol class="sortableList jsReloadPageWhenEmpty" data-object-id="0" start="{@($pageNo - 1) * $itemsPerPage + 1}">
			{foreach from=$objects item='ad'}
				<li class="sortableNode sortableNoNesting jsAd" data-object-id="{@$ad->adID}">
					<span class="sortableNodeLabel">
						<a href="{link controller='AdEdit' object=$ad}{/link}">{$ad->adName}</a>
						
						<span class="statusDisplay sortableButtonContainer">
							<span class="icon icon16 fa-arrows sortableNodeHandle"></span>
							<span class="icon icon16 fa-{if !$ad->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $ad->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$ad->adID}"></span>
							<a href="{link controller='AdEdit' object=$ad}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$ad->adID}" data-confirm-message-html="{lang __encode=true}wcf.acp.ad.delete.confirmMessage{/lang}"></span>
							
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
				<li><a href="{link controller='AdAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.ad.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
