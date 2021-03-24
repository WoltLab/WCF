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
		<ol class="sortableList jsReloadPageWhenEmpty jsObjectActionContainer" data-object-action-class-name="wcf\data\reaction\type\ReactionTypeAction" data-object-id="0" start="{@($pageNo - 1) * $itemsPerPage + 1}">
			{foreach from=$objects item=reactionType}
				<li class="sortableNode sortableNoNesting reactionTypeRow jsObjectActionObject" data-object-id="{@$reactionType->getObjectID()}">
					<span class="sortableNodeLabel">
						<a href="{link controller='ReactionTypeEdit' id=$reactionType->reactionTypeID}{/link}">{@$reactionType->renderIcon()} {$reactionType->getTitle()}</a>
						
						<span class="statusDisplay sortableButtonContainer">
							<span class="icon icon16 fa-arrows sortableNodeHandle"></span>
							{assign var='reactionTypeIsDisabled' value=true}
							{if $reactionType->isAssignable}
								{assign var='reactionTypeIsDisabled' value=false}
							{/if}
							{objectAction action="toggle" isDisabled=$reactionTypeIsDisabled disableTitle='wcf.acp.reactionType.isAssignable' enableTitle='wcf.acp.reactionType.isNotAssignable'}
							<a href="{link controller='ReactionTypeEdit' id=$reactionType->reactionTypeID}{/link}"><span title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip icon icon16 fa-pencil"></span></a>
							{objectAction action="delete" objectTitle=$reactionType->getTitle()}
							
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
