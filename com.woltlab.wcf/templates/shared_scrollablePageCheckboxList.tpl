<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/ItemList/Filter'], function(UiItemListFilter) {
		{jsphrase name='wcf.global.filter.button.visibility'}
		{jsphrase name='wcf.global.filter.button.clear'}
		{jsphrase name='wcf.global.filter.error.noMatches'}
		{jsphrase name='wcf.global.filter.placeholder'}
		{jsphrase name='wcf.global.filter.visibility.activeOnly'}
		{jsphrase name='wcf.global.filter.visibility.highlightActive'}
		{jsphrase name='wcf.global.filter.visibility.showAll'}
		
		new UiItemListFilter('{unsafe:$pageCheckboxListContainerID|encodeJS}');
	});
</script>

<ul class="scrollableCheckboxList" id="{$pageCheckboxListContainerID}">
	{foreach from=$pageNodeList item=pageNode}
		<li{if $pageNode->getDepth() > 1} style="padding-left: {$pageNode->getDepth()*20-20}px"{/if}>
			<label><input type="checkbox" name="{$pageCheckboxID}[]" value="{$pageNode->pageID}" data-identifier="{$pageNode->identifier}"{if $pageNode->pageID|in_array:$pageIDs} checked{/if}> {$pageNode->name}</label>
		</li>
	{/foreach}
</ul>
