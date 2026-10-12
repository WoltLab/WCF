{capture assign='contentDescription'}{lang}wcf.user.register.existingUser{/lang}{/capture}

{include file='authFlowHeader'}

{if $isExternalAuthentication}
	<woltlab-core-notice type="info">{lang}wcf.user.3rdparty.{$__wcf->session->getVar('__3rdPartyProvider')}.register{/lang}</woltlab-core-notice>
{/if}

{unsafe:$form->getHtml()}

{include file='thirdPartySsoButtons'}

{if !$isExternalAuthentication}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Controller/User/Registration'], ({ setup }) => {
			{jsphrase name='wcf.user.username.error.invalid'}
			{jsphrase name='wcf.user.username.error.notUnique'}
			{jsphrase name='wcf.user.email.error.invalid'}
			{jsphrase name='wcf.user.email.error.notUnique'}

			setup(
				document.getElementById('username'),
				document.getElementById('email'),
				document.getElementById('password'),
				{
					minlength: {REGISTER_USERNAME_MIN_LENGTH},
					maxlength: {REGISTER_USERNAME_MAX_LENGTH}
				}
			);
		});
	</script>
{/if}

{include file='authFlowFooter'}
