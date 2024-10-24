<input type="password" {*
	*}id="{$field->getPrefixedId()}" {*
	*}name="{$field->getPrefixedId()}" {*
	*}value="{$field->getValue()}"{*
	*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{if $field->getInputMode() !== null} inputmode="{$field->getInputMode()}"{/if}{*
	*}{if $field->getAutoComplete() !== null} autocomplete="{$field->getAutoComplete()}"{/if}{*
	*}{if $field->getPattern() !== null} pattern="{$field->getPattern()}"{/if}{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isRequired()} required{/if}{*
	*}{if $field->isImmutable()} disabled{/if}{*
	*}{if $field->getMinimumLength() !== null} minlength="{$field->getMinimumLength()}"{/if}{*
	*}{if $field->getMaximumLength() !== null} maxlength="{$field->getMaximumLength()}"{/if}{*
	*}{if $field->getPlaceholder() !== null} placeholder="{$field->getPlaceholder()}"{/if}{*
	*}{if $field->getDocument()->isAjax()} data-dialog-submit-on-enter="true"{/if}{*
	*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
*}>

{if $field->getStrengthMeter()}
	<script data-relocate="true">
		require(["WoltLabSuite/Core/Ui/User/PasswordStrength", "Language"], (PasswordStrength, Language) => {
			{include file='shared_passwordStrengthLanguage'}

			new PasswordStrength(document.getElementById('{unsafe:$field->getPrefixedId()|encodeJS}'), {
				relatedInputs: [
				{foreach from=$field->getRelatedFieldsIDs() item=fieldId}
					document.getElementById('{unsafe:$fieldId|encodeJS}'),
				{/foreach}
				],
			});
		});
	</script>
{/if}
