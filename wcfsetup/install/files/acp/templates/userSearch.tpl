{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/search1.svg" alt="" />
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
		
		<fieldset>
			<legend>{lang}wcf.acp.user.search.conditions.general{/lang}</legend>
			
			<dl>
				<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" class="medium" />
					<script type="text/javascript">
						//<![CDATA[
						var suggestion = new Suggestion('username');
						suggestion.enableMultiple(false);
						//]]>
					</script>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="userID">{lang}wcf.user.userID{/lang}</label></dt>
				<dd>	
					<input type="text" id="userID" name="userID" value="{$userID}" class="short" />
				</dd>
			</dl>
			
			{if $__wcf->session->getPermission('admin.user.canEditMailAddress')}
				<dl>
					<dt><label for="email">{lang}wcf.user.email{/lang}</label></dt>
					<dd>
						<input type="email" id="email" name="email" value="{$email}" class="medium" />
					</dd>
				</dl>
			{/if}
			
			{if $availableGroups|count}
				<dl>
					<dt>
						<label>{lang}wcf.acp.user.groups{/lang}</label>
					</dt>
					<dd>
						<fieldset>
							<legend>{lang}wcf.acp.user.groups{/lang}</legend>
							
							<dl>
								<dd>{htmlCheckboxes options=$availableGroups name='groupIDs' selected=$groupIDs}</dd>
							</dl>
							
							<label style="margin-top: 10px"><input type="checkbox" name="invertGroupIDs" value="1" {if $invertGroupIDs == 1}checked="checked" {/if}/> {lang}wcf.acp.user.groups.invertSearch{/lang}</label>
						</fieldset>
					</dd>
				</dl>
			{/if}
			
			{if $availableLanguages|count > 1}
				<dl>
					<dt>
						<label>{lang}wcf.user.language{/lang}</label>
					</dt>
					<dd>
						<fieldset>
							<legend>{lang}wcf.acp.user.language{/lang}</legend>
							
							<dl>
								<dd>{htmlCheckboxes options=$availableLanguages name='languageIDs' selected=$languageIDs disableEncoding=true}</dd>
							</dl>
						</fieldset>
					</dd>
				</dl>
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
				<hgroup class="subHeading">
					<h1>{lang}wcf.acp.user.search.display{/lang}</h1>
				</hgroup>
				
				<fieldset>
					<legend>{lang}wcf.acp.user.search.display.general{/lang}</legend>
				
					<dl>
						<dt><label for="sortField">{lang}wcf.acp.user.search.display.sort{/lang}</label></dt>
						<dd>
							<select id="sortField" name="sortField">
								<option value="userID"{if $sortField == 'userID'} selected="selected"{/if}>{lang}wcf.user.userID{/lang}</option>
								<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wcf.user.username{/lang}</option>
								<option value="email"{if $sortField == 'email'} selected="selected"{/if}>{lang}wcf.user.email{/lang}</option>
								<option value="registrationDate"{if $sortField == 'registrationDate'} selected="selected"{/if}>{lang}wcf.user.registrationDate{/lang}</option>
							
								{if $additionalSortFields|isset}{@$additionalSortFields}{/if}
							</select>
						</dd>
						<dd>
							<select id="sortOrder" name="sortOrder">
								<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
								<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
							</select>
						</dd>
					</dl>
				
					<dl>
						<dt><label for="itemsPerPage">{lang}wcf.acp.user.search.display.itemsPerPage{/lang}</label></dt>
						<dd>
							<input type="text" id="itemsPerPage" name="itemsPerPage" value="{@$itemsPerPage}" class="short" />
						</dd>
					</dl>
				</fieldset>
			
				<fieldset>
					<legend>{lang}wcf.acp.user.search.display.columns{/lang}</legend>
				
					{if $optionTree|count}
						<dl>
							<dt>
								<label>{lang}wcf.acp.user.search.display.columns.profile{/lang}</label>
							</dt>
							<dd>
								<fieldset>
									<legend>{lang}wcf.acp.user.search.display.columns.profile{/lang}</legend>
									
									<dl>
										<dd>
											{foreach from=$optionTree item=option}
												<label><input type="checkbox" name="columns[]" value="{$option->optionName}" {if $option->optionName|in_array:$columns}checked="checked" {/if}/> {lang}wcf.user.option.{$option->optionName}{/lang}</label>
											{/foreach}
										<dd>
									</dl>
								</fieldset>
							</dd>
						</dl>
					{/if}
				
					<dl>
						<dt><label>{lang}wcf.acp.user.search.display.columns.other{/lang}</label></dt>
						<dd>
							<fieldset>
								<legend>{lang}wcf.acp.user.search.display.columns.other{/lang}</legend>
							
								<dl>
									<dd>
										<label><input type="checkbox" name="columns[]" value="email" {if "email"|in_array:$columns}checked="checked" {/if}/> {lang}wcf.user.email{/lang}</label></dd>
										<label><input type="checkbox" name="columns[]" value="registrationDate" {if "registrationDate"|in_array:$columns}checked="checked"{/if}/> {lang}wcf.user.registrationDate{/lang}</label>
									</dd>
									
									{if $additionalColumns|isset}{@$additionalColumns}{/if}
								</dl>
							</fieldset>
						</dd>
					</dl>
				
				</fieldset>
				
			</div>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}
