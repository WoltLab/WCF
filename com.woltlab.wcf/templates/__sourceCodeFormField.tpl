<textarea id="{@$field->getPrefixedId()}" {*
    *}name="{@$field->getPrefixedId()}" {*
    *}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
    *}{if $field->isRequired()} required{/if}{*
    *}{if $field->isAutofocused()} autofocus{/if}{*
    *}{if $field->isImmutable()} disabled{/if}{*
    *}{if $field->getMinimumLength() !== null} minlength="{$field->getMinimumLength()}"{/if}{*
    *}{if $field->getMaximumLength() !== null} maxlength="{$field->getMaximumLength()}"{/if}{*
    *}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
*}>{$field->getValue()}</textarea>

{include file='codemirror' codemirrorMode=$field->getLanguage() codemirrorSelector='#'|concat:$field->getPrefixedId()}

<script data-relocate="true">
    (() => {
        document.getElementById('{@$field->getPrefixedId()}').parentNode.dir = 'ltr';
    })();
</script>
