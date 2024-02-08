<button
	type="button"
	class="jsObjectAction jsTooltip"
	title="{lang}wcf.global.button.{if !$object->isDisabled}disable{else}enable{/if}{/lang}"
	data-object-action="toggle"
>
	{if $object->isDisabled}
		{icon name='square-check'}
	{else}
		{icon name='square'}
	{/if}
</button>
