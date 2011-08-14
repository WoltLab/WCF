{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabMenu.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var tabMenu = new TabMenu();
	onloadEvents.push(function() { tabMenu.showSubTabMenu("{$activeTabMenuItem}", "{$activeSubTabMenuItem}"); });
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/userGroup{@$action|ucfirst}L.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.group.{@$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.group.{@$action}.success{/lang}</p>	
{/if}

{if $warningSelfEdit|isset}
	<p class="warning">{lang}wcf.acp.group.edit.warning.selfIsMember{/lang}</p>	
{/if}

<div class="contentHeader">
	<nav class="largeButtons">
		<ul>
			<li><a href="index.php?page=UserGroupList{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.group.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/userGroupM.png" alt="" /> <span>{lang}wcf.acp.menu.link.group.view{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

<form method="post" action="index.php?form=UserGroup{@$action|ucfirst}">
	<div class="border content">
		{if $action == 'add' || $additionalFields|isset}
			<fieldset>
				<legend>{lang}wcf.acp.group.data{/lang}</legend>

				{if $action == 'add'}
					<dl id="groupIdentifierDiv"{if $errorType.groupIdentifier|isset} class="formError"{/if}>
						<dt><label for="groupIdentifier">{lang}wcf.acp.group.groupIdentifier{/lang}</label></dt>
						<dd>
							<input type="text" id="groupIdentifier" name="groupIdentifier" value="{$groupIdentifier}" class="medium" />
							{if $errorType.groupIdentifier|isset}
								<small class="innerError">
									{if $errorType.groupIdentifier == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									{if $errorType.groupIdentifier == 'notValid'}{lang}wcf.acp.group.groupIdentifier.error.notValid{/lang}{/if}
									{if $errorType.groupIdentifier == 'notUnique'}{lang}wcf.acp.group.groupIdentifier.error.notUnique{/lang}{/if}
								</small>
							{/if}
							<small id="groupIdentifierHelpMessage">{lang}wcf.acp.group.groupIdentifier.description{/lang}</small>
						</dd>
					</dl>
				{/if}

				{if $additionalFields|isset}{@$additionalFields}{/if}
			</fieldset>
		{/if}

		<fieldset>
			<legend>{lang}wcf.acp.group.groupName{/lang}</legend>

			{* TODO: add some javascript magic maybe *}
			{foreach from=$languageCodes key=key item=language}
				<dl{if $errorType.groupName.$key|isset} class="formError"{/if}>
					<dt><label for="groupName{@$language|ucfirst}">{lang}wcf.global.language.{@$language}{/lang}</label></dt>
					<dd>
						<input type="text" id="groupName{@$language|ucfirst}" name="groupName[{$key}]" value="{if $groupName.$key|isset}{$groupName.$key}{/if}" class="medium" />
						{if $errorType.groupName.$key|isset}
							<small class="innerError">
								{if $errorType.groupName.$key == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
			{/foreach}	
		</fieldset>
	
		{if $additionalFieldSets|isset}{@$additionalFieldSets}{/if}
		
		<nav>
			<ul class="tabMenu">
				{foreach from=$optionTree item=categoryLevel1}
					<li id="{@$categoryLevel1[object]->categoryName}"><a onclick="tabMenu.showSubTabMenu('{@$categoryLevel1[object]->categoryName}');"><span>{lang}wcf.acp.group.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</span></a></li>
				{/foreach}
			</ul>
		</nav>
		
		<nav class="menu"><!-- ToDo -->
			{foreach from=$optionTree item=categoryLevel1}
				<ul id="{@$categoryLevel1[object]->categoryName}-categories" class="hidden">
					{foreach from=$categoryLevel1[categories] item=categoryLevel2}
						<li id="{@$categoryLevel1[object]->categoryName}-{@$categoryLevel2[object]->categoryName}"><a onclick="tabMenu.showTabMenuContent('{@$categoryLevel1[object]->categoryName}-{@$categoryLevel2[object]->categoryName}');"><span>{lang}wcf.acp.group.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</span></a></li>
					{/foreach}
				</ul>
			{/foreach}
		</nav>
		
		{foreach from=$optionTree item=categoryLevel1}
			{foreach from=$categoryLevel1[categories] item=categoryLevel2}
				<div id="{@$categoryLevel1[object]->categoryName}-{@$categoryLevel2[object]->categoryName}-content" class="border tabMenuContent hidden">
					<hgroup class="subHeading">
						<h1>{lang}wcf.acp.group.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</h1>
						<h2>{lang}wcf.acp.group.option.category.{@$categoryLevel2[object]->categoryName}.description{/lang}</h2>
					</hgroup>
					
					{if $categoryLevel2[options]|count}
						{include file='optionFieldList' options=$categoryLevel2[options] langPrefix='wcf.acp.group.option.'}
					{/if}
					
					{if $categoryLevel2[categories]|count}
						{foreach from=$categoryLevel2[categories] item=categoryLevel3}
							<fieldset>
								<legend>{lang}wcf.acp.group.option.category.{@$categoryLevel3[object]->categoryName}{/lang}</legend>
								<p class="description">{lang}wcf.acp.group.option.category.{@$categoryLevel3[object]->categoryName}.description{/lang}</p>
							
								<div>
									{include file='optionFieldList' options=$categoryLevel3[options] langPrefix='wcf.acp.group.option.'}
								</div>
							</fieldset>
						{/foreach}
					{/if}
					
				</div>
			{/foreach}
		{/foreach}
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="action" value="{@$action}" />
 		{if $groupID|isset}<input type="hidden" name="groupID" value="{@$groupID}" />{/if}
 		<input type="hidden" id="activeTabMenuItem" name="activeTabMenuItem" value="{$activeTabMenuItem}" />
 		<input type="hidden" id="activeSubTabMenuItem" name="activeSubTabMenuItem" value="{$activeSubTabMenuItem}" />
 	</div>
</form>

{include file='footer'}
