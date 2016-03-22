<div id="settings_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}" class="settingsContent messageTabMenuContent">
	<dl class="wide">
		{if $__wcf->getSession()->getPermission($permissionCanUseBBCodes)}
			<dt></dt>
			<dd>
				<label><input id="preParse" name="preParse" type="checkbox" value="1"{if $preParse} checked="checked"{/if} /> {lang}wcf.message.settings.preParse{/lang}</label>
				<small>{lang}wcf.message.settings.preParse.description{/lang}</small>
			</dd>
		{/if}
		{if MODULE_SMILEY && $__wcf->getSession()->getPermission($permissionCanUseSmilies)}
			<dt></dt>
			<dd>
				<label><input id="enableSmilies" name="enableSmilies" type="checkbox" value="1"{if $enableSmilies} checked="checked"{/if} /> {lang}wcf.message.settings.enableSmilies{/lang}</label>
				<small>{lang}wcf.message.settings.enableSmilies.description{/lang}</small>
			</dd>
		{/if}
		{if $__wcf->getSession()->getPermission($permissionCanUseBBCodes)}
			<dt></dt>
			<dd>
				<label><input id="enableBBCodes" name="enableBBCodes" type="checkbox" value="1"{if $enableBBCodes} checked="checked"{/if} /> {lang}wcf.message.settings.enableBBCodes{/lang}</label>
				<small>{lang}wcf.message.settings.enableBBCodes.description{/lang}</small>
			</dd>
		{/if}
		{if $__wcf->getSession()->getPermission($permissionCanUseHtml)}
			<dt></dt>
			<dd>
				<label><input id="enableHtml" name="enableHtml" type="checkbox" value="1"{if $enableHtml} checked="checked"{/if} /> {lang}wcf.message.settings.enableHtml{/lang}</label>
				<small>{lang}wcf.message.settings.enableHtml.description{/lang}</small>
			</dd>
		{/if}
		{if MODULE_USER_SIGNATURE && !$showSignatureSetting|empty && $__wcf->user->userID}
			<dt></dt>
			<dd>
				<label><input id="showSignature" name="showSignature" type="checkbox" value="1"{if $showSignature} checked="checked"{/if} /> {lang}wcf.message.settings.showSignature{/lang}</label>
				<small>{lang}wcf.message.settings.showSignature.description{/lang}</small>
			</dd>
		{/if}
		
		{event name='settings'}
	</dl>
</div>
