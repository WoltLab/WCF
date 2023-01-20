<ul class="ratingList jsOnly">
	{foreach from=$field->getRatings() item=rating}
		<li data-rating="{@$rating}">
			<button type="button" class="jsTooltip" title="{lang maximumRating=$field->getMaximum()}wcf.form.field.rating.ratingTitle{/lang}">
				{if $rating <= $field->getValue()}
					{icon size=24 name='star' type='solid'}
				{else}
					{icon size=24 name='star'}
				{/if}
			</button>
		</li>
	{/foreach}
	{if $field->isNullable()}
		<li class="ratingMetaButton" data-action="removeRating">
			<button type="button" class="jsTooltip" title="{lang}wcf.form.field.rating.removeRating{/lang}">
				{icon size=24 name='xmark'}
			</button>
		</li>
	{/if}
</ul>
<noscript>
	<select name="{$field->getPrefixedId()}" {if $field->isImmutable()} disabled{/if}>
		{foreach from=$field->getRatings() item=rating}
			<option value="{$rating}">{@$rating}</option>
		{/foreach}
	</select>
</noscript>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Controller/Rating'], function(FormBuilderFieldRating) {
		new FormBuilderFieldRating(
			'{$field->getPrefixedId()}',
			{if $field->getValue() !== null}{@$field->getValue()}{else}''{/if}
		);
	});
</script>
