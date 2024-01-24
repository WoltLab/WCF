{include file='header' pageTitle='wcf.acp.user.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.{@$action}{/lang}</h1>
		{if $action == 'edit'}<p class="contentHeaderDescription">{$user->username}</p>{/if}
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			{if $action === 'edit'}
				{hascontent}
					<li>
						<div 	class="dropdown"{*
								*}id="userListDropdown{@$user->userID}" {*
								*}data-object-id="{@$user->getObjectID()}" {*
								*}data-banned="{if $user->banned}true{else}false{/if}" {*
								*}data-enabled="{if !$user->activationCode}true{else}false{/if}" {*
								*}data-email-confirmed="{if $user->isEmailConfirmed()}true{else}false{/if}" {*
						*}>
							<a href="#" class="dropdownToggle button">
								{icon name='pencil'}
								<span>{lang}wcf.global.button.edit{/lang}</span>
							</a>

							<ul class="dropdownMenu">
								{content}
									{event name='dropdownItems'}

									{if $user->userID != $__wcf->user->userID}
										{if $__wcf->session->getPermission('admin.user.canEnableUser')}
											<li>
												<a  href="#" {*
													*}class="jsEnable" {*
													*}data-enable-message="{lang}wcf.acp.user.enable{/lang}" {*
													*}data-disable-message="{lang}wcf.acp.user.disable{/lang}"{*
												*}>
													{lang}wcf.acp.user.{if !$user->activationCode}disable{else}enable{/if}{/lang}
												</a>
											</li>
										{/if}

										{if $__wcf->session->getPermission('admin.user.canEnableUser')}
											<li>
												<a href="#" {*
												*}class="jsConfirmEmailToggle" {*
												*}data-confirm-email-message="{lang}wcf.acp.user.action.confirmEmail{/lang}" {*
												*}data-unconfirm-email-message="{lang}wcf.acp.user.action.unconfirmEmail{/lang}"{*
												*}>
													{lang}wcf.acp.user.action.{if $user->isEmailConfirmed()}un{/if}confirmEmail{/lang}
												</a>
											</li>
										{/if}

										{if $__wcf->session->getPermission('admin.user.canMailUser')}
											<li>
												<a {*
												*}href="{link controller='UserMail' id=$user->userID}{/link}"{*
												*}>
													{lang}wcf.acp.user.action.sendMail{/lang}
												</a>
											</li>
										{/if}

										{if $__wcf->session->getPermission('admin.user.canEditPassword')}
											<li>
												<a {*
												*}href="#" {*
												*}class="jsSendNewPassword"{*
												*}>
													{lang}wcf.acp.user.action.sendNewPassword{/lang}
												</a>
											</li>
										{/if}
									{/if}

									{if $__wcf->session->getPermission('admin.user.canExportGdprData')}
										<li>
											<a {*
												*}href="{link controller='UserExportGdpr' id=$user->userID}{/link}"{*
												*}>
												{lang}wcf.acp.user.exportGdpr{/lang}
											</a>
										</li>
									{/if}

									{if $__wcf->session->getPermission('admin.user.canDeleteUser') && $user->userID != $__wcf->user->userID}
										<li class="dropdownDivider"></li>
										<li><a href="#" class="jsDelete">{lang}wcf.global.button.delete{/lang}</a></li>
										<li><a href="#" class="jsDeleteContent">{lang}wcf.acp.content.removeContent{/lang}</a></li>
									{/if}
								{/content}
							</ul>
						</div>
					</li>
					<script data-relocate="true">
						require([
							'WoltLabSuite/Core/Language',
							'WoltLabSuite/Core/Acp/Ui/User/Editor',
							'WoltLabSuite/Core/Acp/Ui/User/Content/Remove/Handler',
							'WoltLabSuite/Core/Acp/Ui/User/Action/Handler/Delete',
							'WoltLabSuite/Core/Acp/Ui/User/Action/DisableAction',
							'WoltLabSuite/Core/Acp/Ui/User/Action/SendNewPasswordAction',
							'WoltLabSuite/Core/Acp/Ui/User/Action/ToggleConfirmEmailAction',
							'WoltLabSuite/Core/Controller/Clipboard',
						], (
							Language,
							AcpUiUserList,
							AcpUserContentRemoveHandler,
							{ Delete },
							{ DisableAction },
							{ SendNewPasswordAction },
							{ ToggleConfirmEmailAction },
							Clipboard
						) => {
								Language.addObject({
									'wcf.acp.user.sendNewPassword.workerTitle': '{jslang}wcf.acp.user.sendNewPassword.workerTitle{/jslang}',
									'wcf.acp.user.action.sendNewPassword.confirmMessage': '{jslang}wcf.acp.user.action.sendNewPassword.confirmMessage{/jslang}',
									'wcf.acp.worker.abort.confirmMessage': '{jslang}wcf.acp.worker.abort.confirmMessage{/jslang}',
									'wcf.acp.content.removeContent': '{jslang}wcf.acp.content.removeContent{/jslang}',
								});

								const dropdownElement = document.querySelector("#userListDropdown{@$user->userID}");

								const deleteContent = document.querySelector(".jsDeleteContent");
								if (deleteContent !== null) {
									new AcpUserContentRemoveHandler(deleteContent, {@$user->userID}, (data) => {
										window.location.reload();
									});
								}

								const sendNewPassword = document.querySelector(".jsSendNewPassword");
								if (sendNewPassword !== null) {
									new SendNewPasswordAction(sendNewPassword, {@$user->userID}, dropdownElement);
								}

								const toggleConfirmEmail = document.querySelector(".jsConfirmEmailToggle");
								if (toggleConfirmEmail !== null) {
									new ToggleConfirmEmailAction(toggleConfirmEmail, {@$user->userID}, dropdownElement);
								}

								const enableUser = document.querySelector(".jsEnable");
								if (enableUser !== null) {
									new DisableAction(enableUser, {@$user->userID}, dropdownElement);
								}

								const deleteUser = document.querySelector(".jsDelete");
								if (deleteUser !== null) {
									// We cannot use the DeleteAction, because the Delete Action is only usable for
									// dropdown menues.
									deleteUser.addEventListener("click", (event) => {
										const deleteAction = new Delete([{@$user->userID}], () => {
											window.location.href = "{link controller='UserList'}{/link}";
										}, '{jslang objectTitle=$user->username}wcf.button.delete.confirmMessage{/jslang}');

										deleteAction.delete();
									});
								}
							});
					</script>
				{/if}
			{/hascontent}

			<li><a href="{link controller='UserList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.user.list{/lang}</span></a></li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $userID|isset && $__wcf->user->userID == $userID}
	<p class="warning">{lang}wcf.acp.user.edit.warning.selfEdit{/lang}</p>
{/if}

{include file='formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='UserAdd'}{/link}{else}{link controller='UserEdit' id=$userID}{/link}{/if}">
	<div class="section tabMenuContainer">
		<nav class="tabMenu">
			<ul>
				<li><a href="#__essentials">{lang}wcf.global.form.data{/lang}</a></li>

				{foreach from=$optionTree item=categoryLevel1}
					<li><a href="#{$categoryLevel1[object]->categoryName|rawurlencode}">{lang}wcf.user.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</a></li>
				{/foreach}

				{if MODULE_USER_SIGNATURE}
					<li><a href="#signatureManagement">{lang}wcf.user.signature{/lang}</a></li>
				{/if}

				{if $action === 'edit'}
					<li><a href="#avatarForm">{lang}wcf.user.avatar{/lang}</a></li>
					<li><a href="#coverPhotoForm">{lang}wcf.user.coverPhoto{/lang}</a></li>
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
						<dd class="checkboxList">
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
				{if $action == 'edit' && !$user->authData|empty}
					<section class="section">
						<h2 class="sectionTitle">{lang}wcf.user.3rdparty{/lang}</h2>

						<div class="info">{lang}wcf.user.3rdparty.connect.info{/lang}</div>

						<dl>
							<dt></dt>
							<dd>
								<label><input type="checkbox" name="disconnect3rdParty" value="1"> {lang}wcf.user.3rdparty.{$user->getAuthProvider()}.disconnect{/lang}</label>
							</dd>
						</dl>
					</section>
				{else}
					<section class="section">
						<h2 class="sectionTitle">{lang}wcf.user.password{/lang}</h2>

						<dl{if $errorType.password|isset} class="formError"{/if}>
							<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
							<dd>
								<input type="password" id="password" name="password" value="{$password}" class="medium" autocomplete="new-password">
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
								<input type="password" id="confirmPassword" name="confirmPassword" value="{$confirmPassword}" class="medium" autocomplete="new-password">
								{if $errorType.confirmPassword|isset}
									<small class="innerError">
										{lang}wcf.user.confirmPassword.error.{@$errorType.confirmPassword}{/lang}
									</small>
								{/if}
							</dd>
						</dl>

						<script data-relocate="true">
							require(['WoltLabSuite/Core/Ui/User/PasswordStrength', 'Language'], function (PasswordStrength, Language) {
								{include file='passwordStrengthLanguage'}

								var relatedInputs = [];
								if (elById('username')) relatedInputs.push(elById('username'));
								if (elById('email')) relatedInputs.push(elById('email'));

								new PasswordStrength(elById('password'), {
									relatedInputs: relatedInputs,
									staticDictionary: [
										'{$__wcf->user->username|encodeJS}',
										'{$__wcf->user->email|encodeJS}',
										{if $user|isset}
											'{$user->username|encodeJS}',
											'{$user->email|encodeJS}',
										{/if}
									]
								});
							})
						</script>

						{event name='passwordFields'}
					</section>
				{/if}

				{if $action == 'edit' && $user->multifactorActive}
					<section class="section">
						<h2 class="sectionTitle">{lang}wcf.acp.user.security.multifactor{/lang}</h2>

						<dl>
							<dt>{lang}wcf.acp.user.security.multifactor{/lang}</dt>
							<dd>
								{icon name='check'} {lang}wcf.acp.user.security.multifactor.active{/lang}
								<small>{lang}wcf.acp.user.security.multifactor.active.description{/lang}</small>
							</dd>
						</dl>

						<dl>
							<dt></dt>
							<dd>
								<label>
									<input type="checkbox" id="multifactorDisable" name="multifactorDisable" value="1"> {lang}wcf.acp.user.security.multifactor.disable{/lang}
								</label>
								<small>
									{lang}wcf.acp.user.security.multifactor.disable.description{/lang}
								</small>
							</dd>
						</dl>
					</section>
				{/if}
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
				</script>
			{/if}

			{event name='sections'}
		</div>

		{foreach from=$optionTree item=categoryLevel1}
			<div id="{@$categoryLevel1[object]->categoryName}" class="tabMenuContent hidden">
				{foreach from=$categoryLevel1[categories] item=categoryLevel2}
					<section class="section">
						<h2 class="sectionTitle">{lang}wcf.user.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</h2>

						{if $categoryLevel2[object]->categoryName == 'settings.general'}
							{if $availableLanguages|count > 1}
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
												<label><input type="checkbox" name="visibleLanguages[]" value="{$availableLanguageID}"{if $availableLanguageID|in_array:$visibleLanguages} checked{/if}> {@$availableLanguage}</label>
											{/foreach}
										</dd>
									</dl>
								{/if}
							{/if}

							{if $action === 'edit' && $availableStyles|count > 1}
								<dl>
									<dt><label for="styleID">{lang}wcf.user.style{/lang}</label></dt>
									<dd>
										<select id="styleID" name="styleID">
											<option value="0">{lang}wcf.global.defaultValue{/lang}</option>
											{foreach from=$availableStyles item=style}
												<option value="{$style->styleID}"{if $style->styleID == $styleID} selected{/if}>{$style->styleName}</option>
											{/foreach}
										</select>
										{if $errorField === 'styleID'}
											<small class="innerError">
												{if $errorType === 'empty' || $errorType === 'noValidSelection'}
													{lang}wcf.global.form.error.{@$errorType}{/lang}
												{else}
													{lang}wcf.user.style.error.{@$errorType.password}{/lang}
												{/if}
											</small>
										{/if}
										<small>{lang}wcf.user.style.description{/lang}</small>
									</dd>
								</dl>
							{/if}
							
							{if $action === 'edit'}
								<dl>
									<dt><label for="colorScheme">{lang}wcf.user.style.colorScheme{/lang}</label></dt>
									<dd>
										<select id="colorScheme" name="colorScheme">
											<option value="system"{if $colorScheme === 'system'} selected{/if}>{lang}wcf.style.setColorScheme.system{/lang}</option>
											<option value="light"{if $colorScheme === 'light'} selected{/if}>{lang}wcf.style.setColorScheme.light{/lang}</option>
											<option value="dark"{if $colorScheme === 'dark'} selected{/if}>{lang}wcf.style.setColorScheme.dark{/lang}</option>
										</select>
										<small>{lang}wcf.user.style.colorScheme.description{/lang}</small>
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
							<textarea name="signature" id="signature" cols="40" rows="10" class="wysiwygTextarea" data-disable-media="true">{$signature}</textarea>
							{if $errorField == 'signature'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{elseif $errorType == 'tooLong'}
										{lang}wcf.message.error.tooLong{/lang}
									{elseif $errorType == 'censoredWordsFound'}
										{lang}wcf.message.error.censoredWordsFound{/lang}
									{elseif $errorType == 'disallowedBBCodes'}
										{lang}wcf.message.error.disallowedBBCodes{/lang}
									{else}
										{lang}wcf.user.signature.error.{@$errorType}{/lang}
									{/if}
								</small>
							{/if}

							{include file='wysiwyg' wysiwygSelector='signature'}
							{include file='messageFormTabs' wysiwygContainerID='signature'}
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
							<dt><label for="disableSignatureExpires">{lang}wcf.acp.user.disableSignature.expires{/lang}</label></dt>
							<dd>
								<input type="date" name="disableSignatureExpires" id="disableSignatureExpires" min="{TIME_NOW|date:'Y-m-d'}" {if $disableSignatureExpires} value="{$disableSignatureExpires|date:'Y-m-d'}"{/if} class="medium" data-ignore-timezone="true">
								<small>{lang}wcf.acp.user.disableSignature.expires.description{/lang}</small>
							</dd>
						</dl>

						{event name='disableSignatureFields'}
					</section>

					<script data-relocate="true">
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
					</script>
				{/if}

				{event name='signatureFieldsets'}
			</div>
		{/if}

		{if $action == 'edit'}
			<div id="avatarForm" class="tabMenuContent hidden">
				<section class="section avatarEdit">
					<h2 class="sectionTitle">{lang}wcf.user.avatar{/lang}</h2>

					<dl class="avatarType">
						<dt></dt>
						<dd>
							<label><input type="radio" name="avatarType" value="none"{if $avatarType == 'none'} checked{/if}> {lang}wcf.user.avatar.type.none{/lang}</label>
						</dd>
					</dl>

					<dl class="avatarType jsOnly{if $errorType[customAvatar]|isset} formError{/if}" id="avatarUpload">
						<dt>
							{if $avatarType == 'custom' && $userAvatar !== null}
								{@$userAvatar->getImageTag(96)}
							{else}
								<img src="{@$__wcf->getPath()}images/avatars/avatar-default.svg" alt="" class="userAvatarImage" height="96" width="96">
							{/if}
						</dt>
						<dd>
							<label><input type="radio" name="avatarType" value="custom"{if $avatarType == 'custom'} checked{/if}> {lang}wcf.user.avatar.type.custom{/lang}</label>

							{* placeholder for upload button: *}
							<div class="avatarUploadButtonContainer"></div>

							{if $errorType[customAvatar]|isset}
								<small class="innerError">
									{if $errorType[customAvatar] == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								</small>
							{/if}
						</dd>
					</dl>

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
							<dt><label for="disableAvatarExpires">{lang}wcf.acp.user.disableAvatar.expires{/lang}</label></dt>
							<dd>
								<input type="date" name="disableAvatarExpires" id="disableAvatarExpires" min="{TIME_NOW|date:'Y-m-d'}" {if $disableAvatarExpires} value="{$disableAvatarExpires|date:'Y-m-d'}"{/if} class="medium">
								<small>{lang}wcf.acp.user.disableAvatar.expires.description{/lang}</small>
							</dd>
						</dl>

						{event name='disableAvatarFields'}
					</section>

					<script data-relocate="true">
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
					</script>
				{/if}

				<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Message.js?v={@LAST_UPDATE_TIME}"></script>
				<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.User.js?v={@LAST_UPDATE_TIME}"></script>
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

						new WCF.User.Avatar.Upload({@$user->userID});
					});
				</script>

				{event name='avatarFieldsets'}
			</div>

			<div id="coverPhotoForm" class="tabMenuContent hidden">
				<section class="section">
					<header class="sectionHeader">
						<h2 class="sectionTitle">{lang}wcf.user.coverPhoto{/lang}</h2>
						<p class="sectionDescription">{lang}wcf.acp.user.coverPhoto.description{/lang}</p>
					</header>

					{if $userCoverPhoto}
						<dl>
							<dt></dt>
							<dd>
								<div id="coverPhotoPreview" style="background-image: url({$userCoverPhoto->getURL()})"></div>
							</dd>
						</dl>

						{if $__wcf->session->getPermission('admin.user.canDisableCoverPhoto')}
							<dl>
								<dt></dt>
								<dd>
									<label><input type="checkbox" id="deleteCoverPhoto" name="deleteCoverPhoto" value="1"{if $deleteCoverPhoto == 1} checked{/if}> {lang}wcf.acp.user.deleteCoverPhoto{/lang}</label>
								</dd>
							</dl>
						{/if}
					{else}
						<p class="info">{lang}wcf.user.coverPhoto.noImage{/lang}</p>
					{/if}

					{event name='coverPhotoFields'}
				</section>

				{if $__wcf->session->getPermission('admin.user.canDisableCoverPhoto')}
					<section class="section">
						<h2 class="sectionTitle">{lang}wcf.acp.user.disableCoverPhoto{/lang}</h2>

						<dl>
							<dt></dt>
							<dd>
								<label><input type="checkbox" id="disableCoverPhoto" name="disableCoverPhoto" value="1"{if $disableCoverPhoto == 1} checked{/if}> {lang}wcf.acp.user.disableCoverPhoto{/lang}</label>
							</dd>
						</dl>

						<dl>
							<dt><label for="disableCoverPhotoReason">{lang}wcf.acp.user.disableCoverPhoto.reason{/lang}</label></dt>
							<dd>
								<textarea name="disableCoverPhotoReason" id="disableCoverPhotoReason" cols="40" rows="10">{$disableCoverPhotoReason}</textarea>
							</dd>
						</dl>

						<dl>
							<dt></dt>
							<dd><label><input type="checkbox" id="disableCoverPhotoNeverExpires" name="disableCoverPhotoNeverExpires" value="1"{if !$disableCoverPhotoExpires} checked{/if}> {lang}wcf.acp.user.disableCoverPhoto.neverExpires{/lang}</label></dd>
						</dl>

						<dl id="disableCoverPhotoExpiresSetting">
							<dt><label for="disableCoverPhotoExpires">{lang}wcf.acp.user.disableCoverPhoto.expires{/lang}</label></dt>
							<dd>
								<input type="date" name="disableCoverPhotoExpires" id="disableCoverPhotoExpires" min="{TIME_NOW|date:'Y-m-d'}" {if $disableCoverPhotoExpires} value="{$disableCoverPhotoExpires|date:'Y-m-d'}"{/if} class="medium">
								<small>{lang}wcf.acp.user.disableCoverPhoto.expires.description{/lang}</small>
							</dd>
						</dl>

						{event name='disableAvatarFields'}
					</section>

					<script data-relocate="true">
						$('#disableCoverPhoto').change(function() {
							if ($('#disableCoverPhoto').is(':checked')) {
								$('#disableCoverPhotoReason').attr('readonly', false);
								$('#disableCoverPhotoNeverExpires, #disableCoverPhotoExpires').enable();
								$('#disableCoverPhotoReason, #disableCoverPhotoNeverExpires, #disableCoverPhotoExpires').parents('dl').removeClass('disabled');
							}
							else {
								$('#disableCoverPhotoReason').attr('readonly', true);
								$('#disableCoverPhotoNeverExpires, #disableCoverPhotoExpires').disable();
								$('#disableCoverPhotoReason, #disableCoverPhotoNeverExpires, #disableCoverPhotoExpires').parents('dl').addClass('disabled');
							}
						});

						$('#disableCoverPhoto').change();

						$('#disableCoverPhotoNeverExpires').change(function() {
							if ($('#disableCoverPhotoNeverExpires').is(':checked')) {
								$('#disableCoverPhotoExpiresSetting').hide();
							}
							else {
								$('#disableCoverPhotoExpiresSetting').show();
							}
						});

						$('#disableCoverPhotoNeverExpires').change();
					</script>
				{/if}

				{event name='coverPhotoFieldsets'}
			</div>
		{/if}

		{event name='tabMenuContent'} {* deprecated event *}
		{event name='tabMenuContents'}
	</div>

	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{if $action === 'edit' && $ownerGroupID}
	<script data-relocate="true">
		(() => {
			const input = document.querySelector('input[name="groupIDs[]"][value="{$ownerGroupID}"]');
			if (input) {
				const icon = document.createElement("span");
				icon.innerHTML = '<fa-icon name="shield-halved"></fa-icon>';
				icon.classList.add("jsTooltip");
				icon.title = '{jslang}wcf.acp.group.type.owner{/jslang}';
				input.parentElement.append(icon);

				{if $user->userID == $__wcf->user->userID}
					const shadow = document.createElement("input");
					shadow.name = input.name;
					shadow.type = "hidden";
					shadow.value = input.value;

					input.parentElement.append(shadow);
					input.disabled = true;
				{/if}
			}
		})();
	</script>
{/if}

{include file='footer'}
