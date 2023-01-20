<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Ui/ItemList/Filter'], function(Language, UiItemListFilter) {
		Language.addObject({
			'wcf.global.filter.button.visibility': '{jslang}wcf.global.filter.button.visibility{/jslang}',
			'wcf.global.filter.button.clear': '{jslang}wcf.global.filter.button.clear{/jslang}',
			'wcf.global.filter.error.noMatches': '{jslang}wcf.global.filter.error.noMatches{/jslang}',
			'wcf.global.filter.placeholder': '{jslang}wcf.global.filter.placeholder{/jslang}',
			'wcf.global.filter.visibility.activeOnly': '{jslang}wcf.global.filter.visibility.activeOnly{/jslang}',
			'wcf.global.filter.visibility.highlightActive': '{jslang}wcf.global.filter.visibility.highlightActive{/jslang}',
			'wcf.global.filter.visibility.showAll': '{jslang}wcf.global.filter.visibility.showAll{/jslang}'
		});
		
		new UiItemListFilter('{@$pageCheckboxListContainerID}');
	});
</script>

<ul class="scrollableCheckboxList" id="{@$pageCheckboxListContainerID}">
	{foreach from=$pageNodeList item=pageNode}
		<li{if $pageNode->getDepth() > 1} style="padding-left: {$pageNode->getDepth()*20-20}px"{/if}>
			<label><input type="checkbox" name="{@$pageCheckboxID}[]" value="{$pageNode->pageID}" data-identifier="{@$pageNode->identifier}"{if $pageNode->pageID|in_array:$pageIDs} checked{/if}> {$pageNode->name}</label>
		</li>
	{/foreach}
</ul>
