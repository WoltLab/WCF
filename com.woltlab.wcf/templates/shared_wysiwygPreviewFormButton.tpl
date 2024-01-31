<button type="button" id="{$button->getPrefixedId()}"{*
	*} class="button{if !$button->getClasses()|empty} {implode from=$button->getClasses() item='class' glue=' '}{$class}{/implode}{/if}"{*
	*}{foreach from=$button->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if $button->getAccessKey()} accesskey="{$button->getAccessKey()}"{/if}{*
*}>{$button->getLabel()}</button>

<script data-relocate="true">
	require(['Language'], function(Language) {
		Language.addObject({
			'wcf.global.preview': '{jslang}wcf.global.preview{/jslang}'
		});
		
		new WCF.Message.DefaultPreview({
			messageFieldID: '{@$button->getPrefixedWysiwygId()|encodeJS}',
			previewButtonID: '{@$button->getPrefixedId()|encodeJS}',
			messageObjectType: '{@$button->getObjectType()->objectType|encodeJS}',
			messageObjectID: '{@$button->getObjectId()}'
		});
	});
</script>
