<ul class="labelSelection{if !$field->getClasses()|empty} {implode from=$field->getClasses() item=class glue=' '}{$class}{/implode}{/if}">
	{foreach from=$field->getOptions() item=color}
		<li{if $color === 'custom'} class="labelSelection__custom custom"{/if}>
			<label class="labelSelection__label">
				<input {*
					*}type="radio" {*
					*}name="{$field->getPrefixedId()}" {*
					*}value="{$color}"{*
					*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item=class glue=' '}{$class}{/implode}"{/if}{*
					*}{if $field->getValue() === $color || ($color === 'custom' && !$field->getCustomClassName()|empty)} checked{/if}{*
					*}{if $field->isImmutable()} disabled{/if}{*
					*}{foreach from=$field->getFieldAttributes() key=attributeName item=attributeValue} {$attributeName}="{$attributeValue}"{/foreach}{*
					*}>
				{if $color === 'custom'}
					<span class="labelSelection__span labelSelection__span--custom">
						<input type="text" id="{$field->getPrefixedId()}Custom" {*
							*}name="{$field->getPrefixedId()}customCssClassName" {*
						    *}value="{$field->getCustomClassName()}" {*
							*}class="long labelSelection__custom__input" {*
							*}{if $field->getPattern() !== null} pattern="{$field->getPattern()}"{/if}{*
						*}>
					</span>
				{else}
					<span class="labelSelection__span badge label{if $color != 'none'} {$color}{/if}">{$field->getDefaultLabelText()}</span>
				{/if}
			</label>
		</li>
	{/foreach}
</ul>

<script data-relocate="true">
	{if $field->getTextReferenceNodeId()}
		require(["WoltLabSuite/Core/Form/Builder/Field/Controller/BadgeColor"], ({ setup }) => {
			setup(
				'{unsafe:$field->getPrefixedId()|encodeJS}Container',
				'{unsafe:$field->getTextReferenceNodeId()|encodeJS}',
				'{unsafe:$field->getDefaultLabelText()|encodeJS}',
			);
		});
	{/if}
	{
		const customInput = document.querySelector('#{unsafe:$field->getPrefixedId()|encodeJS}Container .labelSelection__custom__input');
		const customRadioInput = document.querySelector('#{unsafe:$field->getPrefixedId()|encodeJS}Container .custom > .labelSelection__label > input[type="radio"]');
		if (customInput && customRadioInput) {
			customInput.addEventListener("focus", () => {
				customRadioInput.checked = true;
			});
		}
	}
</script>
