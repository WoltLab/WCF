<div class="totpSecretContainer">
	<input type="hidden" name="{@$field->getPrefixedId()}" value="{$field->getSignedValue()}">
	<kbd {*
	*}class="totpSecret" {*
	*}data-issuer="{PAGE_TITLE}" {*
	*}data-accountname="{$__wcf->user->username}"{*
	*}>{$field->getEncodedValue()}</kbd>
	
	<script>
	(function (script) {
		require(['WoltLabSuite/Core/Ui/User/Multifactor/Totp/Qr'], (Qr) => Qr.render(script.closest(".totpSecretContainer")));
	})(document.currentScript);
	</script>
</div>
