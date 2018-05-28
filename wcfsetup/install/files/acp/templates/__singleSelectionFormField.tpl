{include file='__formFieldHeader'}

{if $field->isFilterable()}
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
			
			new UiItemListFilter('{@$field->getPrefixedId()}_list');
		});
	</script>
	
	<ul class="scrollableCheckboxList" id="{@$field->getPrefixedId()}_list">
		{foreach from=$field->getOptions() key=$__fieldValue item=__fieldLabel}
			<li>
				<label><input type="radio" name="{@$field->getPrefixedId()}" value="{$__fieldValue}"{if $field->getValue() === $__fieldValue} checked{/if}> {@$__fieldLabel}</label>
			</li>
		{/foreach}
	</ul>
{else}
	<select id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}">
		{htmlOptions options=$field->getOptions() selected=$field->getValue() disableEncoding=true}
	</select>
{/if}

{include file='__formFieldFooter'}
