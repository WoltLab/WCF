<script type="text/javascript">
	//<![CDATA[
	$(function() {
		{if $aclValues[$objectTypeID]|isset}
			var initialPermissions = { 
				returnValues: {
					options: {
						{implode from=$aclValues[$objectTypeID][options] key='__optionID' item='__optionData'}
							{@$__optionID}: {
								categoryName: '{@$__optionData[categoryName]|encodeJS}',
								label: '{@$__optionData[label]|encodeJS}',
								optionName: '{@$__optionData[optionName]|encodeJS}'
							}
						{/implode}
					},
					categories: {
						{implode from=$aclValues[$objectTypeID][categories] key='__category' item='__categoryName'}
							'{@$__category|encodeJS}': '{@$__categoryName|encodeJS}'
						{/implode}
					},
					user: {
						{if $aclValues[$objectTypeID][user]|isset}
							option: {
								{implode from=$aclValues[$objectTypeID][user][option] key='__userID' item='__optionData'}
									{@$__userID}: {
										{implode from=$__optionData key='__optionID' item='__optionValue'}
											{@$__optionID}: {@$__optionValue}
										{/implode}
									}
								{/implode}
							},
							label: {
								{implode from=$aclValues[$objectTypeID][user][label] key='__userID' item='__label'}
									{@$__userID}: '{@$__label|encodeJS}'
								{/implode}
							}
						{/if}
					},
					group: {
						{if $aclValues[$objectTypeID][group]|isset}
							option: {
								{implode from=$aclValues[$objectTypeID][group][option] key='__groupID' item='__optionData'}
									{@$__groupID}: {
										{implode from=$__optionData key='__optionID' item='__optionValue'}
											{@$__optionID}: {@$__optionValue}
										{/implode}
									}
								{/implode}
							},
							label: {
								{implode from=$aclValues[$objectTypeID][group][label] key='__groupID' item='__label'}
									{@$__groupID}: '{@$__label|encodeJS}'
								{/implode}
							}
						{/if}
					}
				}
			};
		{/if}
		new {if $aclListClassName|isset}{@$aclListClassName}{else}WCF.ACL.List{/if}($('#{@$containerID}'), {@$objectTypeID}, {if $categoryName|isset}'{@$categoryName}'{else}null{/if}, {if $objectID|isset}{@$objectID}{else}0{/if}, {if !$includeUserGroups|isset || $includeUserGroups}true{else}false{/if}{if $aclValues[$objectTypeID]|isset}, initialPermissions{/if});
	});
	//]]>
</script>