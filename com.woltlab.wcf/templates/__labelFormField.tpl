{include file='__formFieldHeader'}

<ul class="labelList jsOnly" data-object-id="{@$field->getLabelGroup()->groupID}">
	<li class="dropdown labelChooser" data-group-id="{@$field->getLabelGroup()->groupID}">
		<div class="dropdownToggle" data-toggle="labelGroup{@$field->getLabelGroup()->groupID}">
			<span class="badge label">{lang}wcf.label.none{/lang}</span>
		</div>
		<div class="dropdownMenu">
			<ul class="scrollableDropdownMenu">
				{foreach from=$field->getLabelGroup() item=label}
					<li data-label-id="{@$label->labelID}">
						<span><span class="badge label{if $label->getClassNames()} {@$label->getClassNames()}{/if}">{$label->getTitle()}</span></span>
					</li>
				{/foreach}
			</ul>
		</div>
	</li>
</ul>

<noscript>
	<select name="{@$field->getPrefixedId()}[{@$field->getLabelGroup()->groupID}]">
		{foreach from=$field->getLabelGroup() item=label}
			<option value="{@$label->labelID}">{$label->getTitle()}</option>
		{/foreach}
	</select>
</noscript>

{js application='wcf' file='WCF.Label' bundle='WCF.Combined'}
<script data-relocate="true">
	require(['Dom/Util', 'Language', 'WoltLabSuite/Core/Form/Builder/Field/Controller/Label'], function(DomUtil, Language, FormBuilderFieldLabel) {
		Language.addObject({
			'wcf.label.none': '{lang}wcf.label.none{/lang}',
			'wcf.label.withoutSelection': '{lang}wcf.label.withoutSelection{/lang}'
		});
		
		new FormBuilderFieldLabel(
			'{@$field->getPrefixedId()}',
			{if $field->getValue()}{@$field->getValue()}{else}null{/if},
			{
				forceSelection: {if $field->getLabelGroup()->forceSelection}true{else}false{/if}
			}
		);
	});
</script>

{include file='__formFieldFooter'}
