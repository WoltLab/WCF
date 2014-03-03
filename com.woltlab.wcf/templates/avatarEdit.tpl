{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.avatar.edit{/lang} - {lang}wcf.user.usercp{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='userMenuSidebar'}

{include file='header' sidebarOrientation='left'}

<header class="boxHeadline">
	<h1>{lang}wcf.user.avatar.edit{/lang}</h1>
</header>

{include file='userNotice'}

{if $__wcf->user->disableAvatar}
	<p class="error">{lang}wcf.user.avatar.error.disabled{/lang}</p>
{/if}

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form method="post" action="{link controller='AvatarEdit'}{/link}" id="avatarForm">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.user.avatar{/lang}</legend>
				
			<dl>
				<dt></dt>
				<dd>
					<label><input type="radio" name="avatarType" value="none" {if $avatarType == 'none'}checked="checked" {/if}/> {lang}wcf.user.avatar.type.none{/lang}</label>
					<small>{lang}wcf.user.avatar.type.none.description{/lang}</small>
				</dd>
			</dl>
			
			{if $__wcf->getSession()->getPermission('user.profile.avatar.canUploadAvatar')}
				<dl class="jsOnly{if $errorField == 'custom'} formError{/if}" id="avatarUpload">
					<dt class="framed">
						{if $avatarType == 'custom'}
							{assign var='__customAvatar' value=$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(96)}
							{if $__wcf->getUserProfileHandler()->getAvatar()->canCrop()}
								{assign var='__customAvatar' value=$__customAvatar|substr:0:-2}
								{assign var='__customAvatarTitle' value='wcf.user.avatar.type.custom.crop'|language}
								{append var='__customAvatar' value='class="userAvatarCrop jsTooltip" title="'|concat:$__customAvatarTitle:'" />'}
							{/if}
							{@$__customAvatar}
						{else}
							<img src="{@$__wcf->getPath()}images/avatars/avatar-default.svg" alt="" class="icon96" />
						{/if}
					</dt>
					<dd>
						<label><input type="radio" name="avatarType" value="custom" {if $avatarType == 'custom'}checked="checked" {/if}/> {lang}wcf.user.avatar.type.custom{/lang}</label>
						<small>{lang}wcf.user.avatar.type.custom.description{/lang}</small>
						
						{* placeholder for upload button: *}
						<div></div>
						
						{if $errorField == 'custom'}
							<small class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
			{/if}
			
			{if MODULE_GRAVATAR}
				<dl{if $errorField == 'gravatar'} class="formError"{/if}>
					<dt class="framed"><img src="https://secure.gravatar.com/avatar/{@$__wcf->user->email|strtolower|md5}?s=96" alt="" class="icon96" /></dt>
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
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	{if !$__wcf->user->disableAvatar}
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	{/if}
</form>

{include file='footer'}

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
				'wcf.user.avatar.upload.success': '{lang}wcf.user.avatar.upload.success{/lang}',
				'wcf.global.button.upload': '{lang}wcf.global.button.upload{/lang}'
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

</body>
</html>
