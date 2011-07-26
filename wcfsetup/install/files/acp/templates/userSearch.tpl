{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/userSearchL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.user.search{/lang}</h1>
	</hgroup>
</header>

{if $errorField == 'search'}
<p class="error">{lang}wcf.acp.user.search.error.noMatches{/lang}</p>
{/if}

{*if $deletedUsers}
	<p class="success">{lang}wcf.acp.user.delete.success{/lang}</p>	
{elseif $deletedUsers === 0}
	<p class="error">{lang}wcf.acp.user.delete.error{/lang}</p>	
{/if*}

<div class="contentHeader">
	<nav class="largeButtons">
		<ul>
			{if $__wcf->session->getPermission('admin.user.canAddUser')}
				<li><a href="index.php?form=UserAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.user.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/userAddM.png" alt="" /> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
			{/if}
			<li><a href="index.php?page=UserList{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.user.list{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/usersM.png" alt="" /> <span>{lang}wcf.acp.menu.link.user.list{/lang}</span></a></li>
		</ul>
	</nav>
</div>

<form method="post" action="index.php?form=UserSearch">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.user.search.conditions.general{/lang}</legend>
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="username">{lang}wcf.user.username{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="username" name="username" value="{$username}" class="inputText" />
						<script type="text/javascript">
							//<![CDATA[
							var suggestion = new Suggestion('username');
							suggestion.enableMultiple(false);
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="userID">{lang}wcf.user.userID{/lang}</label>
					</div>
					<div class="formField">	
						<input type="text" id="userID" name="userID" value="{$userID}" class="inputText" />
					</div>
				</div>
				
				{if $__wcf->session->getPermission('admin.user.canEditMailAddress')}
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="email">{lang}wcf.user.email{/lang}</label>
						</div>
						<div class="formField">	
							<input type="email" id="email" name="email" value="{$email}" class="inputText" />
						</div>
					</div>
				{/if}
				
				{if $availableGroups|count}
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.user.groups{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.user.groups{/lang}</legend>
								
								<div class="formField">
									{htmlCheckboxes options=$availableGroups name='groupIDs' selected=$groupIDs}
									
									<label style="margin-top: 10px"><input type="checkbox" name="invertGroupIDs" value="1" {if $invertGroupIDs == 1}checked="checked" {/if}/> {lang}wcf.acp.user.groups.invertSearch{/lang}</label>
								</div>
							</fieldset>
						</div>
					</div>
				{/if}
				
				{if $availableLanguages|count > 1}
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.user.language{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.user.language{/lang}</legend>
								
								<div class="formField">
									{htmlCheckboxes options=$availableLanguages name='languageIDs' selected=$languageIDs disableEncoding=true}
								</div>
							</fieldset>
						</div>
					</div>
				{/if}
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
			
			<div class="tabMenuContainer">
				<nav>
					<ul class="tabMenu">
						{if $optionTree|count}<li><a href="#profile">{*<span>*}{lang}wcf.acp.user.search.conditions.profile{/lang}{*</span>*}</a></li>{/if}
						{if $additionalTabs|isset}{@$additionalTabs}{/if}
						<li><a href="#resultOptions">{*<span>*}{lang}wcf.acp.user.search.display{/lang}{*</span>*}</a></li>
					</ul>
				</nav>
				
				{if $optionTree|count}
					<div id="profile" class="border tabMenuContent hidden">
						<div class="container-1">
							<h3 class="subHeading">{lang}wcf.acp.user.search.conditions.profile{/lang}</h3>
							{include file='optionFieldList' langPrefix='wcf.user.option.' options=$optionTree}
						</div>
					</div>
				{/if}
			
				{if $additionalTabContents|isset}{@$additionalTabContents}{/if}
			
				<div id="resultOptions" class="border tabMenuContent hidden">
					<div class="container-1">
						<h3 class="subHeading">{lang}wcf.acp.user.search.display{/lang}</h3>
					
						<fieldset>
							<legend>{lang}wcf.acp.user.search.display.general{/lang}</legend>
						
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="sortField">{lang}wcf.acp.user.search.display.sort{/lang}</label>
								</div>
								<div class="formField">
									<select id="sortField" name="sortField">
										<option value="userID"{if $sortField == 'userID'} selected="selected"{/if}>{lang}wcf.user.userID{/lang}</option>
										<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wcf.user.username{/lang}</option>
										<option value="email"{if $sortField == 'email'} selected="selected"{/if}>{lang}wcf.user.email{/lang}</option>
										<option value="registrationDate"{if $sortField == 'registrationDate'} selected="selected"{/if}>{lang}wcf.user.registrationDate{/lang}</option>
									
										{if $additionalSortFields|isset}{@$additionalSortFields}{/if}
									</select>
									<select id="sortOrder" name="sortOrder">
										<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
										<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
									</select>
								</div>
							</div>
						
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="itemsPerPage">{lang}wcf.acp.user.search.display.itemsPerPage{/lang}</label>
								</div>
								<div class="formField">
									<input type="text" id="itemsPerPage" name="itemsPerPage" value="{@$itemsPerPage}" class="inputText" />
								</div>
							</div>
						</fieldset>
					
						<fieldset>
							<legend>{lang}wcf.acp.user.search.display.columns{/lang}</legend>
						
							{if $optionTree|count}
								<div class="formGroup">
									<div class="formGroupLabel">
										<label>{lang}wcf.acp.user.search.display.columns.profile{/lang}</label>
									</div>
									<div class="formGroupField">
										<fieldset>
											<legend>{lang}wcf.acp.user.search.display.columns.profile{/lang}</legend>
										
											<div class="formField">
												{foreach from=$optionTree item=option}
													<label><input type="checkbox" name="columns[]" value="{$option->optionName}" {if $option->optionName|in_array:$columns}checked="checked" {/if}/> {lang}wcf.user.option.{$option->optionName}{/lang}</label>
												{/foreach}
											</div>
										</fieldset>
									</div>
								</div>
							{/if}
						
							<div class="formGroup">
								<div class="formGroupLabel">
									<label>{lang}wcf.acp.user.search.display.columns.other{/lang}</label>
								</div>
								<div class="formGroupField">
									<fieldset>
										<legend>{lang}wcf.acp.user.search.display.columns.other{/lang}</legend>
									
										<div class="formField">
											<label><input type="checkbox" name="columns[]" value="email" {if "email"|in_array:$columns}checked="checked" {/if}/> {lang}wcf.user.email{/lang}</label>
											<label><input type="checkbox" name="columns[]" value="registrationDate" {if "registrationDate"|in_array:$columns}checked="checked" {/if}/> {lang}wcf.user.registrationDate{/lang}</label>
										
											{if $additionalColumns|isset}{@$additionalColumns}{/if}
										</div>
									</fieldset>
								</div>
							</div>
						
						</fieldset>
					</div>
				</div>
			</div>
			
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}
