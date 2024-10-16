
{foreach from=$view->getRows() item='row'}
	<tr>
		{foreach from=$view->getColumns() item='column'}
			<td class="{$column->getClasses()}">
				{unsafe:$view->renderColumn($column, $row)}
			</td>
		{/foreach}
		{if $view->hasActions()}
			<td>
				<div class="dropdown">
					<button type="button" class="gridViewActions button small dropdownToggle" aria-label="{lang}wcf.global.button.more{/lang}">{icon name='ellipsis-vertical'}</button>

					<ul class="dropdownMenu">
							{foreach from=$view->getActions() item='action'}
								<li>
									{unsafe:$view->renderAction($action, $row)}
								</li>
							{/foreach}
						
					</ul>
				</div>
			</td>
		{/if}
	</tr>
{/foreach}
