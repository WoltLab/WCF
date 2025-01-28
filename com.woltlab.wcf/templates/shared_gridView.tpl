<div class="gridView">
	{if $view->isFilterable()}
		<div class="gridView__filterBar">
			<div class="gridView__filters" id="{$view->getID()}_filters">
				{foreach from=$view->getActiveFilters() item='value' key='key'}
					<button type="button" class="button small" data-filter="{$key}" data-filter-value="{$value}">
						{icon name='circle-xmark'}
						{$view->getFilterLabel($key)}
					</button>
				{/foreach}
			</div>
			<div class="gridView__filterButton">
				<button type="button" class="button small" id="{$view->getID()}_filterButton" data-endpoint="{$view->getFilterActionEndpoint()}">
					{icon name='filter'}
					{lang}wcf.global.filter{/lang}
				</button>
			</div>
		</div>
	{/if}
	
	<div class="gridView__tableContainer">
		<table class="gridView__table" id="{$view->getID()}_table"{if !$view->countRows()} hidden{/if}>
			<thead>
				<tr class="gridView__headerRow">
					{if $view->hasBulkInteractions()}
						<th class="gridView__headerColumn gridView__selectColumn">
							<input type="checkbox" class="gridView__selectAllRows" aria-label="{lang}wcf.clipboard.item.markAll{/lang}">
						</th>
					{/if}
					{foreach from=$view->getVisibleColumns() item='column'}
						<th
							class="gridView__headerColumn {$column->getClasses()}"
							data-id="{$column->getID()}"
							data-sortable="{$column->isSortable()}"
						>
							{if $column->isSortable()}
								<button type="button" class="gridView__headerColumn__button">
									{unsafe:$column->getLabel()}
								</button>
							{else}
								{unsafe:$column->getLabel()}
							{/if}
						</th>
					{/foreach}
					{if $view->hasInteractions()}
						<th class="gridView__headerColumn gridView__actionColumn"></th>
					{/if}
				</td>
			</thead>
			<tbody>
				{unsafe:$view->renderRows()}
			</tbody>
		</table>
	</div>

	<div class="gridView__pagination">
		<woltlab-core-pagination id="{$view->getID()}_pagination" page="{$view->getPageNo()}" count="{$view->countPages()}"></woltlab-core-pagination>
	</div>

	{if $view->hasBulkInteractions()}
		<div id="{$view->getID()}_selectionBar" class="gridView__selectionBar dropdown" hidden>
			<button type="button" id="{$view->getID()}_bulkInteractionButton" class="button gridView__bulkInteractionButton dropdownToggle"></button>
			<ul class="dropdownMenu">
				<li class="disabled"><span>{lang}wcf.global.loading{/lang}</span></li>
				<li class="dropdownDivider"></li>
				<li>
					<button type="button" id="{$view->getID()}_resetSelectionButton">{lang}wcf.clipboard.item.unmarkAll{/lang}</button>
				</li>
			</ul>
		</div>
	{/if}

	<woltlab-core-notice type="info" id="{$view->getID()}_noItemsNotice"{if $view->countRows()} hidden{/if}>{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/GridView'], ({ GridView }) => {
		WoltLabLanguage.registerPhrase("wcf.clipboard.button.numberOfSelectedItems", '{jslang __literal=true}wcf.clipboard.button.numberOfSelectedItems{/jslang}');
		
		new GridView(
			'{unsafe:$view->getID()|encodeJs}',
			'{unsafe:$view->getClassName()|encodeJS}',
			{$view->getPageNo()},
			'{unsafe:$view->getBaseUrl()|encodeJS}',
			'{unsafe:$view->getSortField()|encodeJS}',
			'{unsafe:$view->getSortOrder()|encodeJS}',
			'{unsafe:$view->getBulkInteractionProviderClassName()|encodeJS}',
			new Map([
				{foreach from=$view->getParameters() key='name' item='value'}
					['{unsafe:$name|encodeJs}', '{unsafe:$value|encodeJs}'],
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
