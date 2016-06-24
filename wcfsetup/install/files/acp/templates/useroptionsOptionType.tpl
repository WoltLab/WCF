{if $option->issortable}
	<script data-relocate="true">
		document.addEventListener('DOMContentLoaded', function() {
			require(['Dom/Traverse', 'Dom/Util', 'Ui/TabMenu'], function (DomTraverse, DomUtil, UiTabMenu) {
				var sortableList = elById('{$option->optionName}SortableList');
				var tabMenu = UiTabMenu.getTabMenu(DomUtil.identify(DomTraverse.parentByClass(sortableList, 'tabMenuContainer')));
				var activeTab = tabMenu.getActiveTab();
				
				// select the tab the sortable list is in as jQuery's sortable requires
				// the sortable list to be visible
				tabMenu.select(null, DomTraverse.parentByClass(sortableList, 'tabMenuContent'));
				
				new WCF.Sortable.List('{$option->optionName}SortableList', null, 0, { }, true);
				
				// re-select the previously selected tab
				tabMenu.select(null, activeTab);
			});
		});
	</script>
	
	<div id="{$option->optionName}SortableList" class="sortableListContainer">
		<ol class="sortableList">
			{foreach from=$availableOptions item=availableOption}
				<li class="sortableNode">
					<span class="sortableNodeLabel">
						<label><input type="checkbox" name="values[{$option->optionName}][]" value="{$availableOption}"{if $availableOption|in_array:$value} checked{/if}> {lang}wcf.user.option.{$availableOption}{/lang}</label>
					</span>
				</li>
			{/foreach}
		</ol>
	</div>
{else}
	{foreach from=$availableOptions item=availableOption}
		<label><input type="checkbox" name="values[{$option->optionName}][]" value="{$availableOption}"{if $availableOption|in_array:$value} checked{/if}> {lang}wcf.user.option.{$availableOption}{/lang}</label>
	{/foreach}
{/if}