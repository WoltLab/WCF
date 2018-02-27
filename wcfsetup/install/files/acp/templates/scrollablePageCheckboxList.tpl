<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Ui/ItemList/Filter'], function(Language, UiItemListFilter) {
		Language.addObject({
			'wcf.global.filter.button.visibility': '{lang}wcf.global.filter.button.visibility{/lang}',
			'wcf.global.filter.button.clear': '{lang}wcf.global.filter.button.clear{/lang}',
			'wcf.global.filter.error.noMatches': '{lang}wcf.global.filter.error.noMatches{/lang}',
			'wcf.global.filter.placeholder': '{lang}wcf.global.filter.placeholder{/lang}',
			'wcf.global.filter.visibility.activeOnly': '{lang}wcf.global.filter.visibility.activeOnly{/lang}',
			'wcf.global.filter.visibility.highlightActive': '{lang}wcf.global.filter.visibility.highlightActive{/lang}',
			'wcf.global.filter.visibility.showAll': '{lang}wcf.global.filter.visibility.showAll{/lang}'
		});
		
		new UiItemListFilter('{@$pageCheckboxListContainerID}');
	});
</script>

<ul class="scrollableCheckboxList" id="{@$pageCheckboxListContainerID}">
	{foreach from=$pageNodeList item=pageNode}
		<li{if $pageNode->getDepth() > 1} style="padding-left: {$pageNode->getDepth()*20-20}px"{/if}>
			<label><input type="checkbox" name="{@$pageCheckboxID}[]" value="{@$pageNode->pageID}" data-identifier="{@$pageNode->identifier}"{if $pageNode->pageID|in_array:$pageIDs} checked{/if}> {$pageNode->name}</label>
		</li>
	{/foreach}
</ul>
