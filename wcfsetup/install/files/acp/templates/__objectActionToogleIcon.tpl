<button type="button" class="jsObjectAction jsTooltip" {*
    *}title="{lang}wcf.global.button.{if !$object->isDisabled}disable{else}enable{/if}{/lang}" {*
    *}data-object-action="toggle"{*
*}>
    {if $object->isDisabled}
        {icon size=16 name='square-check'}
    {else}
        {icon size=16 name='square'}
    {/if}
</button>
