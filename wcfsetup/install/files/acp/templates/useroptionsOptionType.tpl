{if $option->issortable}
	<script data-relocate="true">
		document.addEventListener('DOMContentLoaded', function() {
			require(["WoltLabSuite/Core/Ui/Sortable/List"], function (UiSortableList) {
				new UiSortableList({
					containerId: '{$option->optionName}SortableList',
				});
			});
		});
	</script>
	
	<div id="{$option->optionName}SortableList" class="sortableListContainer">
		<ol class="sortableList">
			{foreach from=$availableOptions item=availableOption}
				<li class="sortableNode">
					<span class="sortableNodeLabel">
						<span class="sortableNodeHandle">
							{icon name='arrows-up-down-left-right'}
						</span>
						<label>
							<input type="checkbox" name="values[{$option->optionName}][]" value="{$availableOption}"{if $availableOption|in_array:$value} checked{/if}>
							{$optionTitles[$availableOption]}
						</label>
					</span>
				</li>
			{/foreach}
		</ol>
	</div>
{else}
	{foreach from=$availableOptions item=availableOption}
		<label><input type="checkbox" name="values[{$option->optionName}][]" value="{$availableOption}"{if $availableOption|in_array:$value} checked{/if}> {$optionTitles[$availableOption]}</label>
	{/foreach}
{/if}
