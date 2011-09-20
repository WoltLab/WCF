{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabMenu.class.js"></script>
{if $userID|isset}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/InlineListEdit.class.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/UserListEdit.class.js"></script>
{/if}
<script type="text/javascript">
	//<![CDATA[
	var tabMenu = new TabMenu();
	{if $optionTree|count}onloadEvents.push(function() { tabMenu.showSubTabMenu('{@$options[0][object]->categoryName}') });{/if}
	
	{if $userID|isset}
		var userData = new Hash();
		userData.set({@$userID}, {
			'isMarked': {@$user->isMarked()}
		});
		
		var url = '{@$url|encodeJS}';
		
		// language
		var language = new Object();
		language['wcf.global.button.mark']		= '{lang}wcf.global.button.mark{/lang}';
		language['wcf.global.button.unmark']		= '{lang}wcf.global.button.unmark{/lang}';
		language['wcf.global.button.delete']		= '{lang}wcf.global.button.delete{/lang}';
		language['wcf.acp.user.button.sendMail']	= '{lang}wcf.acp.user.button.sendMail{/lang}';
		language['wcf.acp.user.button.exportMail']	= '{lang}wcf.acp.user.button.exportMail{/lang}';
		language['wcf.acp.user.button.assignGroup']	= '{lang}wcf.acp.user.button.assignGroup{/lang}';
		language['wcf.acp.user.deleteMarked.sure']	= '{lang}wcf.acp.user.deleteMarked.sure{/lang}';
		language['wcf.acp.user.delete.sure']		= '{lang}wcf.acp.user.delete.sure{/lang}';
		language['wcf.acp.user.markedUsers']		= '{lang}wcf.acp.user.markedUsers{/lang}';
		
		// additional options
		var additionalOptions = new Array();
		var additionalUserOptions = new Array();
		{if $additionalUserOptions|isset}{@$additionalUserOptions}{/if}
		{if $additionalMarkedOptions|isset}{@$additionalMarkedOptions}{/if}
		
		// permissions
		var permissions = new Object();
		permissions['canEditUser'] = {if $__wcf->session->getPermission('admin.user.canEditUser')}1{else}0{/if};
		permissions['canDeleteUser'] = {if $__wcf->session->getPermission('admin.user.canDeleteUser')}1{else}0{/if};
		permissions['canMailUser'] = {if $__wcf->session->getPermission('admin.user.canMailUser')}1{else}0{/if};
		permissions['canEditMailAddress'] = {if $__wcf->session->getPermission('admin.user.canEditMailAddress')}1{else}0{/if};
		permissions['canEditPassword'] = {if $__wcf->session->getPermission('admin.user.canEditPassword')}1{else}0{/if};
		
		onloadEvents.push(function() { userListEdit = new UserListEdit(userData, {@$markedUsers}, additionalUserOptions, additionalOptions); });
	{/if}
	//]]>
</script>

<header class="mainHeading">
	<img {if $userID|isset}id="userEdit{@$userID}" {/if}src="{@RELATIVE_WCF_DIR}icon/{@$action}1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.user.{@$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.form.{@$action}.success{/lang}</p>	
{/if}

{if $userID|isset && $__wcf->user->userID == $userID}
	<p class="warning">{lang}wcf.acp.user.edit.warning.selfEdit{/lang}</p>	
{/if}

<div class="contentHeader">
	<nav class="largeButtons">
		<ul>
			<li><a href="index.php?page=UserList{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.user.list{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/usersM.png" alt="" /> <span>{lang}wcf.acp.menu.link.user.list{/lang}</span></a></li>
			<li><a href="index.php?form=UserSearch{@SID_ARG_2ND}" title="{lang}wcf.acp.user.search{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/searchM.png" alt="" /> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

<form method="post" action="index.php?form=User{@$action|ucfirst}">
	<div class="border content">
		<dl{if $errorType.username|isset} class="formError"{/if}>
			<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
			<dd>
				<input type="text" id="username" name="username" value="{$username}" class="medium" />
				{if $errorType.username|isset}
					<small class="innerError">
						{if $errorType.username == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
						{if $errorType.username == 'notValid'}{lang}wcf.user.error.username.notValid{/lang}{/if}
						{if $errorType.username == 'notUnique'}{lang}wcf.user.error.username.notUnique{/lang}{/if}
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
				
				<dl{if $errorType.email|isset} class="formError"{/if}>
					<dt><label for="email">{lang}wcf.user.email{/lang}</label></dt>
					<dd>	
						<input type="email" id="email" name="email" value="{$email}" class="medium" />
						{if $errorType.email|isset}
							<small class="innerError">
								{if $errorType.email == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								{if $errorType.email == 'notValid'}{lang}wcf.user.error.email.notValid{/lang}{/if}
								{if $errorType.email == 'notUnique'}{lang}wcf.user.error.email.notUnique{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl{if $errorType.confirmEmail|isset} class="formError"{/if}>
					<dt><label for="confirmEmail">{lang}wcf.user.confirmEmail{/lang}</label></dt>
					<dd>
						<input type="email" id="confirmEmail" name="confirmEmail" value="{$confirmEmail}" class="medium" />
						{if $errorType.confirmEmail|isset}
							<small class="innerError">
								{if $errorType.confirmEmail == 'notEqual'}{lang}wcf.user.error.confirmEmail.notEqual{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
			</fieldset>
		{/if}
		
		{if $action == 'add' || $__wcf->session->getPermission('admin.user.canEditPassword')}
			<fieldset>
				<legend>{lang}wcf.user.password{/lang}</legend>
				
				<dl{if $errorType.password|isset} class="formError"{/if}>
					<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
					<dd>
						<input type="password" id="password" name="password" value="{$password}" class="medium" />
						{if $errorType.password|isset}
							<small class="innerError">
								{if $errorType.password == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl{if $errorType.confirmPassword|isset} class="formError"{/if}>
					<dt><label for="confirmPassword">{lang}wcf.user.confirmPassword{/lang}</label></dt>
					<dd>
						<input type="password" id="confirmPassword" name="confirmPassword" value="{$confirmPassword}" class="medium" />
						{if $errorType.confirmPassword|isset}
							<small class="innerError">
								{if $errorType.confirmPassword == 'notEqual'}{lang}wcf.user.error.confirmPassword.notEqual{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
			</fieldset>
		{/if}
	
		{if $additionalFields|isset}{@$additionalFields}{/if}
		
		{if $optionTree|count || $additionalTabs|isset}
			<nav>
				<div class="tabMenu">
					<ul>
						{foreach from=$optionTree item=categoryLevel1}
							<li id="{@$categoryLevel1[object]->categoryName}"><a onclick="tabMenu.showSubTabMenu('{@$categoryLevel1[object]->categoryName}');"><span>{lang}wcf.user.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</span></a></li>
						{/foreach}
						
						{if $additionalTabs|isset}{@$additionalTabs}{/if}
					</ul>
				</div>
			<nav>
			<div class="menu">
				<div class="containerHead"><div> </div></div>
			</div>
			
			{foreach from=$optionTree item=categoryLevel1}
				<div id="{@$categoryLevel1[object]->categoryName}-content" class="border tabMenuContent hidden">
					<hgroup class="subHeading">
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
		{/if}
		
		{if $additionalTabContents|isset}{@$additionalTabContents}{/if}
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="action" value="{@$action}" />
 		{if $userID|isset}<input type="hidden" name="userID" value="{@$userID}" />{/if}
 	</div>
</form>

{include file='footer'}
