<textarea {*
	*}id="{$field->getPrefixedId()}" {*
	*}name="{$field->getPrefixedId()}" {*
	*}class="wysiwygTextarea" {*
	*}data-disable-attachments="{if $field->supportsAttachments()}false{else}true{/if}" {*
	*}data-support-mention="{if $field->supportsMentions()}true{else}false{/if}"{*
	*}{if $field->getAutosaveId() !== null}{*
		*} data-autosave="{@$field->getAutosaveId()}"{*
		*}{if $field->getLastEditTime() !== 0}{*
			*} data-autosave-last-edit-time="{@$field->getLastEditTime()}"{*
		*}{/if}{*
	*}{/if}{*
	*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
*}>{$field->getValue()}</textarea>

{include file='shared_wysiwyg' wysiwygSelector=$field->getPrefixedId()}

{if $field->supportsQuotes()}
	<script data-relocate="true">
		// Bootstrap for window.__wcf_bc_eventHandler
		require(['WoltLabSuite/Core/Bootstrap'], function(Bootstrap) {
			{include file='shared_messageQuoteManager' wysiwygSelector=$field->getPrefixedId() supportPaste=true}
			
			{if $field->getQuoteData() !== null}
				var quoteHandler = new WCF.Message.Quote.Handler(
					$quoteManager,
					'{$field->getQuoteData('actionClass')|encodeJS}',
					'{$field->getQuoteData('objectType')}',
					'{$field->getQuoteData('selectors')[container]}',
					'{$field->getQuoteData('selectors')[messageBody]}',
					'{$field->getQuoteData('selectors')[messageContent]}',
					true
				);
				
				elData(elById('{@$field->getPrefixedId()|encodeJS}'), 'quote-handler', quoteHandler);
			{/if}
		});
	</script>
{/if}
