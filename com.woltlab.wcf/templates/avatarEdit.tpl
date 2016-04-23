{capture assign='pageTitle'}{lang}wcf.user.avatar.edit{/lang} - {lang}wcf.user.usercp{/lang}{/capture}

{capture assign='contentTitle'}{lang}wcf.user.avatar.edit{/lang}{/capture}

{include file='userMenuSidebar'}

{include file='header'}

{if $__wcf->user->disableAvatar}
	<p class="error">{lang}wcf.user.avatar.error.disabled{/lang}</p>
{/if}

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<form method="post" action="{link controller='AvatarEdit'}{/link}" id="avatarForm">
	<div class="section avatarEdit">
		<dl class="avatarType">
			<dt></dt>
			<dd>
				<label><input type="radio" name="avatarType" value="none" {if $avatarType == 'none'}checked="checked" {/if}/> {lang}wcf.user.avatar.type.none{/lang}</label>
				<small>{lang}wcf.user.avatar.type.none.description{/lang}</small>
			</dd>
		</dl>
		
		{if $__wcf->getSession()->getPermission('user.profile.avatar.canUploadAvatar')}
			<dl class="avatarType jsOnly{if $errorField == 'custom'} formError{/if}" id="avatarUpload">
				<dt>
					{if $avatarType == 'custom'}
						{if $__wcf->getUserProfileHandler()->getAvatar()->canCrop()}
							{@$__wcf->getUserProfileHandler()->getAvatar()->getCropImageTag(96)}
						{else}
							{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(96)}
						{/if}
					{else}
						<img src="{@$__wcf->getPath()}images/avatars/avatar-default.svg" alt="" class="userAvatarImage" style="width: 96px; height: 96px" />
					{/if}
				</dt>
				<dd>
					<label><input type="radio" name="avatarType" value="custom" {if $avatarType == 'custom'}checked="checked" {/if}/> {lang}wcf.user.avatar.type.custom{/lang}</label>
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
		
		{if MODULE_GRAVATAR}
			<dl class="avatarType{if $errorField == 'gravatar'} formError{/if}">
				<dt><img src="https://secure.gravatar.com/avatar/{@$__wcf->user->email|strtolower|md5}?s=96{if GRAVATAR_DEFAULT_TYPE != '404'}&amp;d={@GRAVATAR_DEFAULT_TYPE}{/if}" alt="" class="userAvatarImage icon96" /></dt>
				<dd>
					<label><input type="radio" name="avatarType" value="gravatar" {if $avatarType == 'gravatar'}checked="checked" {/if}/> {lang}wcf.user.avatar.type.gravatar{/lang}</label>
					{if $errorField == 'gravatar'}
						<small class="innerError">
							{if $errorType == 'notFound'}{lang}wcf.user.avatar.type.gravatar.error.notFound{/lang}{/if}
						</small>
					{/if}
					<small>{lang}wcf.user.avatar.type.gravatar.description{/lang}</small>
				</dd>
			</dl>
		{/if}
		
		{event name='avatarFields'}
	</div>
		
	{event name='sections'}
	
	{if !$__wcf->user->disableAvatar}
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	{/if}
</form>

{if $__wcf->getSession()->getPermission('user.profile.avatar.canUploadAvatar')}
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.user.avatar.type.custom.crop': '{lang}wcf.user.avatar.type.custom.crop{/lang}',
				'wcf.user.avatar.upload.error.invalidExtension': '{lang}wcf.user.avatar.upload.error.invalidExtension{/lang}',
				'wcf.user.avatar.upload.error.tooSmall': '{lang}wcf.user.avatar.upload.error.tooSmall{/lang}',
				'wcf.user.avatar.upload.error.tooLarge': '{lang}wcf.user.avatar.upload.error.tooLarge{/lang}',
				'wcf.user.avatar.upload.error.uploadFailed': '{lang}wcf.user.avatar.upload.error.uploadFailed{/lang}',
				'wcf.user.avatar.upload.error.badImage': '{lang}wcf.user.avatar.upload.error.badImage{/lang}',
				'wcf.user.avatar.upload.success': '{lang}wcf.user.avatar.upload.success{/lang}'
			});
			
			{if !$__wcf->user->disableAvatar}
				{if $__wcf->getUserProfileHandler()->getAvatar()->canCrop()}
					new WCF.User.Avatar.Upload(0, new WCF.User.Avatar.Crop({@$__wcf->getUserProfileHandler()->getAvatar()->avatarID}));
				{else}
					new WCF.User.Avatar.Upload();
				{/if}
			{/if}
		});
		//]]>
	</script>
{/if}

{include file='footer'}
