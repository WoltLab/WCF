{if $view->countRows()}
	<div class="paginationTop">
		<woltlab-core-pagination id="{$view->getID()}_topPagination" page="{$view->getPageNo()}" count="{$view->countPages()}"></woltlab-core-pagination>
	</div>

	<div class="section tabularBox">
		<table class="table" id="{$view->getID()}_table">
			<thead>
				<tr>
					{foreach from=$view->getColumns() item='column'}
						<th
							class="{$column->getClasses()}"
							data-id="{$column->getID()}"
							data-sortable="{$column->isSortable()}"
						>
							{unsafe:$column->getLabel()}
						</th>
					{/foreach}
					<th></th>
				</td>
			</thead>
			<tbody>
				{unsafe:$view->renderRows()}
			</tbody>
		</table>
	</div>

	<div class="paginationBottom">
		<woltlab-core-pagination id="{$view->getID()}_bottomPagination" page="{$view->getPageNo()}" count="{$view->countPages()}"></woltlab-core-pagination>
	</div>

	<script data-relocate="true">
		require(['WoltLabSuite/Core/Component/GridView'], ({ GridView }) => {
			new GridView(
				'{unsafe:$view->getID()|encodeJs}',
				'{unsafe:$view->getClassName()|encodeJS}',
				{$view->getPageNo()},
				'{unsafe:$view->getBaseUrl()|encodeJS}',
				'{unsafe:$view->getSortField()|encodeJS}',
				'{unsafe:$view->getSortOrder()|encodeJS}'
			);
		});
	</script>
	{unsafe:$view->renderActionInitialization()}
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}
