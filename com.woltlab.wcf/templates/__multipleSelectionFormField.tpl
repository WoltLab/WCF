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
		{foreach from=$field->getNestedOptions() item=__fieldNestedOption}
			<li{if $__fieldNestedOption[depth] > 0} style="padding-left: {$__fieldNestedOption[depth]*20}px"{/if}>
				<label><input {*
						*}type="checkbox" {*
						*}name="{@$field->getPrefixedId()}[]" {*
						*}value="{$__fieldNestedOption[value]}"{*
						*}{if $field->getValue() !== null && $__fieldNestedOption[value]|in_array:$field->getValue()} checked{/if}{*
						*}{if $field->isImmutable()} disabled{/if}{*
					*}> {@$__fieldNestedOption[label]}</label>
			</li>
		{/foreach}
	</ul>
{else}
	{htmlCheckboxes options=$field->getOptions() name=$field->getPrefixedId() selected=$field->getValue() disabled=$field->isImmutable() disableEncoding=true}
{/if}

{include file='__formFieldFooter'}
