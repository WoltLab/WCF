{if $field->getDocument()->isAjax() && !$field->getJavaScriptDataHandlerModule()|empty}
	<script data-relocate="true">
		require([
			'tslib',
			'{$field->getJavaScriptDataHandlerModule()}',
			'WoltLabSuite/Core/Form/Builder/Manager'
		], function(
			tslib,
			FormBuilderField,
			FormBuilderManager
		) {
			FormBuilderField = tslib.__importDefault(FormBuilderField);

			FormBuilderManager.registerField(
				'{@$field->getDocument()->getId()|encodeJS}',
				new (FormBuilderField.default)('{@$field->getPrefixedId()|encodeJS}')
			);
		});
	</script>
{/if}
