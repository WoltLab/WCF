{if $category->categoryName === 'general'}
	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Acp/Ui/Option/EmailSmtpTest'], function (Language, AcpUiOptionEmailSmtpTest) {
			Language.addObject({
				'wcf.acp.email.smtp.test': '{jslang}wcf.acp.email.smtp.test{/jslang}',
				'wcf.acp.email.smtp.test.description': '{jslang}wcf.acp.email.smtp.test.description{/jslang}',
				'wcf.acp.email.smtp.test.error.empty.host': '{jslang}wcf.acp.email.smtp.test.error.empty.host{/jslang}',
				'wcf.acp.email.smtp.test.error.empty.password': '{jslang}wcf.acp.email.smtp.test.error.empty.password{/jslang}',
				'wcf.acp.email.smtp.test.error.empty.user': '{jslang}wcf.acp.email.smtp.test.error.empty.user{/jslang}',
				'wcf.acp.email.smtp.test.run': '{jslang}wcf.acp.email.smtp.test.run{/jslang}',
				'wcf.acp.email.smtp.test.run.success': '{jslang}wcf.acp.email.smtp.test.run.success{/jslang}'
			});
			
			AcpUiOptionEmailSmtpTest.init();
		});
	</script>
{/if}
