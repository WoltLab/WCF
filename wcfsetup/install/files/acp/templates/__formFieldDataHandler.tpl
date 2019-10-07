{if $field->getDocument()->isAjax() && !$field->getJavaScriptDataHandlerModule()|empty}
	<script data-relocate="true">
		require([
			'{$field->getJavaScriptDataHandlerModule()}',
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
