
{foreach from=$view->getRows() item='row'}
	<tr>
		{foreach from=$view->getColumns() item='column'}
			<td class="{$column->getClasses()}">
				{unsafe:$view->renderColumn($column, $row)}
			</td>
		{/foreach}
	</tr>
{/foreach}
