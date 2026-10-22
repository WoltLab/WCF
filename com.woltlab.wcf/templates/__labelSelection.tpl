{foreach from=$labelGroups item=labelGroup}
	{if $labelGroup|count}
		{assign var='__labelPickerID' value='labelGroup'|concat:$labelGroup->groupID}
		{assign var='__labelPickerName' value='labelIDs['|concat:$labelGroup->groupID:']'}
		<dt><label for="{$__labelPickerID}">{$labelGroup->getTitle()}</label></dt>
		<dd>
			{if $labelIDs[$labelGroup->groupID]|isset}
				{unsafe:$labelGroup->renderPicker($__labelPickerName, $labelIDs[$labelGroup->groupID], false, true, $__labelPickerID)}
			{else}
				{unsafe:$labelGroup->renderPicker($__labelPickerName, 0, false, true, $__labelPickerID)}
			{/if}
		</dd>
	{/if}
{/foreach}
