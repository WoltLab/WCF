{include file='header' pageTitle='wcf.acp.user.option.category.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.option.category.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='UserOptionCategoryList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.user.option.category.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>

</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='UserOptionCategoryAdd'}{/link}{else}{link controller='UserOptionCategoryEdit' id=$categoryID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'categoryName'} class="formError"{/if}>
			<dt><label for="categoryName">{lang}wcf.global.name{/lang}</label></dt>
			<dd>
				<input type="text" id="categoryName" name="categoryName" value="{$i18nPlainValues['categoryName']}" required="required" autofocus="autofocus" class="long" />
				{if $errorField == 'categoryName'}
					<small class="innerError">
						{if $errorType == 'multilingual'}
							{lang}wcf.global.form.error.multilingual{/lang}
						{else}
							{lang}wcf.acp.user.option.category.name.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		{include file='multipleLanguageInputJavascript' elementIdentifier='categoryName' forceSelection=true}
		
		<dl>
			<dt><label for="showOrder">{lang}wcf.acp.user.option.category.showOrder{/lang}</label></dt>
			<dd>
				<input type="number" id="showOrder" name="showOrder" value="{@$showOrder}" class="short" />
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
