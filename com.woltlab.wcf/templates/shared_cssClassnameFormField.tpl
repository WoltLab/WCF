<ul class="classNameSelection{if !$field->getClasses()|empty} {implode from=$field->getClasses() item=class glue=' '}{$class}{/implode}{/if}">
	{foreach from=$field->getOptions() key=className item=label}
		<li{if $className == 'custom'} class="custom"{/if}>
			<label class="classNameSelection__label">
				<input {*
					*}type="radio" {*
					*}name="{$field->getPrefixedId()}" {*
					*}value="{$className}"{*
					*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item=class glue=' '}{$class}{/implode}"{/if}{*
					*}{if $field->getValue() === $className || ($className === 'custom' && !$field->getCustomClassName()|empty)} checked{/if}{*
					*}{if $field->isImmutable()} disabled{/if}{*
					*}{foreach from=$field->getFieldAttributes() key=attributeName item=attributeValue} {$attributeName}="{$attributeValue}"{/foreach}{*
					*}>
				{if $className == 'custom'}
					<span class="classNameSelection__span">
						<input type="text" id="{$field->getPrefixedId()}Custom" {*
							*}name="{$field->getPrefixedId()}customCssClassName" {*
						    *}value="{$field->getCustomClassName()}" {*
							*}{if $field->isImmutable()} disabled{/if}{*
							*}class="long classNameSelection__custom__input" {*
							*}pattern="{$field->getPattern()}"{*
						*}>
					</span>
				{else}
					{unsafe:$field->renderVisualTemplate($className, $label)}
				{/if}
			</label>
		</li>
	{/foreach}
</ul>
