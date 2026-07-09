{if $field->isFilterable()}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/ItemList/Filter'], function(UiItemListFilter) {
			{jsphrase name='wcf.global.filter.button.visibility'}
			{jsphrase name='wcf.global.filter.button.clear'}
			{jsphrase name='wcf.global.filter.error.noMatches'}
			{jsphrase name='wcf.global.filter.placeholder'}
			{jsphrase name='wcf.global.filter.visibility.activeOnly'}
			{jsphrase name='wcf.global.filter.visibility.highlightActive'}
			{jsphrase name='wcf.global.filter.visibility.showAll'}
			
			new UiItemListFilter('{unsafe:$field->getPrefixedId()|encodeJS}_list');
		});
	</script>
	
	<ul class="scrollableCheckboxList" id="{$field->getPrefixedId()}_list">
		{foreach from=$field->getNestedOptions() item=__fieldNestedOption}
			<li{if $__fieldNestedOption[depth] > 0} style="padding-left: {$__fieldNestedOption[depth]*20}px"{/if}>
				<label><input {*
						*}type="radio" {*
						*}name="{$field->getPrefixedId()}" {*
						*}value="{$__fieldNestedOption[value]}"{*
						*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
						*}{if $field->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]} checked{/if}{*
						*}{if $field->isImmutable() || !$__fieldNestedOption[isSelectable]} disabled{/if}{*
					*}> {unsafe:$__fieldNestedOption[label]}</label>
			</li>
		{/foreach}
	</ul>
{else}
	<select id="{$field->getPrefixedId()}" {*
		*}name="{$field->getPrefixedId()}"{*
		*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}>
		{foreach from=$field->getNestedOptions() item=__fieldNestedOption}
			<option {*
				*}value="{$__fieldNestedOption[value]}"{*
				*}{if $field->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]} selected{/if}{*
				*}{if $field->isImmutable() || !$__fieldNestedOption[isSelectable]} disabled{/if}{*
			*}>{unsafe:'&nbsp;'|str_repeat:$__fieldNestedOption[depth] * 4}{unsafe:$__fieldNestedOption[label]}</option>
		{/foreach}
	</select>
{/if}
