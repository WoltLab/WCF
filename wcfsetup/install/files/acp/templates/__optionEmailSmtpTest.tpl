{if $category->categoryName === 'general'}
	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Acp/Ui/Option/EmailSmtpTest'], function (Language, AcpUiOptionEmailSmtpTest) {
			Language.addObject({
				'wcf.acp.email.smtp.test': '{lang}wcf.acp.email.smtp.test{/lang}',
				'wcf.acp.email.smtp.test.description': '{lang}wcf.acp.email.smtp.test.description{/lang}',
				'wcf.acp.email.smtp.test.error.empty.host': '{lang}wcf.acp.email.smtp.test.error.empty.host{/lang}',
				'wcf.acp.email.smtp.test.error.empty.password': '{lang}wcf.acp.email.smtp.test.error.empty.password{/lang}',
				'wcf.acp.email.smtp.test.error.empty.user': '{lang}wcf.acp.email.smtp.test.error.empty.user{/lang}',
				'wcf.acp.email.smtp.test.run': '{lang}wcf.acp.email.smtp.test.run{/lang}',
				'wcf.acp.email.smtp.test.run.success': '{lang}wcf.acp.email.smtp.test.run.success{/lang}'
			});
			
			AcpUiOptionEmailSmtpTest.init();
		});
	</script>
{/if}
