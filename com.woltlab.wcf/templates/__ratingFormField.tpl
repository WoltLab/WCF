<ul class="ratingList jsOnly">
	{foreach from=$field->getRatings() item=rating}
		<li data-rating="{@$rating}"><span class="icon icon24 {if $rating <= $field->getValue()}{implode from=$field->getActiveCssClasses() item=cssClass glue=' '}{@$cssClass}{/implode}{else}{implode from=$field->getDefaultCssClasses() item=cssClass glue=' '}{@$cssClass}{/implode}{/if} pointer jsTooltip" title="{lang maximumRating=$field->getMaximum()}wcf.form.field.rating.ratingTitle{/lang}"></span></li>
	{/foreach}
	{if $field->isNullable()}
		<li class="ratingMetaButton" data-action="removeRating"><span class="icon icon24 fa-times pointer jsTooltip" title="{lang}wcf.form.field.rating.removeRating{/lang}"></span></li>
	{/if}
</ul>
<noscript>
	<select name="{@$field->getPrefixedId()}" {if $field->isImmutable()} disabled{/if}>
		{foreach from=$field->getRatings() item=rating}
			<option value="{@$rating}">{@$rating}</option>
		{/foreach}
	</select>
</noscript>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Controller/Rating'], function(FormBuilderFieldRating) {
		new FormBuilderFieldRating(
			'{@$field->getPrefixedId()}',
			{if $field->getValue() !== null}{@$field->getValue()}{else}''{/if},
			[ {implode from=$field->getActiveCssClasses() item=cssClass}'{@$cssClass}'{/implode} ],
			[ {implode from=$field->getDefaultCssClasses() item=cssClass}'{@$cssClass}'{/implode} ]
		);
	});
</script>
