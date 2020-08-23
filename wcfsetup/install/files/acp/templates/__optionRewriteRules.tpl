{if $category->categoryName === 'general'}
	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Acp/Ui/Option/RewriteGenerator'], function (Language, AcpUiOptionRewriteGenerator) {
			Language.addObject({
				'wcf.acp.rewrite': '{jslang}wcf.acp.rewrite{/jslang}',
				'wcf.acp.rewrite.description': '{jslang}wcf.acp.rewrite.description{/jslang}',
				'wcf.acp.rewrite.generate': '{jslang}wcf.acp.rewrite.generate{/jslang}'
			});

			AcpUiOptionRewriteGenerator.init();
		});
	</script>
{/if}
