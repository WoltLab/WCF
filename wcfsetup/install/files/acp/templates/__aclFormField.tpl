{if $includeAclJavaScript}
	{include file='aclPermissions'}
{/if}

{include file='aclPermissionJavaScript' containerID=$field->getPrefixedId()|concat:'Container' categoryName=$field->getCategoryName() objectID=$field->getObjectID() objectTypeID=$field->getObjectType()->objectTypeID aclFormBuilderMode=true aclValuesFieldName=$field->getPrefixedId()}
