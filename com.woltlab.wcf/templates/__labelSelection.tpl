{foreach from=$labelGroups item=labelGroup}
	{if $labelGroup|count}
		<dt>##<label>{$labelGroup->getTitle()}</label></dt>
		<dd>
			<ul class="labelList jsOnly">
				<li class="dropdown labelChooser" id="labelGroup{@$labelGroup->groupID}" data-group-id="{@$labelGroup->groupID}">
					<div class="dropdownToggle" data-toggle="labelGroup{@$labelGroup->groupID}"><span class="badge label">{lang}wcf.label.none{/lang}</span></div>
					<div class="dropdownMenu">
						<ul class="scrollableDropdownMenu">
							{foreach from=$labelGroup item=label}
								<li data-label-id="{@$label->labelID}"><span>{@$label->render()}</span></li>
							{/foreach}
						</ul>
					</div>
				</li>
			</ul>
			{if $noLabelSelectionNoScript|empty}
				<noscript>
					{foreach from=$labelGroups item=labelGroup}
						<select name="labelIDs[{@$labelGroup->groupID}]">
							<option value="0">{lang}wcf.label.none{/lang}</option>
							<option value="-1">{lang}wcf.label.withoutSelection{/lang}</option>
							{foreach from=$labelGroup item=label}
								<option value="{@$label->labelID}"{if $labelIDs[$labelGroup->groupID]|isset && $labelIDs[$labelGroup->groupID] == $label->labelID} selected{/if}>{$label->getTitle()}</option>
							{/foreach}
						</select>
					{/foreach}
				</noscript>
			{/if}
		</dd>
	{/if}
{/foreach}
