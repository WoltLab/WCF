{if $field->getDocument()->isAjax() && !$javaScriptDataHandlerModule|empty}
	<script data-relocate="true">
		require([
			'{$javaScriptDataHandlerModule}{if $field|is_subclass_of:'wcf\system\form\builder\field\II18nFormField' && $field->isI18n()}I18n{/if}',
			'WoltLabSuite/Core/Form/Builder/Manager'
		], function(
			FormBuilderField,
			FormBuilderManager
		) {
			FormBuilderManager.registerField(
				'{@$field->getDocument()->getId()}',
				new FormBuilderField('{@$field->getPrefixedId()}')
			);
		});
	</script>
{/if}
