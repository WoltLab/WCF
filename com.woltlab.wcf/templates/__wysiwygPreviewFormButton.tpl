<button id="{@$button->getPrefixedId()}"{*
	*}{if !$button->getClasses()|empty} class="{implode from=$button->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$button->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if $button->getAccessKey()} accesskey="{$button->getAccessKey()}"{/if}{*
*}>{$button->getLabel()}</button>

<script data-relocate="true">
	require(['Language'], function(Language) {
		Language.addObject({
			'wcf.global.preview': '{lang}wcf.global.preview{/lang}'
		});
		
		new WCF.Message.DefaultPreview({
			messageFieldID: '{@$button->getWysiwygId()}',
			previewButtonID: '{@$button->getPrefixedId()}',
			messageObjectType: '{@$button->getObjectType()->objectType}',
			messageObjectID: '{@$button->getObjectId()}'
		});
	});
</script>
