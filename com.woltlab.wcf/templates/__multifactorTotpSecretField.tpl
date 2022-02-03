<div class="totpSecretContainer">
	<input type="hidden" name="{@$field->getPrefixedId()}" value="{$field->getSignedValue()}">
	<canvas></canvas><br>
	<kbd {*
	*}class="totpSecret" {*
	*}data-issuer="{PAGE_TITLE|phrase}" {*
	*}data-accountname="{$__wcf->user->username}"{*
	*}>{$field->getEncodedValue()}</kbd>
	
	<script>
	(function (script) {
		require(['WoltLabSuite/Core/Ui/User/Multifactor/Totp/Qr', 'Language'], (Qr, Language) => {
			Language.addObject({
				'wcf.user.security.multifactor.com.woltlab.wcf.multifactor.totp.link': '{jslang}wcf.user.security.multifactor.com.woltlab.wcf.multifactor.totp.link{/jslang}',
			});

			Qr.render(script.closest(".totpSecretContainer"));
		});
	})(document.currentScript);
	</script>
</div>
