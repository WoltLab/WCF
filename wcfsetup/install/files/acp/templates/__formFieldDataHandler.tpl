{if $field->getDocument()->isAjax() && !$javaScriptDataHandlerModule|empty}
	<script data-relocate="true">
		require([
			'{$javaScriptDataHandlerModule}',
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
