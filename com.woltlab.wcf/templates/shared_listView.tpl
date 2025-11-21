<div class="listView">
	{if $view->isSortable() || $view->isFilterable() || $view->hasBulkInteractions()}
		<div class="listView__header">
			{if $view->isFilterable()}
				<div class="listView__filters" id="{$view->getID()}_filters">
					{foreach from=$view->getActiveFilters() item='value' key='key'}
						<button
							type="button"
							class="button small jsTooltip"
							data-filter="{$key}"
							data-filter-value="{$value}"
							title="{lang filterLabel=$view->getFilterLabel($key)}wcf.page.removeFilterTooltip{/lang}"
						>
							{icon name='circle-xmark'}
							{$view->getFilterLabel($key)}
						</button>
					{/foreach}
				</div>
			{/if}
			<div class="listView__header__buttons">
				{if $view->hasAvailableInteractions()}
					<div class="listView__header__button">
						<button type="button" class="button small listView__editMode__toggle">
							{icon name='pencil'}
							<span>{lang}wcf.global.button.edit{/lang}</span>
						</button>

						{if $view->hasBulkInteractions()}
							<label class="listView__selectAllItems__label jsTooltip" title="{lang}wcf.clipboard.item.markAll{/lang}">
								<input type="checkbox" id="{$view->getID()}_selectAllItems" class="listView__selectAllItems"
									aria-label="{lang}wcf.clipboard.item.markAll{/lang}">
							</label>
						{/if}
					</div>
				{/if}
				{if $view->isSortable()}
					<div class="listView__header__button dropdown">
						<button type="button" class="button small dropdownToggle">
							{icon name='arrow-down-short-wide'}
							<span>{lang}wcf.global.sorting{/lang}</span>
						</button>
						<ul class="dropdownMenu" id="{$view->getID()}_sorting">
							{foreach from=$view->getAvailableSortFields() item='sortField'}
								<li>
									<button type="button" class="listView__sorting__button" data-sort-id="{$sortField->id}">
										{unsafe:$sortField}
									</button>
								</li>
							{/foreach}
						</ul>
					</div>
				{/if}
				{if $view->isFilterable()}
					<div class="listView__header__button">
						<button type="button" class="button small" id="{$view->getID()}_filterButton" data-endpoint="{$view->getFilterActionEndpoint()}">
							{icon name='sliders'}
							{lang}wcf.global.filter{/lang}
						</button>
					</div>
				{/if}
			</div>
		</div>
	{/if}
	
	<div class="listView__itemContainer">
		<div class="listView__items {$view->getCssClassName()}" id="{$view->getID()}_items"{if !$view->countItems()} hidden{/if}>
			{unsafe:$view->renderItems()}
		</div>
	</div>

	<woltlab-core-notice type="info" id="{$view->getID()}_noItemsNotice"{if $view->countItems()} hidden{/if}>{lang}wcf.global.noItems{/lang}</woltlab-core-notice>

	<div class="listView__footer" id="{$view->getID()}_footer"{if $view->countPages() < 2} hidden{/if}>
		{if $view->hasBulkInteractions()}
			<div class="listView__selectionBar__container">
				<div id="{$view->getID()}_selectionBar" class="listView__selectionBar dropdown" hidden>
					<button type="button" id="{$view->getID()}_bulkInteractionButton" class="button small listView__bulkInteractionButton dropdownToggle"></button>
					<ul class="dropdownMenu">
						<li class="disabled"><span>{lang}wcf.global.loading{/lang}</span></li>
						<li class="dropdownDivider"></li>
						<li>
							<button type="button" id="{$view->getID()}_resetSelectionButton">{lang}wcf.clipboard.item.unmarkAll{/lang}</button>
						</li>
					</ul>
				</div>
			</div>
		{/if}

		<div class="listView__pagination">
			<woltlab-core-pagination
				id="{$view->getID()}_pagination"
				page="{$view->getPageNo()}"
				count="{$view->countPages()}"
				behavior="button"
				url="{$view->getBaseUrl()}"
			></woltlab-core-pagination>
		</div>
	</div>
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/ListView'], ({ ListView }) => {
		WoltLabLanguage.registerPhrase("wcf.clipboard.button.numberOfSelectedItems", '{jslang __literal=true}wcf.clipboard.button.numberOfSelectedItems{/jslang}');
		WoltLabLanguage.registerPhrase("wcf.page.removeFilterTooltip", '{jslang __literal=true}wcf.page.removeFilterTooltip{/jslang}');
		
		new ListView(
			'{unsafe:$view->getID()|encodeJS}',
			'{unsafe:$view->getClassName()|encodeJS}',
			{$view->getPageNo()},
			'{unsafe:$view->getBaseUrl()|encodeJS}',
			'{unsafe:$view->getSortField()|encodeJS}',
			'{unsafe:$view->getSortOrder()|encodeJS}',
			'{unsafe:$view->getDefaultSortField()|encodeJS}',
			'{unsafe:$view->getDefaultSortOrder()|encodeJS}',
			'{unsafe:$view->getBulkInteractionProviderClassName()|encodeJS}',
			new Map([
				{foreach from=$view->getParameters() key='name' item='value'}
					['{unsafe:$name|encodeJS}', {unsafe:$value|json}],
				{/foreach}
			]),
		);
	});
</script>
{if $view->hasInteractions()}
	{unsafe:$view->renderInteractionInitialization()}
{/if}
{if $view->hasBulkInteractions()}
	{unsafe:$view->renderBulkInteractionInitialization()}
{/if}
