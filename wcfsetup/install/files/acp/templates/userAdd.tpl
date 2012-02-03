{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>

<header class="wcf-mainHeading">
	<img {if $userID|isset}id="userEdit{@$userID}" {/if}src="{@RELATIVE_WCF_DIR}icon/{@$action}1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.user.{@$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="wcf-error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $userID|isset && $__wcf->user->userID == $userID}
	<p class="wcf-warning">{lang}wcf.acp.user.edit.warning.selfEdit{/lang}</p>	
{/if}

{if $success|isset}
	<p class="wcf-success">{lang}wcf.global.form.{@$action}.success{/lang}</p>	
{/if}

<div class="wcf-contentHeader">
	<nav>
		<ul class="largeButtons">
			<li><a href="{link controller='UserList'}{/link}" title="{lang}wcf.acp.menu.link.user.list{/lang}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/users1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.user.list{/lang}</span></a></li>
			<li><a href="{link controller='UserSearch'}{/link}" title="{lang}wcf.acp.user.search{/lang}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/search1.svg" alt="" /> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='UserAdd'}{/link}{else}{link controller='UserEdit'}{/link}{/if}">
	<div class="wcf-border wcf-content">
		<dl{if $errorType.username|isset} class="wcf-formError"{/if}>
			<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
			<dd>
				<input type="text" id="username" name="username" value="{$username}" pattern="^[^,\n]+$" autofocus="autofocus" class="medium" />
				{if $errorType.username|isset}
					<small class="wcf-innerError">
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
					<fieldset>
						<legend>{lang}wcf.acp.user.groups{/lang}</legend>
						
						<dl>
							<dd>
								{htmlCheckboxes options=$availableGroups name=groupIDs selected=$groupIDs}
							</dd>
						</dl>
					</fieldset>
				</dd>
			</dl>
		{/if}
		
		{if $action == 'add' || $__wcf->session->getPermission('admin.user.canEditMailAddress')}
			<fieldset>
				<legend>{lang}wcf.user.email{/lang}</legend>
				
				<dl{if $errorType.email|isset} class="wcf-formError"{/if}>
					<dt><label for="email">{lang}wcf.user.email{/lang}</label></dt>
					<dd>	
						<input type="email" id="email" name="email" value="{$email}" required="required" class="medium" />
						{if $errorType.email|isset}
							<small class="wcf-innerError">
								{if $errorType.email == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.user.email.error.{@$errorType.email}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl{if $errorType.confirmEmail|isset} class="wcf-formError"{/if}>
					<dt><label for="confirmEmail">{lang}wcf.user.confirmEmail{/lang}</label></dt>
					<dd>
						<input type="email" id="confirmEmail" name="confirmEmail" value="{$confirmEmail}" required="required" class="medium" />
						{if $errorType.confirmEmail|isset}
							<small class="wcf-innerError">
								{lang}wcf.user.confirmEmail.error.{@$errorType.confirmEmail}{/lang}
							</small>
						{/if}
					</dd>
				</dl>
			</fieldset>
		{/if}
		
		{if $action == 'add' || $__wcf->session->getPermission('admin.user.canEditPassword')}
			<fieldset>
				<legend>{lang}wcf.user.password{/lang}</legend>
				
				<dl{if $errorType.password|isset} class="wcf-formError"{/if}>
					<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
					<dd>
						<input type="password" id="password" name="password" value="{$password}"{if $action == 'add'} required="required"{/if} class="medium" />
						{if $errorType.password|isset}
							<small class="wcf-innerError">
								{if $errorType.password == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.user.password.error.{@$errorType.password}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl{if $errorType.confirmPassword|isset} class="wcf-formError"{/if}>
					<dt><label for="confirmPassword">{lang}wcf.user.confirmPassword{/lang}</label></dt>
					<dd>
						<input type="password" id="confirmPassword" name="confirmPassword" value="{$confirmPassword}"{if $action == 'add'} required="required"{/if} class="medium" />
						{if $errorType.confirmPassword|isset}
							<small class="wcf-innerError">
								{lang}wcf.user.confirmPassword.error.{@$errorType.confirmPassword}{/lang}
							</small>
						{/if}
					</dd>
				</dl>
			</fieldset>
		{/if}
	
		{event name='fieldsets'}
		
		{if $optionTree|count || $additionalTabs|isset}
			<div class="wcf-tabMenuContainer">
				<nav class="wcf-tabMenu">
					<ul>
						{foreach from=$optionTree item=categoryLevel1}
							<li><a href="#{@$categoryLevel1[object]->categoryName}">{lang}wcf.user.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</a></li>
						{/foreach}
						
						{event name='tabMenuTabs'}
					</ul>
				</nav>
			
				{foreach from=$optionTree item=categoryLevel1}
					<div id="{@$categoryLevel1[object]->categoryName}" class="wcf-border wcf-tabMenuContent">
						<hgroup class="wcf-subHeading">
							<h1>{lang}wcf.user.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</h1>
						</hgroup>
					
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
												{lang}wcf.user.visibleLanguages{/lang}
											</dt>
											<dd>
												<fieldset>
													<legend>{lang}wcf.user.visibleLanguages{/lang}</legend>
													<dl>
														<dd>
															{foreach from=$availableContentLanguages key=availableLanguageID item=availableLanguage}
																<label><input type="checkbox" name="visibleLanguages[]" value="{@$availableLanguageID}"{if $availableLanguageID|in_array:$visibleLanguages} checked="checked"{/if} /> {@$availableLanguage}</label>
															{/foreach}
														</dd>
													</dl>
												</fieldset>
											</dd>
										</dl>
									{/if}
								{/if}
							
								{include file='optionFieldList' options=$categoryLevel2[options] langPrefix='wcf.user.option.'}
							</fieldset>
						{/foreach}
					</div>
				{/foreach}

				{event name='tabMenuContent'}
			</div>
		{/if}
	</div>
	
	<div class="wcf-formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="action" value="{@$action}" />
 		{if $userID|isset}<input type="hidden" name="id" value="{@$userID}" />{/if}
 	</div>
</form>

{include file='footer'}
