{include file='header'}

{if $categoryNodeList->hasChildren()}
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			{if $collapsibleObjectTypeID}
				new WCF.ACP.Category.Collapsible('wcf\\data\\category\\CategoryAction', {@$collapsibleObjectTypeID});
			{/if}
			
			{if $objectType->getProcessor()->canDeleteCategory()}
				new WCF.Action.NestedDelete('wcf\\data\\category\\CategoryAction', '.jsCategory');
			{/if}
			{if $objectType->getProcessor()->canEditCategory()}
				new WCF.Action.Toggle('wcf\\data\\category\\CategoryAction', '.jsCategory', '> .sortableNodeLabel > .buttons > .jsToggleButton');
				
				var sortableNodes = $('.sortableNode');
				sortableNodes.each(function(index, node) {
					$(node).wcfIdentify();
				});
				
				new WCF.Sortable.List('categoryList', 'wcf\\data\\category\\CategoryAction', 0{if $objectType->getProcessor()->getMaximumNestingLevel() != -1}, {
					/**
					 * Updates the sortable nodes after a sorting is started with
					 * regard to their possibility to have child the currently sorted
					 * category as a child category.
					 */
					start: function(event, ui) {
						var sortedListItem = $(ui.item);
						var itemNestingLevel = sortedListItem.find('.sortableList:has(.sortableNode)').length;
						
						sortableNodes.each(function(index, node) {
							node = $(node);
							
							if (node.attr('id') != sortedListItem.attr('id')) {
								if (node.parents('.sortableList').length + itemNestingLevel >= {@$objectType->getProcessor()->getMaximumNestingLevel() + 1}) {
									node.addClass('sortableNoNesting');
								}
								else if (node.hasClass('sortableNoNesting')) {
									node.removeClass('sortableNoNesting');
								}
							}
						});
					},
					
					/**
					 * Updates the sortable nodes after a sorting is completed with
					 * regard to their possibility to have child categories.
					 */
					stop: function(event, ui) {
						sortableNodes.each(function(index, node) {
							node = $(node);
							
							if (node.parents('.sortableList').length == {@$objectType->getProcessor()->getMaximumNestingLevel() + 1}) {
								node.addClass('sortableNoNesting');
							}
							else if (node.hasClass('sortableNoNesting')) {
								node.removeClass('sortableNoNesting');
							}
						});
					}
				}{/if});
			{/if}
		});
		//]]>
	</script>
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{@$objectType->getProcessor()->getLanguageVariable('list')}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $objectType->getProcessor()->canAddCategory()}
						<li><a href="{link controller=$addController application=$objectType->getProcessor()->getApplication()}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{@$objectType->getProcessor()->getLanguageVariable('add')}</span></a></li>
					{/if}
						
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{hascontent}
	<div id="categoryList" class="section{if $objectType->getProcessor()->canEditCategory()} sortableListContainer{/if}">
		<ol class="categoryList sortableList" data-object-id="0">
			{content}
				{assign var=oldDepth value=0}
				{foreach from=$categoryNodeList item='category'}
					{section name=i loop=$oldDepth-$categoryNodeList->getDepth()}</ol></li>{/section}
					
					<li class="{if $objectType->getProcessor()->canEditCategory()}sortableNode {if $categoryNodeList->getDepth() == $objectType->getProcessor()->getMaximumNestingLevel()}sortableNoNesting {/if}{/if}jsCategory" data-object-id="{@$category->categoryID}"{if $collapsedCategoryIDs|is_array} data-is-open="{if $collapsedCategoryIDs[$category->categoryID]|isset}0{else}1{/if}"{/if}>
						<span class="sortableNodeLabel">
							<span class="title">
								{if $objectType->getProcessor()->canEditCategory()}
									<a href="{link controller=$editController application=$objectType->getProcessor()->getApplication() id=$category->categoryID title=$category->getTitle()}{/link}">{$category->getTitle()}</a>
								{else}
									{$category->getTitle()}
								{/if}
							</span>
							
							<span class="statusDisplay buttons">
								{if $objectType->getProcessor()->canEditCategory()}
									<a href="{link controller=$editController application=$objectType->getProcessor()->getApplication() id=$category->categoryID title=$category->getTitle()}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
								{/if}
								
								{if $objectType->getProcessor()->canDeleteCategory()}
									<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$category->categoryID}" data-confirm-message="{@$objectType->getProcessor()->getLanguageVariable('delete.sure')}"></span>
								{/if}
								
								{if $objectType->getProcessor()->canEditCategory()}
									<span class="icon icon16 fa-{if !$category->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$category->isDisabled}disable{else}enable{/if}{/lang}" data-object-id="{@$category->categoryID}"></span>
								{/if}
								
								{event name='itemButtons'}
							</span>
						</span>
						
						<ol class="categoryList sortableList" data-object-id="{@$category->categoryID}">
					{if !$categoryNodeList->current()->hasChildren()}
						</ol></li>
					{/if}
					{assign var=oldDepth value=$categoryNodeList->getDepth()}
				{/foreach}
				{section name=i loop=$oldDepth}</ol></li>{/section}
			{/content}
		</ol>
	</div>
	
	<div class="formSubmit">
		<button class="button" data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
	</div>
{hascontentelse}
	<p class="info">{@$objectType->getProcessor()->getLanguageVariable('noneAvailable')}</p>
{/hascontent}

{include file='footer'}