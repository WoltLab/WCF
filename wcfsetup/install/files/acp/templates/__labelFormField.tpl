<ul class="labelList jsOnly" data-object-id="{@$field->getLabelGroup()->groupID}">
	<li class="dropdown labelChooser" data-group-id="{@$field->getLabelGroup()->groupID}">
		<div class="dropdownToggle" data-toggle="labelGroup{@$field->getLabelGroup()->groupID}">
			<span class="badge label">{lang}wcf.label.none{/lang}</span>
		</div>
		<div class="dropdownMenu">
			<ul class="scrollableDropdownMenu">
				{foreach from=$field->getLabelGroup() item=label}
					<li data-label-id="{@$label->labelID}">
						<span>{@$label->render()}</span>
					</li>
				{/foreach}
			</ul>
		</div>
	</li>
</ul>

<noscript>
	<select name="{$field->getPrefixedId()}[{@$field->getLabelGroup()->groupID}]">
		{foreach from=$field->getLabelGroup() item=label}
			<option value="{$label->labelID}">{$label->getTitle()}</option>
		{/foreach}
	</select>
</noscript>

<script data-relocate="true">
	require(['Dom/Util', 'Language', 'WoltLabSuite/Core/Form/Builder/Field/Controller/Label'], function(DomUtil, Language, FormBuilderFieldLabel) {
		Language.addObject({
			'wcf.label.none': '{jslang}wcf.label.none{/jslang}',
			'wcf.label.withoutSelection': '{jslang}wcf.label.withoutSelection{/jslang}'
		});
		
		new FormBuilderFieldLabel(
			'{@$field->getPrefixedId()|encodeJS}',
			{if $field->getValue()}'{$field->getValue()|encodeJS}'{else}null{/if},
			{
				forceSelection: {if $field->getLabelGroup()->forceSelection}true{else}false{/if}
			}
		);
	});
</script>
