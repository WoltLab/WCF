{if $category->categoryName === 'general'}
	<div id="dialogRewriteTest" style="display: none">
		<div id="dialogRewriteTestRunning" class="box24">
			{icon size=24 name='spinner'}
			<p>{lang}wcf.acp.option.url_omit_index_php.test.running{/lang}</p>
		</div>
		<div id="dialogRewriteTestSuccess" class="box24" style="display: none">
			{icon size=24 name='check'}
			<p>{lang}wcf.acp.option.url_omit_index_php.test.success{/lang}</p>
		</div>
		
		<div id="dialogRewriteTestFailure" style="display: none">
			<div class="box24">
				{icon size=24 name='triangle-exclamation'}
				<p>{lang}wcf.acp.option.url_omit_index_php.test.failure{/lang}</p>
			</div>
			<p>{lang}wcf.acp.option.url_omit_index_php.test.failure.description{/lang}</p>
			<p style="margin-top: 20px">{lang}wcf.acp.option.url_omit_index_php.test.status{/lang}</p>
			<ul id="dialogRewriteTestFailureResults"></ul>
		</div>
		
		<div class="formSubmit">
			<button type="button" id="rewriteTestStart" class="button buttonPrimary">{lang}wcf.acp.option.url_omit_index_php.button.runTestAgain{/lang}</button>
		</div>
	</div>
	<script data-relocate="true">
		require(['Dictionary', 'WoltLabSuite/Core/Acp/Ui/Option/RewriteTest'], function (Dictionary, AcpUiOptionRewriteTest) {
			{jsphrase name='wcf.acp.option.url_omit_index_php'}
			{jsphrase name='wcf.acp.option.url_omit_index_php.test.status.failure'}
			{jsphrase name='wcf.acp.option.url_omit_index_php.test.status.success'}
			
			const apps = new Map(Object.entries({
				{* this bypasses the route system to force rewritten urls *}
				{implode from=$rewriteTestApplications item=$rewriteTestApplication}'{unsafe:$rewriteTestApplication->getPackage()|encodeJS}': '{$__wcf->getPath($rewriteTestApplication->getAbbreviation())}core-rewrite-test/'{/implode}
			}));
			
			AcpUiOptionRewriteTest.init(apps);
		});
	</script>
{/if}
