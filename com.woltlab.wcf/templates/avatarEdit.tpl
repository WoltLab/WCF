{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{if $__wcf->user->disableAvatar}
	<woltlab-core-notice type="error">{lang}wcf.user.avatar.error.disabled{/lang}</woltlab-core-notice>
{/if}

{include file='shared_formError'}

{if $success|isset}
	<woltlab-core-notice type="success">{lang}wcf.global.success.edit{/lang}</woltlab-core-notice>
{/if}

<form method="post" action="{link controller='AvatarEdit'}{/link}" id="avatarForm">
	<div role="group" aria-label="{lang}wcf.user.avatar{/lang}" class="section avatarEdit">
		<dl class="avatarType">
			<dt></dt>
			<dd>
				<label><input type="radio" name="avatarType" value="none" {if $avatarType == 'none'}checked {/if}> {lang}wcf.user.avatar.type.none{/lang}</label>
				<small>{lang}wcf.user.avatar.type.none.description{/lang}</small>
			</dd>
		</dl>
		
		{if $__wcf->getSession()->getPermission('user.profile.avatar.canUploadAvatar')}
			<dl class="avatarType jsOnly{if $errorField == 'custom'} formError{/if}" id="avatarUpload">
				<dt>
					{if $avatarType == 'custom'}
						{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(96)}
					{else}
						<img src="{@$__wcf->getPath()}images/avatars/avatar-default.svg" alt="" class="userAvatarImage" style="width: 96px; height: 96px">
					{/if}
				</dt>
				<dd>
					<label><input type="radio" name="avatarType" value="custom"{if $avatarType == 'custom'} checked{/if}> {lang}wcf.user.avatar.type.custom{/lang}</label>
					<small>{lang}wcf.user.avatar.type.custom.description{/lang}</small>
					
					{* placeholder for upload button: *}
					<div class="avatarUploadButtonContainer"></div>
					
					{if $errorField == 'custom'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
						</small>
					{/if}
				</dd>
			</dl>
		{/if}
		
		{event name='avatarFields'}
	</div>
		
	{event name='sections'}
	
	{if !$__wcf->user->disableAvatar}
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{csrfToken}
		</div>
	{/if}
</form>

{if $__wcf->getSession()->getPermission('user.profile.avatar.canUploadAvatar')}
	<script data-relocate="true">
		$(function() {
			WCF.Language.addObject({
				'wcf.user.avatar.upload.error.invalidExtension': '{jslang}wcf.user.avatar.upload.error.invalidExtension{/jslang}',
				'wcf.user.avatar.upload.error.tooSmall': '{jslang}wcf.user.avatar.upload.error.tooSmall{/jslang}',
				'wcf.user.avatar.upload.error.tooLarge': '{jslang}wcf.user.avatar.upload.error.tooLarge{/jslang}',
				'wcf.user.avatar.upload.error.uploadFailed': '{jslang}wcf.user.avatar.upload.error.uploadFailed{/jslang}',
				'wcf.user.avatar.upload.error.badImage': '{jslang}wcf.user.avatar.upload.error.badImage{/jslang}',
				'wcf.user.avatar.upload.success': '{jslang}wcf.user.avatar.upload.success{/jslang}'
			});
			
			{if !$__wcf->user->disableAvatar}
				new WCF.User.Avatar.Upload();
			{/if}
		});
	</script>
{/if}

{include file='footer'}
