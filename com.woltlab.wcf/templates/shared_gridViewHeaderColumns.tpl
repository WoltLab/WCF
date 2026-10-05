{foreach from=$view->getVisibleColumns() item='column'}
	<th
		class="gridView__headerColumn {$column->getClasses()} {if $view->isSortedBy($column)}active {$view->getSortOrder()}{/if}"
		data-id="{$column->getID()}"
		data-sortable="{$column->isSortable()}"
		{if $column->isSortable()}data-default-sort-order="{$column->getDefaultSortOrder()}"{/if}
		{if $column->isSortable()}
			{if $view->isSortedBy($column)}
				aria-sort="{if $view->getSortOrder() === 'ASC'}ascending{else}descending{/if}"
			{else}
				aria-sort="none"
			{/if}
		{/if}
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
