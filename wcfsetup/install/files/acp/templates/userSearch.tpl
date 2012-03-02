{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>

<header class="wcf-container wcf-mainHeading">
	<img src="{@$__wcf->getPath()}icon/search1.svg" alt="" class="wcf-containerIcon" />
	<hgroup class="wcf-containerContent">
		<h1>{lang}wcf.acp.user.search{/lang}</h1>
	</hgroup>
</header>

{if $errorField == 'search'}
	<p class="wcf-error">{lang}wcf.acp.user.search.error.noMatches{/lang}</p>
{/if}

{*if $deletedUsers}
	<p class="wcf-success">{lang}wcf.acp.user.delete.success{/lang}</p>	
{elseif $deletedUsers === 0}
	<p class="wcf-error">{lang}wcf.acp.user.delete.error{/lang}</p>	
{/if*}

<div class="wcf-contentHeader">
	<nav>
		<ul class="wcf-largeButtons">
			{if $__wcf->session->getPermission('admin.user.canAddUser')}
				<li><a href="{link controller='UserAdd'}{/link}" title="{lang}wcf.acp.user.add{/lang}" class="wcf-button"><img src="{@$__wcf->getPath()}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='UserList'}{/link}" title="{lang}wcf.acp.menu.link.user.list{/lang}" class="wcf-button"><img src="{@$__wcf->getPath()}icon/users1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.user.list{/lang}</span></a></li>
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='UserSearch'}{/link}">
	<div class="wcf-border wcf-content">
		
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
							
							<!-- ToDo --><label style="margin-top: 10px"><input type="checkbox" name="invertGroupIDs" value="1" {if $invertGroupIDs == 1}checked="checked" {/if}/> {lang}wcf.acp.user.groups.invertSearch{/lang}</label>
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
		
		{event name='fieldsets'}
		
		<div class="wcf-tabMenuContainer">
			<nav class="wcf-tabMenu">
				<ul>
					{if $optionTree|count}
						<li><a href="#profile">{*<span>*}{lang}wcf.acp.user.search.conditions.profile{/lang}{*</span>*}</a></li>
					{/if}
					
					{event name='tabMenuTabs'}
					
					<li><a href="#resultOptions">{*<span>*}{lang}wcf.acp.user.search.display{/lang}{*</span>*}</a></li>
				</ul>
			</nav>
			
			{if $optionTree|count}
				<div id="profile" class="wcf-border wcf-tabMenuContent hidden">
					<div>
						<h3 class="wcf-subHeading">{lang}wcf.acp.user.search.conditions.profile{/lang}</h3>
						{include file='optionFieldList' langPrefix='wcf.user.option.' options=$optionTree}
					</div>
				</div>
			{/if}
		
			{event name='tabMenuContent'}
		
			<div id="resultOptions" class="wcf-border wcf-tabMenuContent hidden">
				<hgroup class="wcf-subHeading">
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
	
	<div class="wcf-formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}
