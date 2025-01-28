
{foreach from=$view->getRows() item='row'}
	<tr class="gridView__row" data-object-id="{$view->getObjectID($row)}">
		{if $view->hasBulkInteractions()}
			<td class="gridView__column gridView__selectColumn">
				<input type="checkbox" class="gridView__selectRow" aria-label="{lang}wcf.clipboard.item.mark{/lang}">
			</td>
		{/if}
		{foreach from=$view->getVisibleColumns() item='column'}
			<td class="gridView__column {$column->getClasses()}">
				{unsafe:$view->renderColumn($column, $row)}
			</td>
		{/foreach}
		{if $view->hasInteractions()}
			<td class="gridView__column gridView__actionColumn">
				<div class="gridView__actionColumn__buttons">
					{unsafe:$view->renderQuickInteractions($row)}
					{unsafe:$view->renderInteractionContextMenuButton($row)}
				</div>
			</td>
		{/if}
	</tr>
{/foreach}
