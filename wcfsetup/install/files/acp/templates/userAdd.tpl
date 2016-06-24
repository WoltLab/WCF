{include file='header' pageTitle='wcf.acp.user.'|concat:$action}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.{@$action}{/lang}</h1>
		{if $action == 'edit'}<p class="contentHeaderDescription">{$user->username}</p>{/if}
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='UserList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.user.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $userID|isset && $__wcf->user->userID == $userID}
	<p class="warning">{lang}wcf.acp.user.edit.warning.selfEdit{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{@$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='UserAdd'}{/link}{else}{link controller='UserEdit' id=$userID}{/link}{/if}">
	<div class="section tabMenuContainer">
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
		
		<div id="__essentials" class="tabMenuContent hidden">
			<div class="section">
				<dl{if $errorType.username|isset} class="formError"{/if}>
					<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
					<dd>
						<input type="text" id="username" name="username" value="{$username}" pattern="^[^,\n]+$" autofocus class="medium">
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
			</div>
			
			{if $action == 'add' || $__wcf->session->getPermission('admin.user.canEditMailAddress')}
				<section class="section">
					<h2 class="sectionTitle">{lang}wcf.user.email{/lang}</h2>
					
					<dl{if $errorType.email|isset} class="formError"{/if}>
						<dt><label for="email">{lang}wcf.user.email{/lang}</label></dt>
						<dd>
							<input type="email" id="email" name="email" value="{$email}" class="medium">
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
							<input type="email" id="confirmEmail" name="confirmEmail" value="{$confirmEmail}" class="medium">
							{if $errorType.confirmEmail|isset}
								<small class="innerError">
									{lang}wcf.user.confirmEmail.error.{@$errorType.confirmEmail}{/lang}
								</small>
							{/if}
						</dd>
					</dl>
					
					{event name='emailFields'}
				</section>
			{/if}
			
			{if $action == 'add' || $__wcf->session->getPermission('admin.user.canEditPassword')}
				<section class="section">
					<h2 class="sectionTitle">{lang}wcf.user.password{/lang}</h2>
					
					<dl{if $errorType.password|isset} class="formError"{/if}>
						<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
						<dd>
							<input type="password" id="password" name="password" value="{$password}" class="medium" autocomplete="off">
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
							<input type="password" id="confirmPassword" name="confirmPassword" value="{$confirmPassword}" class="medium" autocomplete="off">
							{if $errorType.confirmPassword|isset}
								<small class="innerError">
									{lang}wcf.user.confirmPassword.error.{@$errorType.confirmPassword}{/lang}
								</small>
							{/if}
						</dd>
					</dl>
					
					{event name='passwordFields'}
				</section>
			{/if}
			
			{if $action == 'edit' && $__wcf->session->getPermission('admin.user.canBanUser') && $__wcf->user->userID != $userID}
				<section class="section">
					<h2 class="sectionTitle">{lang}wcf.acp.user.banUser{/lang}</h2>
					
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="banned" name="banned" value="1"{if $banned == 1} checked{/if}> {lang}wcf.acp.user.banUser{/lang}</label>
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
					
					<dl>
						<dt></dt>
						<dd><label><input type="checkbox" id="banNeverExpires" name="banNeverExpires" value="1"{if !$banExpires} checked{/if}> {lang}wcf.acp.user.ban.neverExpires{/lang}</label></dd>
					</dl>
					
					<dl id="banExpiresSetting">
						<dt><label for="banExpires">{lang}wcf.acp.user.ban.expires{/lang}</label></dt>
						<dd>
							<input type="date" name="banExpires" id="banExpires" min="{TIME_NOW|date:'Y-m-d'}" {if $banExpires} value="{$banExpires|date:'Y-m-d'}"{/if} class="medium" data-ignore-timezone="true">
							<small>{lang}wcf.acp.user.ban.expires.description{/lang}</small>
						</dd>
					</dl>
					
					{event name='banFields'}
				</section>
				
				<script data-relocate="true">
					//<![CDATA[
					$('#banned').change(function() {
						if ($('#banned').is(':checked')) {
							$('#banReason').attr('readonly', false);
							$('#banNeverExpires, #banExpires').enable();
							$('#banReason, #banNeverExpires, #banExpires').parents('dl').removeClass('disabled');
						}
						else {
							$('#banReason').attr('readonly', true);
							$('#banNeverExpires, #banExpires').disable();
							$('#banReason, #banNeverExpires, #banExpires').parents('dl').addClass('disabled');
						}
					});
					
					$('#banned').change();
					
					$('#banNeverExpires').change(function() {
						if ($('#banNeverExpires').is(':checked')) {
							$('#banExpiresSetting').hide();
						}
						else {
							$('#banExpiresSetting').show();
						}
					});
					
					$('#banNeverExpires').change();
					//]]>
				</script>
			{/if}
			
			{event name='sections'}
		</div>
		
		{foreach from=$optionTree item=categoryLevel1}
			<div id="{@$categoryLevel1[object]->categoryName}" class="tabMenuContent hidden">
				{foreach from=$categoryLevel1[categories] item=categoryLevel2}
					<section class="section">
						<h2 class="sectionTitle">{lang}wcf.user.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</h2>
						
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
											<label><input type="checkbox" name="visibleLanguages[]" value="{@$availableLanguageID}"{if $availableLanguageID|in_array:$visibleLanguages} checked{/if}> {@$availableLanguage}</label>
										{/foreach}
									</dd>
								</dl>
							{/if}
						{/if}
						
						{if $categoryLevel2[object]->categoryName == 'profile.personal' && MODULE_USER_RANK}
							<dl>
								<dt><label for="userTitle">{lang}wcf.user.userTitle{/lang}</label></dt>
								<dd>
									<input type="text" id="userTitle" name="userTitle" value="{$userTitle}" class="long" maxlength="{@USER_TITLE_MAX_LENGTH}">
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
					</section>
				{/foreach}
			</div>
		{/foreach}
		
		{if MODULE_USER_SIGNATURE}
			<div id="signatureManagement" class="tabMenuContent hidden">
				<section class="section">
					<h2 class="sectionTitle">{lang}wcf.user.signature{/lang}</h2>
					
					<dl>
						<dt><label for="signature">{lang}wcf.user.signature{/lang}</label></dt>
						<dd>
							<textarea name="signature" id="signature" cols="40" rows="10">{$signature}</textarea>
						</dd>
					</dl>
					
					<dl>
						<dt>{lang}wcf.message.settings{/lang}</dt>
						<dd>
							<label><input id="signatureEnableSmilies" name="signatureEnableSmilies" type="checkbox" value="1"{if $signatureEnableSmilies} checked{/if}> {lang}wcf.message.settings.enableSmilies{/lang}</label>
							<label><input id="signatureEnableBBCodes" name="signatureEnableBBCodes" type="checkbox" value="1"{if $signatureEnableBBCodes} checked{/if}> {lang}wcf.message.settings.enableBBCodes{/lang}</label>
							<label><input id="signatureEnableHtml" name="signatureEnableHtml" type="checkbox" value="1"{if $signatureEnableHtml} checked{/if}> {lang}wcf.message.settings.enableHtml{/lang}</label>
						</dd>
					</dl>
					
					{event name='signatureFields'}
				</section>
				
				{if $__wcf->session->getPermission('admin.user.canDisableSignature')}
					<section class="section">
						<h2 class="sectionTitle">{lang}wcf.acp.user.disableSignature{/lang}</h2>
						
						<dl>
							<dt></dt>
							<dd>
								<label><input type="checkbox" id="disableSignature" name="disableSignature" value="1"{if $disableSignature == 1} checked{/if}> {lang}wcf.acp.user.disableSignature{/lang}</label>
							</dd>
						</dl>
						
						<dl>
							<dt><label for="disableSignatureReason">{lang}wcf.acp.user.disableSignatureReason{/lang}</label></dt>
							<dd>
								<textarea name="disableSignatureReason" id="disableSignatureReason" cols="40" rows="10">{$disableSignatureReason}</textarea>
							</dd>
						</dl>
						
						<dl>
							<dt></dt>
							<dd><label><input type="checkbox" id="disableSignatureNeverExpires" name="disableSignatureNeverExpires" value="1"{if !$disableSignatureExpires} checked{/if}> {lang}wcf.acp.user.disableSignature.neverExpires{/lang}</label></dd>
						</dl>
						
						<dl id="disableSignatureExpiresSetting">
							<dt><label for="disableSignatureExpiresExpires">{lang}wcf.acp.user.disableSignature.expires{/lang}</label></dt>
							<dd>
								<input type="date" name="disableSignatureExpires" id="disableSignatureExpires" min="{TIME_NOW|date:'Y-m-d'}" {if $disableSignatureExpires} value="{$disableSignatureExpires|date:'Y-m-d'}"{/if} class="medium" data-ignore-timezone="true">
								<small>{lang}wcf.acp.user.disableSignature.expires.description{/lang}</small>
							</dd>
						</dl>

						{event name='disableSignatureFields'}
					</section>
					
					<script data-relocate="true">
						//<![CDATA[
						$('#disableSignature').change(function() {
							if ($('#disableSignature').is(':checked')) {
								$('#disableSignatureReason').attr('readonly', false);
								$('#disableSignatureNeverExpires, #disableSignatureExpires').enable();
								$('#disableSignatureReason, #disableSignatureNeverExpires, #disableSignatureExpires').parents('dl').removeClass('disabled');
							}
							else {
								$('#disableSignatureReason').attr('readonly', true);
								$('#disableSignatureNeverExpires, #disableSignatureExpires').disable();
								$('#disableSignatureReason, #disableSignatureNeverExpires, #disableSignatureExpires').parents('dl').addClass('disabled');
							}
						});
						
						$('#disableSignature').change();
						
						$('#disableSignatureNeverExpires').change(function() {
							if ($('#disableSignatureNeverExpires').is(':checked')) {
								$('#disableSignatureExpiresSetting').hide();
							}
							else {
								$('#disableSignatureExpiresSetting').show();
							}
						});
						
						$('#disableSignatureNeverExpires').change();
						//]]>
					</script>
				{/if}

				{event name='signatureFieldsets'}
			</div>
		{/if}
		
		{if $action == 'edit'}
			<div id="avatarForm" class="tabMenuContent hidden">
				<section class="section">
					<h2 class="sectionTitle">{lang}wcf.user.avatar{/lang}</h2>
					
					<dl>
						<dt></dt>
						<dd>
							<label><input type="radio" name="avatarType" value="none"{if $avatarType == 'none'} checked{/if}> {lang}wcf.user.avatar.type.none{/lang}</label>
						</dd>
					</dl>
					
					<dl class="jsOnly{if $errorType[customAvatar]|isset} formError{/if}" id="avatarUpload">
						<dt>
							{if $avatarType == 'custom'}
								{assign var='__customAvatar' value=$userAvatar->getImageTag(96)}
								{if $userAvatar->canCrop()}
									{assign var='__customAvatar' value=$__customAvatar|substr:0:-2}
									{assign var='__customAvatarTitle' value='wcf.user.avatar.type.custom.crop'|language}
									{append var='__customAvatar' value='class="userAvatarCrop jsTooltip" title="'|concat:$__customAvatarTitle:'">'}
								{/if}
								
								{@$__customAvatar}
							{else}
								<img src="{@$__wcf->getPath()}images/avatars/avatar-default.svg" alt="" class="userAvatarImage icon96">
							{/if}
						</dt>
						<dd>
							<label><input type="radio" name="avatarType" value="custom"{if $avatarType == 'custom'} checked{/if}> {lang}wcf.user.avatar.type.custom{/lang}</label>
							
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
							<dt><img src="https://secure.gravatar.com/avatar/{@$user->email|strtolower|md5}?s=96{if GRAVATAR_DEFAULT_TYPE != '404'}&amp;d={@GRAVATAR_DEFAULT_TYPE}{/if}" alt="" class="userAvatarImage icon96"></dt>
							<dd>
								<label><input type="radio" name="avatarType" value="gravatar"{if $avatarType == 'gravatar'} checked{/if}> {lang}wcf.user.avatar.type.gravatar{/lang}</label>
								
								{if $errorType[gravatar]|isset}
									<small class="innerError">
										{if $errorType[gravatar] == 'notFound'}{lang}wcf.user.avatar.type.gravatar.error.notFound{/lang}{/if}
									</small>
								{/if}
							</dd>
						</dl>
					{/if}

					{event name='avatarFields'}
				</section>
				
				{if $__wcf->session->getPermission('admin.user.canDisableAvatar')}
					<section class="section">
						<h2 class="sectionTitle">{lang}wcf.acp.user.disableAvatar{/lang}</h2>
						
						<dl>
							<dt></dt>
							<dd>
								<label><input type="checkbox" id="disableAvatar" name="disableAvatar" value="1"{if $disableAvatar == 1} checked{/if}> {lang}wcf.acp.user.disableAvatar{/lang}</label>
							</dd>
						</dl>
						
						<dl>
							<dt><label for="disableAvatarReason">{lang}wcf.acp.user.disableAvatarReason{/lang}</label></dt>
							<dd>
								<textarea name="disableAvatarReason" id="disableAvatarReason" cols="40" rows="10">{$disableAvatarReason}</textarea>
							</dd>
						</dl>
						
						<dl>
							<dt></dt>
							<dd><label><input type="checkbox" id="disableAvatarNeverExpires" name="disableAvatarNeverExpires" value="1"{if !$disableAvatarExpires} checked{/if}> {lang}wcf.acp.user.disableAvatar.neverExpires{/lang}</label></dd>
						</dl>
						
						<dl id="disableAvatarExpiresSetting">
							<dt><label for="disableAvatarExpiresExpires">{lang}wcf.acp.user.disableAvatar.expires{/lang}</label></dt>
							<dd>
								<input type="date" name="disableAvatarExpires" id="disableAvatarExpires" min="{TIME_NOW|date:'Y-m-d'}" {if $disableAvatarExpires} value="{$disableAvatarExpires|date:'Y-m-d'}"{/if} class="medium">
								<small>{lang}wcf.acp.user.disableAvatar.expires.description{/lang}</small>
							</dd>
						</dl>

						{event name='disableAvatarFields'}
					</section>
					
					<script data-relocate="true">
						//<![CDATA[
						$('#disableAvatar').change(function() {
							if ($('#disableAvatar').is(':checked')) {
								$('#disableAvatarReason').attr('readonly', false);
								$('#disableAvatarNeverExpires, #disableAvatarExpires').enable();
								$('#disableAvatarReason, #disableAvatarNeverExpires, #disableAvatarExpires').parents('dl').removeClass('disabled');
							}
							else {
								$('#disableAvatarReason').attr('readonly', true);
								$('#disableAvatarNeverExpires, #disableAvatarExpires').disable();
								$('#disableAvatarReason, #disableAvatarNeverExpires, #disableAvatarExpires').parents('dl').addClass('disabled');
							}
						});
						
						$('#disableAvatar').change();
						
						$('#disableAvatarNeverExpires').change(function() {
							if ($('#disableAvatarNeverExpires').is(':checked')) {
								$('#disableAvatarExpiresSetting').hide();
							}
							else {
								$('#disableAvatarExpiresSetting').show();
							}
						});
						
						$('#disableAvatarNeverExpires').change();
						//]]>
					</script>
				{/if}
				
				<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Message.js?v={@LAST_UPDATE_TIME}"></script>
				<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.User.js?v={@LAST_UPDATE_TIME}"></script>
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
						
						{if $userAvatar && $userAvatar->canCrop()}
							new WCF.User.Avatar.Upload({@$user->userID}, new WCF.User.Avatar.Crop({@$userAvatar->avatarID}));
						{else}
							new WCF.User.Avatar.Upload({@$user->userID});
						{/if}
					});
					//]]>
				</script>

				{event name='avatarFieldsets'}
			</div>
		{/if}
		
		{event name='tabMenuContent'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
