{if $category->categoryName === 'general'}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Acp/Ui/Option/EmailSmtpTest'], function (AcpUiOptionEmailSmtpTest) {
			{jsphrase name='wcf.acp.email.smtp.test'}
			{jsphrase name='wcf.acp.email.smtp.test.description'}
			{jsphrase name='wcf.acp.email.smtp.test.error.empty.host'}
			{jsphrase name='wcf.acp.email.smtp.test.error.empty.password'}
			{jsphrase name='wcf.acp.email.smtp.test.error.empty.user'}
			{jsphrase name='wcf.acp.email.smtp.test.run'}
			{jsphrase name='wcf.acp.email.smtp.test.run.success'}
			
			AcpUiOptionEmailSmtpTest.init();
		});
	</script>
{/if}
