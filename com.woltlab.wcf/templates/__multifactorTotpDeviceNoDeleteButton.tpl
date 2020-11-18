<button type="button" id="{@$button->getPrefixedId()}" class="jsStaticDialog" data-dialog-id="{@$button->getPrefixedId()}DeletionInfo">{$button->getLabel()}</button>
<div style="display: none;" id="{@$button->getPrefixedId()}DeletionInfo" data-title="{lang}wcf.user.security.multifactor.totp.lastDevice.title{/lang}">
	{lang deviceName=$device[deviceName]}wcf.user.security.multifactor.totp.lastDevice{/lang}
</div>
