{if $category->categoryName === 'general'}
	<div id="dialogRewriteTest" style="display: none">
		<div id="dialogRewriteTestRunning" class="box24">
			<span class="icon icon24 fa-spinner"></span>
			<p>{lang}wcf.acp.option.url_omit_index_php.test.running{/lang}</p>
		</div>
		<div id="dialogRewriteTestSuccess" class="box24" style="display: none">
			<span class="icon icon24 fa-check green"></span>
			<p>{lang}wcf.acp.option.url_omit_index_php.test.success{/lang}</p>
		</div>
		
		<div id="dialogRewriteTestFailure" style="display: none">
			<div class="box24">
				<span class="icon icon24 fa-times red"></span>
				<p>{lang}wcf.acp.option.url_omit_index_php.test.failure{/lang}</p>
			</div>
			<p>{lang}wcf.acp.option.url_omit_index_php.test.failure.description{/lang}</p>
		</div>
		
		<div class="formSubmit">
			<button id="rewriteTestStart" class="buttonPrimary">{lang}wcf.acp.option.url_omit_index_php.button.runTestAgain{/lang}</button>
		</div>
	</div>
	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Acp/Ui/Option/RewriteTest'], function (Language, AcpUiOptionRewriteTest) {
			Language.addObject({
				'wcf.acp.option.url_omit_index_php': '{lang}wcf.acp.option.url_omit_index_php{/lang}'
			});
			
			AcpUiOptionRewriteTest.init('{$__wcf->getPath()}core-rewrite-test/?uuidHash={'sha256'|hash:WCF_UUID}');
		});
	</script>
{/if}
