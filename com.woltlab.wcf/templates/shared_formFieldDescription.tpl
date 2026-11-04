{if $field->getDescription() !== null}
	<div id="{$field->getPrefixedId()}Description" class="formField__description">{unsafe:$field->getDescription()}</div>
{/if}
