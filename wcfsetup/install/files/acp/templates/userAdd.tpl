{include file='header' pageTitle='wcf.acp.user.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.{$action}{/lang}</h1>
	</div>

	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{if $action === 'edit'}
	{unsafe:$userProfileHeaderView}
{/if}

{if $userID|isset && $__wcf->user->userID == $userID}
	<woltlab-core-notice type="warning">{lang}wcf.acp.user.edit.warning.selfEdit{/lang}</woltlab-core-notice>
{/if}

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='UserAdd'}{/link}{else}{link controller='UserEdit' id=$userID}{/link}{/if}">
	<div class="section tabMenuContainer">
		<nav class="tabMenu">
			<ul>
				<li><a href="#__essentials">{lang}wcf.global.form.data{/lang}</a></li>

				{foreach from=$optionTree item=categoryLevel1}
					<li><a href="#{$categoryLevel1[object]->categoryName|rawurlencode}">{lang}wcf.user.option.category.{$categoryLevel1[object]->categoryName}{/lang}</a></li>
				{/foreach}

				{if MODULE_USER_SIGNATURE}
					<li><a href="#signatureManagement">{lang}wcf.user.signature{/lang}</a></li>
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
									{lang}wcf.user.username.error.{$errorType.username}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>

				{if $action == 'add' || $__wcf->session->getPermission('admin.user.canEditMailAddress')}
					<dl{if $errorType.email|isset} class="formError"{/if}>
						<dt><label for="email">{lang}wcf.user.email{/lang}</label></dt>
						<dd>
							<input type="email" id="email" name="email" value="{$email}" class="medium">
							{if $errorType.email|isset}
								<small class="innerError">
									{if $errorType.email == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.user.email.error.{$errorType.email}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
				{/if}

				{if ($action == 'add' || $__wcf->session->getPermission('admin.user.canEditPassword')) && ($action == 'add' || $user->authData|empty)}
					<dl{if $errorType.password|isset} class="formError"{/if}>
						<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
						<dd>
							<input type="password" id="password" name="password" value="{$password}" class="medium" autocomplete="new-password">
							{if $errorType.password|isset}
								<small class="innerError">
									{if $errorType.password == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.user.password.error.{$errorType.password}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>

					<script data-relocate="true">
						require(['WoltLabSuite/Core/Ui/User/PasswordStrength'], function (PasswordStrength) {
							{include file='shared_passwordStrengthLanguage'}

							var relatedInputs = [];
							if (elById('username')) relatedInputs.push(elById('username'));
							if (elById('email')) relatedInputs.push(elById('email'));

							new PasswordStrength(elById('password'), {
								relatedInputs: relatedInputs,
								staticDictionary: [
									'{unsafe:$__wcf->user->username|encodeJS}',
									'{unsafe:$__wcf->user->email|encodeJS}',
									{if $user|isset}
										'{unsafe:$user->username|encodeJS}',
										'{unsafe:$user->email|encodeJS}',
									{/if}
								]
							});
						})
					</script>
				{/if}

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

			{if $action == 'add' || $__wcf->session->getPermission('admin.user.canEditPassword')}
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

			{event name='sections'}
		</div>

		{foreach from=$optionTree item=categoryLevel1}
			<div id="{$categoryLevel1[object]->categoryName}" class="tabMenuContent hidden">
				{foreach from=$categoryLevel1[categories] item=categoryLevel2}
					<section class="section">
						<h2 class="sectionTitle">{lang}wcf.user.option.category.{$categoryLevel2[object]->categoryName}{/lang}</h2>

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
												<label><input type="checkbox" name="visibleLanguages[]" value="{$availableLanguageID}"{if $availableLanguageID|in_array:$visibleLanguages} checked{/if}> {$availableLanguage}</label>
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
													{lang}wcf.global.form.error.{$errorType}{/lang}
												{else}
													{lang}wcf.user.style.error.{$errorType.password}{/lang}
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
									<input type="text" id="userTitle" name="userTitle" value="{$userTitle}" class="long" maxlength="{USER_TITLE_MAX_LENGTH}">
									{if $errorType[userTitle]|isset}
										<small class="innerError">
											{lang}wcf.user.userTitle.error.{$errorType[userTitle]}{/lang}
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
										{lang}wcf.user.signature.error.{$errorType}{/lang}
									{/if}
								</small>
							{/if}

							{include file='shared_wysiwyg' wysiwygSelector='signature'}
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
