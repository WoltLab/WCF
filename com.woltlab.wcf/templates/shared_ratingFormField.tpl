<div class="ratingField">
	{if $field->isNullable()}
		<label class="ratingField__reset jsTooltip" title="{lang}wcf.form.field.rating.removeRating{/lang}">
			<input {*
				*}type="radio" {*
				*}name="{$field->getPrefixedId()}" {*
				*}value="" {*
				*}class="ratingField__input visuallyHidden"{*
				*}{if $field->getValue() === null} checked{/if}{*
				*}{if $field->isImmutable()} disabled{/if}{*
			*}>
			{icon size=24 name='xmark'}
			<span class="visuallyHidden">{lang}wcf.form.field.rating.removeRating{/lang}</span>
		</label>
	{/if}
	<span class="ratingField__stars">
		{foreach from=$field->getRatings() item=rating}
			<label class="ratingField__star">
				<input {*
					*}type="radio" {*
					*}name="{$field->getPrefixedId()}" {*
					*}value="{$rating}" {*
					*}class="ratingField__input visuallyHidden"{*
					*}{if $field->getValue() !== null && $field->getValue() == $rating} checked{/if}{*
					*}{if $field->isImmutable()} disabled{/if}{*
					*}{if $field->isRequired()} required{/if}{*
				*}>
				<span class="ratingField__icon ratingField__icon--empty">{icon size=24 name='star'}</span>
				<span class="ratingField__icon ratingField__icon--full">{icon size=24 name='star' type='solid'}</span>
				<span class="visuallyHidden">{lang maximumRating=$field->getMaximum()}wcf.form.field.rating.ratingTitle{/lang}</span>
			</label>
		{/foreach}
	</span>
</div>
