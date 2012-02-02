{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();

		var $availableLanguages = { {implode from=$availableLanguages key=languageID item=languageName}{@$languageID}: '{$languageName}'{/implode} };
		var $groupNameValues = { {implode from=$i18nValues['groupName'] key=languageID item=value}'{@$languageID}': '{$value}'{/implode} };
		new WCF.MultipleLanguageInput('groupName', false, $groupNameValues, $availableLanguages);
	});
	//]]>
</script>

<header class="wcf-mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/{@$action}1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.group.{@$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="wcf-error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $warningSelfEdit|isset}
	<p class="wcf-warning">{lang}wcf.acp.group.edit.warning.selfIsMember{/lang}</p>	
{/if}

{if $success|isset}
	<p class="wcf-success">{lang}wcf.global.form.{@$action}.success{/lang}</p>	
{/if}

<div class="wcf-contentHeader">
	<nav>
		<ul class="largeButtons">
			<li><a href="{link controller='UserGroupList'}{/link}" title="{lang}wcf.acp.menu.link.group.list{/lang}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/users1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.group.list{/lang}</span></a></li>
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='UserGroupAdd'}{/link}{else}{link controller='UserGroupEdit'}{/link}{/if}">
	<div class="wcf-border wcf-content">
		
		<fieldset>
			<legend>{lang}wcf.acp.group.data{/lang}</legend>
			
			<dl{if $errorType.groupName|isset} class="formError"{/if}>
				<dt><label for="groupName">{lang}wcf.acp.group.groupName{/lang}</label></dt>
				<dd>
					<input type="text" id="groupName" name="groupName" value="{$i18nPlainValues['groupName']}" class="medium" />
					{if $errorType.groupName|isset}
						<small class="wcf-innerError">
							{if $errorType.groupName == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.group.groupName.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.group.groupName.description{/lang}</small>
				</dd>
			</dl>
			
			{event name='dataFields'}
		</fieldset>
		
		{event name='fieldsets'}
		
		<div class="tabMenuContainer" data-active="{$activeMenuItem}" data-store="activeTabMenuItem">
			<nav class="tabMenu">
				<ul>
					{foreach from=$optionTree item=categoryLevel1}
						<li><a href="#{@$categoryLevel1[object]->categoryName}">{lang}wcf.acp.group.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</a></li>
					{/foreach}
				</ul>
			</nav>
			
			{foreach from=$optionTree item=categoryLevel1}
				<div id="{@$categoryLevel1[object]->categoryName}" class="tabMenuContainer border tabMenuContent" data-active="{$activeTabMenuItem}" data-store="activeMenuItem">
					<nav class="menu">
						<ul>
							{foreach from=$categoryLevel1[categories] item=$categoryLevel2}
								<li><a href="#{@$categoryLevel1[object]->categoryName}-{@$categoryLevel2[object]->categoryName}">{lang}wcf.acp.group.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</a></li>
							{/foreach}
						</ul>
					</nav>
					
					{foreach from=$categoryLevel1[categories] item=categoryLevel2}
						<div id="{@$categoryLevel1[object]->categoryName}-{@$categoryLevel2[object]->categoryName}" class="hidden">
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
										{hascontent}<p class="description">{content}{lang}wcf.acp.group.option.category.{@$categoryLevel3[object]->categoryName}.description{/lang}{/content}</p>{/hascontent}
								
										<div>
											{include file='optionFieldList' options=$categoryLevel3[options] langPrefix='wcf.acp.group.option.'}
										</div>
									</fieldset>
								{/foreach}
							{/if}
							
						</div>
					{/foreach}
				</div>
			{/foreach}
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="action" value="{@$action}" />
 		{if $groupID|isset}<input type="hidden" name="id" value="{@$groupID}" />{/if}
 		<input type="hidden" id="activeTabMenuItem" name="activeTabMenuItem" value="{$activeTabMenuItem}" />
 		<input type="hidden" id="activeMenuItem" name="activeMenuItem" value="{$activeMenuItem}" />
 	</div>
</form>

{include file='footer'}
