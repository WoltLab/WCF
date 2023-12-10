<div id="acpDashboardSortableContainer" class="sortableListContainer">
	<ul class="sortableList" id="{$field->getPrefixedId()}_list">
		{foreach from=$field->getNestedOptions() item=__fieldNestedOption}
			<li{if $__fieldNestedOption[depth] > 0} style="padding-left: {$__fieldNestedOption[depth]*20}px"{/if}>
				<span class="sortableHandle">
					{icon name='arrows-up-down'}
				</span>
				
				<label>
					<input {*
						*}type="checkbox" {*
						*}name="{$field->getPrefixedId()}[]" {*
						*}value="{$__fieldNestedOption[value]}"{*
						*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
						*}{if $field->getValue() !== null && $__fieldNestedOption[value]|in_array:$field->getValue() && $__fieldNestedOption[isSelectable]} checked{/if}{*
						*}{if $field->isImmutable() || !$__fieldNestedOption[isSelectable]} disabled{/if}{*
						*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
					*}>
					{@$__fieldNestedOption[label]}
				</label>
			</li>
		{/foreach}
	</ul>
</div>

<script data-relocate="true">
	$(() => {
		new window.WCF.Sortable.List(
			'acpDashboardSortableContainer',
			'',
			0,
			{
				handle: '.sortableHandle'
			},
			true,
			{},
		);
	});
</script>
