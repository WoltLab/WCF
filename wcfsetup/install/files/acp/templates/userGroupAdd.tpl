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
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.group.data{/lang}</legend>
				
				<dl id="groupNameDiv"{if $errorType.groupName|isset} class="formError"{/if}>
					<dt><label for="groupName">{lang}wcf.acp.group.groupName{/lang}</label></dt>
					<dd>
						<input type="text" id="groupName" name="groupName" value="{$groupName}" class="medium" />
						{if $errorType.groupName|isset}
							<small class="innerError">
								{if $errorType.groupName == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</small>
						{/if}
						<small id="groupNameHelpMessage">{lang}wcf.acp.group.groupName.description{/lang}</small>
					</dd>
				</dl>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</fieldset>
		
			{if $additionalFieldSets|isset}{@$additionalFieldSets}{/if}
			
			<nav>
				<ul class="tabMenu">
					{foreach from=$optionTree item=categoryLevel1}
						<li id="{@$categoryLevel1[object]->categoryName}"><a onclick="tabMenu.showSubTabMenu('{@$categoryLevel1[object]->categoryName}');"><span>{lang}wcf.acp.group.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</span></a></li>
					{/foreach}
				</ul>
			</nav>
			
			<nav class="menu"><!-- ToDo: Border missing -->
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
						<div class="container-1">
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
					</div>
				{/foreach}
			{/foreach}
		</div>
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
