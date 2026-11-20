{foreach from=$statItems item='statItem'}
	{if $statItem->link}
		<a href="{$statItem->link}" class="{$className}{if $statItem->cssClassName} {$statItem->cssClassName}{/if}" {unsafe:$statItem->attributes}>
			<span class="{$className}__key">{unsafe:$statItem->title}</span>
			<span class="{$className}__value">{unsafe:$statItem->value}</span>
		</a>
	{elseif $statItem->isButton}
		<button type="button" class="{$className}{if $statItem->cssClassName} {$statItem->cssClassName}{/if}" {unsafe:$statItem->attributes}>
			<span class="{$className}__key">{unsafe:$statItem->title}</span>
			<span class="{$className}__value">{unsafe:$statItem->value}</span>
		</button>
	{else}
		<div class="{$className}{if $statItem->cssClassName} {$statItem->cssClassName}{/if}">
			<span class="{$className}__key">{unsafe:$statItem->title}</span>
			<span class="{$className}__value">{unsafe:$statItem->value}</span>
		</div>
	{/if}
{/foreach}
