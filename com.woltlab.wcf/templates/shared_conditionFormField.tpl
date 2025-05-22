<select id="{$field->getPrefixedId()}_condition" name="{$field->getPrefixedId()}_condition"{if $field->isImmutable()} disabled{/if}>
    {foreach from=$field->getConditions() key=condition item=label}
		<option value="{$condition}"{if $field->getCondition() === $condition} selected{/if}>{lang}{$label}{/lang}</option>
    {/foreach}
</select>
