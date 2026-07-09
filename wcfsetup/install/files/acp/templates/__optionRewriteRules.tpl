{if $category->categoryName === 'general'}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Acp/Ui/Option/RewriteGenerator'], function (AcpUiOptionRewriteGenerator) {
			{jsphrase name='wcf.acp.rewrite'}
			{jsphrase name='wcf.acp.rewrite.description'}
			{jsphrase name='wcf.acp.rewrite.generate'}

			AcpUiOptionRewriteGenerator.init();
		});
	</script>
{/if}
