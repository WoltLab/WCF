{include file='header' pageTitle='wcf.acp.menu.link.reactionType.list'}

{if $objects|count}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Sortable/List'], function (UiSortableList) {
			new UiSortableList({
				containerId: 'reactionTypeList',
				className: 'wcf\\data\\reaction\\type\\ReactionTypeAction',
				offset: {@$startIndex}
			});
		});
		
		$(function() {
			new WCF.Action.Delete('wcf\\data\\reaction\\type\\ReactionTypeAction', '.reactionTypeRow');
			new WCF.Action.Toggle('wcf\\data\\reaction\\type\\ReactionTypeAction', '.reactionTypeRow');
		});
	</script>
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.reactionType.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='ReactionTypeAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.reactionType.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="ReactionTypeList" link="pageNo=%d"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div id="reactionTypeList" class="sortableListContainer section">
		<ol class="sortableList" data-object-id="0" start="{@($pageNo - 1) * $itemsPerPage + 1}">
			{foreach from=$objects item=reactionType}
				<li class="sortableNode sortableNoNesting reactionTypeRow" data-object-id="{@$reactionType->reactionTypeID}">
					<span class="sortableNodeLabel">
						<a href="{link controller='ReactionTypeEdit' id=$reactionType->reactionTypeID}{/link}">{@$reactionType->renderIcon()} {$reactionType->getTitle()}</a>
						
						<span class="statusDisplay sortableButtonContainer">
							<span class="icon icon16 fa-arrows sortableNodeHandle"></span>
							<span class="jsOnly icon icon16 fa-{if $reactionType->isAssignable}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.acp.reactionType.{if !$reactionType->isAssignable}allow{else}disallow{/if}{/lang}" data-object-id="{@$reactionType->reactionTypeID}"></span>
							<a href="{link controller='ReactionTypeEdit' id=$reactionType->reactionTypeID}{/link}"><span title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip icon icon16 fa-pencil"></span></a>
							<span title="{lang}wcf.global.button.delete{/lang}" class="jsDeleteButton pointer jsTooltip icon icon16 fa-times" data-object-id="{@$reactionType->reactionTypeID}" data-confirm-message-html="{lang __encode=true}wcf.acp.reactionType.delete.confirmMessage{/lang}"></span>
							
							{event name='itemButtons'}
						</span>
					</span>
					<ol class="sortableList" data-object-id="{@$reactionType->reactionTypeID}"></ol>
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
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}
						{event name='contentFooterNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
