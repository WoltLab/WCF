{include file='header' pageTitle='wcf.acp.user.'|concat:$action}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.{@$action}{/lang}</h1>
	{if $action == 'edit'}<p>{$user->username}</p>{/if}
</header>

{include file='formError'}

{if $userID|isset && $__wcf->user->userID == $userID}
	<p class="warning">{lang}wcf.acp.user.edit.warning.selfEdit{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{@$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='UserList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.user.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='UserAdd'}{/link}{else}{link controller='UserEdit' id=$userID}{/link}{/if}">
	<div class="tabMenuContainer">
		<nav class="tabMenu">
			<ul>
				<li><a href="{@$__wcf->getAnchor('__essentials')}">{lang}wcf.global.form.data{/lang}</a></li>
				
				{foreach from=$optionTree item=categoryLevel1}
					<li><a href="{@$__wcf->getAnchor($categoryLevel1[object]->categoryName)}">{lang}wcf.user.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</a></li>
				{/foreach}
				
				{if MODULE_USER_SIGNATURE}
					<li><a href="{@$__wcf->getAnchor('signatureManagement')}">{lang}wcf.user.signature{/lang}</a></li>
				{/if}

				{if $action == 'edit'}
					<li><a href="{@$__wcf->getAnchor('avatarForm')}">{lang}wcf.user.avatar{/lang}</a></li>
				{/if}
				
				{event name='tabMenuTabs'}
			</ul>
		</nav>
		
		<div id="__essentials" class="container containerPadding tabMenuContent hidden">
			<fieldset>
				<legend>{lang}wcf.acp.user.general{/lang}</legend>
				
				<dl{if $errorType.username|isset} class="formError"{/if}>
					<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
					<dd>
						<input type="text" id="username" name="username" value="{$username}" pattern="^[^,\n]+$" autofocus="autofocus" class="medium" />
						{if $errorType.username|isset}
							<small class="innerError">
								{if $errorType.username == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.user.username.error.{@$errorType.username}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				{if $availableGroups|count}
					<dl>
						<dt>
							<label>{lang}wcf.acp.user.groups{/lang}</label>
						</dt>
						<dd>
							{htmlCheckboxes options=$availableGroups name=groupIDs selected=$groupIDs}
						</dd>
					</dl>
				{/if}
				
				{event name='generalFields'}
			</fieldset>
			
			{if $action == 'add' || $__wcf->session->getPermission('admin.user.canEditMailAddress')}
				<fieldset>
					<legend>{lang}wcf.user.email{/lang}</legend>
					
					<dl{if $errorType.email|isset} class="formError"{/if}>
						<dt><label for="email">{lang}wcf.user.email{/lang}</label></dt>
						<dd>
							<input type="email" id="email" name="email" value="{$email}" required="required" class="medium" />
							{if $errorType.email|isset}
								<small class="innerError">
									{if $errorType.email == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.user.email.error.{@$errorType.email}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<dl{if $errorType.confirmEmail|isset} class="formError"{/if}>
						<dt><label for="confirmEmail">{lang}wcf.user.confirmEmail{/lang}</label></dt>
						<dd>
							<input type="email" id="confirmEmail" name="confirmEmail" value="{$confirmEmail}" required="required" class="medium" />
							{if $errorType.confirmEmail|isset}
								<small class="innerError">
									{lang}wcf.user.confirmEmail.error.{@$errorType.confirmEmail}{/lang}
								</small>
							{/if}
						</dd>
					</dl>
					
					{event name='emailFields'}
				</fieldset>
			{/if}
			
			{if $action == 'add' || $__wcf->session->getPermission('admin.user.canEditPassword')}
				<fieldset>
					<legend>{lang}wcf.user.password{/lang}</legend>
					
					<dl{if $errorType.password|isset} class="formError"{/if}>
						<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
						<dd>
							<input type="password" id="password" name="password" value="{$password}"{if $action == 'add'} required="required"{/if} class="medium" />
							{if $errorType.password|isset}
								<small class="innerError">
									{if $errorType.password == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.user.password.error.{@$errorType.password}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<dl{if $errorType.confirmPassword|isset} class="formError"{/if}>
						<dt><label for="confirmPassword">{lang}wcf.user.confirmPassword{/lang}</label></dt>
						<dd>
							<input type="password" id="confirmPassword" name="confirmPassword" value="{$confirmPassword}"{if $action == 'add'} required="required"{/if} class="medium" />
							{if $errorType.confirmPassword|isset}
								<small class="innerError">
									{lang}wcf.user.confirmPassword.error.{@$errorType.confirmPassword}{/lang}
								</small>
							{/if}
						</dd>
					</dl>
					
					{event name='passwordFields'}
				</fieldset>
			{/if}
			
			{if $action == 'edit' && $__wcf->session->getPermission('admin.user.canBanUser') && $__wcf->user->userID != $userID}
				<fieldset>
					<legend>{lang}wcf.acp.user.banUser{/lang}</legend>
					
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="banned" name="banned" value="1" {if $banned == 1}checked="checked" {/if}/> {lang}wcf.acp.user.banUser{/lang}</label>
							<small>{lang}wcf.acp.user.banUser.description{/lang}</small>	
						</dd>
					</dl>
					
					<dl>
						<dt><label for="banReason">{lang}wcf.acp.user.banReason{/lang}</label></dt>
						<dd>
							<textarea name="banReason" id="banReason" cols="40" rows="10">{$banReason}</textarea>
							<small>{lang}wcf.acp.user.banReason.description{/lang}</small>
						</dd>
					</dl>
					
					{event name='banFields'}
				</fieldset>
				
				<script data-relocate="true">
					//<![CDATA[
					$('#banned').change(function (event) {
						if ($('#banned').is(':checked')) {
							$('#banReason').attr('readonly', false);
						}
						else {
							$('#banReason').attr('readonly', true);
						}
					});
					$('#banned').change();
					//]]>
				</script>
			{/if}
			
			{event name='fieldsets'}
		</div>
		
		{foreach from=$optionTree item=categoryLevel1}
			<div id="{@$categoryLevel1[object]->categoryName}" class="container containerPadding tabMenuContent hidden">
				{foreach from=$categoryLevel1[categories] item=categoryLevel2}
					<fieldset>
						<legend>{lang}wcf.user.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</legend>
						
						{if $categoryLevel2[object]->categoryName == 'settings.general' && $availableLanguages|count > 1}
							<dl>
								<dt><label for="languageID">{lang}wcf.user.language{/lang}</label></dt>
								<dd>
									{htmlOptions options=$availableLanguages selected=$languageID name=languageID id=languageID disableEncoding=true}
								</dd>
							</dl>
							
							{if $availableContentLanguages|count > 1}
								<dl>
									<dt>
										<label>{lang}wcf.user.visibleLanguages{/lang}</label>
									</dt>
									<dd>
										{foreach from=$availableContentLanguages key=availableLanguageID item=availableLanguage}
											<label><input type="checkbox" name="visibleLanguages[]" value="{@$availableLanguageID}"{if $availableLanguageID|in_array:$visibleLanguages} checked="checked"{/if} /> {@$availableLanguage}</label>
										{/foreach}
									</dd>
								</dl>
							{/if}
						{/if}
						
						{if $categoryLevel2[object]->categoryName == 'profile.personal' && MODULE_USER_RANK}
							<dl>
								<dt><label for="userTitle">{lang}wcf.user.userTitle{/lang}</label></dt>
								<dd>
									<input type="text" id="userTitle" name="userTitle" value="{$userTitle}" class="long" maxlength="{@USER_TITLE_MAX_LENGTH}" />
									{if $errorType[userTitle]|isset}
										<small class="innerError">
											{lang}wcf.user.userTitle.error.{@$errorType[userTitle]}{/lang}
										</small>
									{/if}
									<small>{lang}wcf.user.userTitle.description{/lang}</small>
								</dd>
							</dl>
						{/if}
						
						{event name='categoryFields'}
						
						{include file='optionFieldList' options=$categoryLevel2[options] langPrefix='wcf.user.option.'}
						
						{if $categoryLevel2[categories]|count}
							{foreach from=$categoryLevel2[categories] item=categoryLevel3}
								{include file='optionFieldList' options=$categoryLevel3[options] langPrefix='wcf.user.option.'}
							{/foreach}
						{/if}
					</fieldset>
				{/foreach}
			</div>
		{/foreach}
		
		{if MODULE_USER_SIGNATURE}
			<div id="signatureManagement" class="container containerPadding tabMenuContent hidden">
				<fieldset>
					<legend>{lang}wcf.user.signature{/lang}</legend>
					
					<dl>
						<dt><label for="signature">{lang}wcf.user.signature{/lang}</label></dt>
						<dd>
							<textarea name="signature" id="signature" cols="40" rows="10">{$signature}</textarea>
						</dd>
					</dl>
					
					<dl>
						<dt>{lang}wcf.message.settings{/lang}</dt>
						<dd>
							<label><input id="signatureEnableSmilies" name="signatureEnableSmilies" type="checkbox" value="1"{if $signatureEnableSmilies} checked="checked"{/if} /> {lang}wcf.message.settings.enableSmilies{/lang}</label>
							<label><input id="signatureEnableBBCodes" name="signatureEnableBBCodes" type="checkbox" value="1"{if $signatureEnableBBCodes} checked="checked"{/if} /> {lang}wcf.message.settings.enableBBCodes{/lang}</label>
							<label><input id="signatureEnableHtml" name="signatureEnableHtml" type="checkbox" value="1"{if $signatureEnableHtml} checked="checked"{/if} /> {lang}wcf.message.settings.enableHtml{/lang}</label>
						</dd>
					</dl>
				</fieldset>
				
				<fieldset>
					<legend>{lang}wcf.acp.user.disableSignature{/lang}</legend>
					
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="disableSignature" name="disableSignature" value="1" {if $disableSignature == 1}checked="checked" {/if}/> {lang}wcf.acp.user.disableSignature{/lang}</label>
						</dd>
					</dl>
					
					<dl>
						<dt><label for="disableSignatureReason">{lang}wcf.acp.user.disableSignatureReason{/lang}</label></dt>
						<dd>
							<textarea name="disableSignatureReason" id="disableSignatureReason" cols="40" rows="10">{$disableSignatureReason}</textarea>
						</dd>
					</dl>
				</fieldset>
				
				<script data-relocate="true">
					//<![CDATA[
					$('#disableSignature').change(function (event) {
						if ($('#disableSignature').is(':checked')) {
							$('#disableSignatureReason').attr('readonly', false);
						}
						else {
							$('#disableSignatureReason').attr('readonly', true);
						}
					});
					$('#disableSignature').change();
					//]]>
				</script>
			</div>
		{/if}
		
		{if $action == 'edit'}
			<div id="avatarForm" class="container containerPadding tabMenuContent hidden">
				<fieldset>
					<legend>{lang}wcf.user.avatar{/lang}</legend>
					
					<dl>
						<dt></dt>
						<dd>
							<label><input type="radio" name="avatarType" value="none" {if $avatarType == 'none'}checked="checked" {/if}/> {lang}wcf.user.avatar.type.none{/lang}</label>
						</dd>
					</dl>
					
					<dl class="jsOnly{if $errorType[customAvatar]|isset} formError{/if}" id="avatarUpload">
						<dt class="framed">{if $avatarType == 'custom'}{@$userAvatar->getImageTag(96)}{else}<img src="{@$__wcf->getPath()}images/avatars/avatar-default.svg" alt="" class="icon96" />{/if}</dt>
						<dd>
							<label><input type="radio" name="avatarType" value="custom" {if $avatarType == 'custom'}checked="checked" {/if}/> {lang}wcf.user.avatar.type.custom{/lang}</label>
							
							{* placeholder for upload button: *}
							<div></div>
							
							{if $errorType[customAvatar]|isset}
								<small class="innerError">
									{if $errorType[customAvatar] == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					{if MODULE_GRAVATAR}
						<dl{if $errorType[gravatar]|isset} class="formError"{/if}>
							<dt class="framed"><img src="https://secure.gravatar.com/avatar/{@$user->email|strtolower|md5}?s=96" alt="" class="icon96" /></dt>
							<dd>
								<label><input type="radio" name="avatarType" value="gravatar" {if $avatarType == 'gravatar'}checked="checked" {/if}/> {lang}wcf.user.avatar.type.gravatar{/lang}</label>
								
								{if $errorType[gravatar]|isset}
									<small class="innerError">
										{if $errorType[gravatar] == 'notFound'}{lang}wcf.user.avatar.type.gravatar.error.notFound{/lang}{/if}
									</small>
								{/if}
							</dd>
						</dl>
					{/if}
				</fieldset>
				
				<fieldset>
					<legend>{lang}wcf.acp.user.disableAvatar{/lang}</legend>
					
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="disableAvatar" name="disableAvatar" value="1" {if $disableAvatar == 1}checked="checked" {/if}/> {lang}wcf.acp.user.disableAvatar{/lang}</label>
						</dd>
					</dl>
					
					<dl>
						<dt><label for="disableAvatarReason">{lang}wcf.acp.user.disableAvatarReason{/lang}</label></dt>
						<dd>
							<textarea name="disableAvatarReason" id="disableAvatarReason" cols="40" rows="10">{$disableAvatarReason}</textarea>
						</dd>
					</dl>
				</fieldset>
				
				<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Message{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
				<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.User{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
				<script data-relocate="true">
					//<![CDATA[
					$(function() {
						$('#disableAvatar').change(function (event) {
							if ($('#disableAvatar').is(':checked')) {
								$('#disableAvatarReason').attr('readonly', false);
							}
							else {
								$('#disableAvatarReason').attr('readonly', true);
							}
						});
						$('#disableAvatar').change();
						
						WCF.Language.addObject({
							'wcf.user.avatar.upload.error.invalidExtension': '{lang}wcf.user.avatar.upload.error.invalidExtension{/lang}',
							'wcf.user.avatar.upload.error.tooSmall': '{lang}wcf.user.avatar.upload.error.tooSmall{/lang}',
							'wcf.user.avatar.upload.error.tooLarge': '{lang}wcf.user.avatar.upload.error.tooLarge{/lang}',
							'wcf.user.avatar.upload.error.uploadFailed': '{lang}wcf.user.avatar.upload.error.uploadFailed{/lang}',
							'wcf.user.avatar.upload.error.badImage': '{lang}wcf.user.avatar.upload.error.badImage{/lang}',
							'wcf.user.avatar.upload.success': '{lang}wcf.user.avatar.upload.success{/lang}',
							'wcf.global.button.upload': '{lang}wcf.global.button.upload{/lang}'
						});
						
						new WCF.User.Avatar.Upload({@$user->userID});
					});
					//]]>
				</script>
			</div>
		{/if}

		{event name='tabMenuContent'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
