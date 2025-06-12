{if $field->getDocument()->isAjax() && !$field->getJavaScriptDataHandlerModule()|empty}
	<script data-relocate="true">
		require([
			'tslib',
			'{unsafe:$field->getJavaScriptDataHandlerModule()|encodeJS}',
			'WoltLabSuite/Core/Form/Builder/Manager'
		], function(
			tslib,
			FormBuilderField,
			FormBuilderManager
		) {
			FormBuilderField = tslib.__importDefault(FormBuilderField);

			FormBuilderManager.registerField(
				'{unsafe:$field->getDocument()->getId()|encodeJS}',
				new (FormBuilderField.default)('{unsafe:$field->getPrefixedId()|encodeJS}')
			);
		});
	</script>
{/if}
