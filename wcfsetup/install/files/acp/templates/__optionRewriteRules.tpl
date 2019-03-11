{if $category->categoryName === 'general'}
	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Acp/Ui/Option/RewriteGenerator'], function (Language, AcpUiOptionRewriteGenerator) {
			Language.addObject({
				'wcf.acp.rewrite': '{lang}wcf.acp.rewrite{/lang}',
				'wcf.acp.rewrite.description': '{lang}wcf.acp.rewrite.description{/lang}',
				'wcf.acp.rewrite.generate': '{lang}wcf.acp.rewrite.generate{/lang}'
			});

			AcpUiOptionRewriteGenerator.init();
		});
	</script>
{/if}
