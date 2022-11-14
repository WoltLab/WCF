<input id="{$field->getPrefixedId()}" {*
	*}type="text" {*
	*}value="" {*
	*}class="long"{*
	*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
*}>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/ItemList'], function(UiItemList) {
		UiItemList.init(
			'{@$field->getPrefixedId()|encodeJS}',
			[{if $field->getValue() !== null && !$field->getValue()|empty}{implode from=$field->getValue() item=tag}'{@$tag|encodeJS}'{/implode}{/if}],
			{
				ajax: {
					className: 'wcf\\data\\tag\\TagAction'
				},
				maxLength: {@TAGGING_MAX_TAG_LENGTH},
				submitFieldName: '{@$field->getPrefixedId()|encodeJS}[]'
			}
		);
	});
</script>
