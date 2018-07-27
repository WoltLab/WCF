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
				<label><input type="radio" name="{@$field->getPrefixedId()}" value="{$__fieldNestedOption[value]}"{if $field->getValue() === $__fieldNestedOption[value]} checked{/if}> {@$__fieldNestedOption[label]}</label>
			</li>
		{/foreach}
	</ul>
{else}
	<select id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}">
		{foreach from=$field->getNestedOptions() item=__fieldNestedOption}
			<option name="{@$field->getPrefixedId()}" value="{$__fieldNestedOption[value]}"{if $field->getValue() === $__fieldNestedOption[value]} selected{/if}>{@'&nbsp;'|str_repeat:$__fieldNestedOption[depth] * 4}{@$__fieldNestedOption[label]}</option>
		{/foreach}
	</select>
{/if}

{include file='__formFieldFooter'}
