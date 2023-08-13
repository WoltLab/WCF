{if $categoryName|isset && $categoryName|str_ends_with:'.*'}
	{assign var='__categoryNameStart' value=$categoryName|mb_substr:0:-1}
{/if}
<script data-relocate="true">
	$(function() {
		{if $aclValues[$objectTypeID]|isset}
			var initialPermissions = {
				returnValues: {
					options: {
						{foreach from=$aclValues[$objectTypeID][options] key='__optionID' item='__optionData'}
							{assign var='__optionCategoryName' value=$__optionData[categoryName]}
							
							{if !$categoryName|isset || ($__categoryNameStart|isset && $__optionCategoryName|str_starts_with:$__categoryNameStart) || (!$__categoryNameStart|isset && $__optionCategoryName == $categoryName)}
								{@$__optionID}: {
									categoryName: '{@$__optionData[categoryName]|encodeJS}',
									label: '{@$__optionData[label]|encodeJS}',
									optionName: '{@$__optionData[optionName]|encodeJS}'
								},
							{/if}
						{/foreach}
					},
					categories: {
						{implode from=$aclValues[$objectTypeID][categories] key='__category' item='__categoryName'}
							'{@$__category|encodeJS}': '{@$__categoryName|encodeJS}'
						{/implode}
					},
					user: {
						{if $aclValues[$objectTypeID][user]|isset}
							option: {
								{foreach from=$aclValues[$objectTypeID][user][option] key='__userID' item='__optionData'}
									{hascontent}
										{@$__userID}: {
											{content}
												{foreach from=$__optionData key='__optionID' item='__optionValue'}
													{assign var='__optionCategoryName' value=$aclValues[$objectTypeID][options][$__optionID][categoryName]}
													
													{if !$categoryName|isset || ($__categoryNameStart|isset && $__optionCategoryName|str_starts_with:$__categoryNameStart) || (!$__categoryNameStart|isset && $__optionCategoryName == $categoryName)}
														{@$__optionID}: {@$__optionValue},
													{/if}
												{/foreach}
											{/content}
										},
									{/hascontent}
								{/foreach}
							},
							label: { }
						{/if}
					},
					group: {
						{if $aclValues[$objectTypeID][group]|isset}
							option: {
								{foreach from=$aclValues[$objectTypeID][group][option] key='__groupID' item='__optionData'}
									{hascontent}
										{@$__groupID}: {
											{content}
												{foreach from=$__optionData key='__optionID' item='__optionValue'}
													{assign var='__optionCategoryName' value=$aclValues[$objectTypeID][options][$__optionID][categoryName]}
													
													{if !$categoryName|isset || ($__categoryNameStart|isset && $__optionCategoryName|str_starts_with:$__categoryNameStart) || (!$__categoryNameStart|isset && $__optionCategoryName == $categoryName)}
														{@$__optionID}: {@$__optionValue},
													{/if}
												{/foreach}
											{/content}
										},
									{/hascontent}
								{/foreach}
							},
							label: { }
						{/if}
					}
				}
			};
			
			{if $aclValues[$objectTypeID][user]|isset}
				{foreach from=$aclValues[$objectTypeID][user][label] key='__userID' item='__label'}
					if (initialPermissions.returnValues.user.option[{@$__userID}]) {
						initialPermissions.returnValues.user.label[{@$__userID}] = '{@$__label|encodeJS}';
					}
				{/foreach}
			{/if}
			
			{if $aclValues[$objectTypeID][group]|isset}
				{foreach from=$aclValues[$objectTypeID][group][label] key='__groupID' item='__label'}
					if (initialPermissions.returnValues.group.option[{@$__groupID}]) {
						initialPermissions.returnValues.group.label[{@$__groupID}] = '{@$__label|encodeJS}';
					}
				{/foreach}
			{/if}
		{/if}
		
		var aclList = new {if $aclListClassName|isset}{@$aclListClassName}{else}WCF.ACL.List{/if}(
			$('#{@$containerID}'),
			{@$objectTypeID},
			{if $categoryName|isset}'{@$categoryName}'{else}null{/if},
			{if $objectID|isset}{@$objectID}{else}0{/if},
			{if !$includeUserGroups|isset || $includeUserGroups}true{else}false{/if},
			{if $aclValues[$objectTypeID]|isset}initialPermissions{else}undefined{/if},
			{if $aclValuesFieldName|isset}'{@$aclValuesFieldName}'{else}undefined{/if}
		);
		
		{if !$aclFormBuilderMode|empty}
			require(['WoltLabSuite/Core/Form/Builder/Manager'], function(FormBuilderManager) {
				FormBuilderManager.getField(
					'{@$field->getDocument()->getId()|encodeJS}',
					'{@$field->getPrefixedId()|encodeJS}'
				).setAclList(aclList);
			});
		{/if}
	});
</script>
