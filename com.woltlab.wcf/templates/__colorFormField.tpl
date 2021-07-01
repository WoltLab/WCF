{if $field->isImmutable()}
    <span class="colorPickerButton">
        <span{if $field->getValue()} style="background-color: {$field->getValue()}"{/if}></span>
    </span>
{else}
    <a href="#" class="colorPickerButton jsTooltip" id="{@$field->getPrefixedId()}_colorPickerButton" title="{lang}wcf.style.colorPicker.button.changeColor{/lang}" data-store="{@$field->getPrefixedId()}">
        <span{if $field->getValue()} style="background-color: {$field->getValue()}"{/if}></span>
    </a>
    <input type="hidden" {*
        *}id="{@$field->getPrefixedId()}" {*
        *}name="{@$field->getPrefixedId()}" {*
        *}value="{$field->getValue()}"{*
    *}>

    <script data-relocate="true">
        require(['WoltLabSuite/Core/Language', 'WoltLabSuite/Core/Ui/Color/Picker'], (Language, UiColorPicker) => {
            Language.addObject({
                'wcf.style.colorPicker': '{jslang}wcf.style.colorPicker{/jslang}',
                'wcf.style.colorPicker.alpha': '{jslang}wcf.style.colorPicker.alpha{/jslang}',
                'wcf.style.colorPicker.button.apply': '{jslang}wcf.style.colorPicker.button.apply{/jslang}',
                'wcf.style.colorPicker.color': '{jslang}wcf.style.colorPicker.color{/jslang}',
                'wcf.style.colorPicker.current': '{jslang}wcf.style.colorPicker.current{/jslang}',
                'wcf.style.colorPicker.error.invalidColor': '{jslang}wcf.style.colorPicker.error.invalidColor{/jslang}',
                'wcf.style.colorPicker.hexAlpha': '{jslang}wcf.style.colorPicker.hexAlpha{/jslang}',
                'wcf.style.colorPicker.new': '{jslang}wcf.style.colorPicker.new{/jslang}',
            });

            UiColorPicker.fromSelector("#{@$field->getPrefixedId()}_colorPickerButton");
        });
    </script>
{/if}
