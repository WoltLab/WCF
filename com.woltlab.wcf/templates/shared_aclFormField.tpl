{if $includeAclJavaScript}
	{include file='aclPermissions'}
{/if}

{include file='shared_aclPermissionJavaScript' containerID=$field->getPrefixedId()|concat:'Container' categoryName=$field->getCategoryName() objectID=$field->getObjectID() objectTypeID=$field->getObjectType()->objectTypeID aclFormBuilderMode=$field->getDocument()->isAjax() aclValuesFieldName=$field->getPrefixedId()}
