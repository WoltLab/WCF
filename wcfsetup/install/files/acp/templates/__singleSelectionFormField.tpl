{include file='__formFieldHeader'}

<select id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}">
	{htmlOptions options=$field->getOptions() selected=$field->getValue() disableEncoding=true}
</select>

{include file='__formFieldFooter'}
