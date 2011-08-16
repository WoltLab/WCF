{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Menu();
	});
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/{@$action}1.svg" alt="" />
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
		
		{*
			Note:
			
			 - Remove inline css (relocate to global WCF-CSS)
			 - [.menuItems] is used by JS to determine container and additionally (!) applying [.menuItemsJS]
			 - [.menuItemsJS] is applied with JavaScript, do NOT copy css instructions into the non-js class,
			   as certain dimension calculations will be broken after applying them

			 - JavaScript is provided within wcf/js/WCF.js, search for WCF.Menu

		*}
		<style type="text/css">
			.scrollableMenuContainer {
				overflow: hidden;
				position: relative;
			}

			.menuItemsJS {
				overflow: hidden;
				position: relative;
				width: 20000em;
			}

			.menuItemsJS > div {
				float: left;
				margin-top: 0 !important;
			}
		</style>

		{foreach from=$optionTree item=categoryLevel1}
			<div class="scrollableMenuContainer" data-categoryName="{$categoryLevel1[object]->categoryName}">
				<nav class="menu">
					<ul id="{@$categoryLevel1[object]->categoryName}-categories">
						{foreach from=$categoryLevel1[categories] item=$categoryLevel2}
							<li id="{@$categoryLevel1[object]->categoryName}-{@$categoryLevel2[object]->categoryName}"><a href="#{@$categoryLevel1[object]->categoryName}-{@$categoryLevel2[object]->categoryName}-content"><span>{lang}wcf.acp.group.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</span></a></li>
						{/foreach}
					</ul>
				</nav>

				<div class="menuItems" id="{$categoryLevel1[object]->categoryName}-items">
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
				</div>
			</div>
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
