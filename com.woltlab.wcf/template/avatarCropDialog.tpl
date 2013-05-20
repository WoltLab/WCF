<dl class="wide">
	<dt>{lang}wcf.user.avatar.type.custom.crop{/lang}</dt>
	<dd>
		<div id="userAvatarCropSelection">
			{@$avatar->getImageTag()}
			<div id="userAvatarCropOverlay"></div>
			<div id="userAvatarCropOverlaySelection"></div>
		</div>
		
		<small>{lang}wcf.user.avatar.type.custom.crop.description{/lang}</small>
	</dd>
</dl>

<div class="formSubmit">
	<button data-type="save" class="buttonPrimary">{lang}wcf.global.button.save{/lang}</button>
</div>
