{include file='header' pageTitle='wcf.acp.user.option.category.'|concat:$action}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.option.category.{$action}{/lang}</h1>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='UserOptionCategoryList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.user.option.category.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='UserOptionCategoryAdd'}{/link}{else}{link controller='UserOptionCategoryEdit' id=$categoryID}{/link}{/if}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.global.form.data{/lang}</legend>
			
			<dl{if $errorField == 'categoryName'} class="formError"{/if}>
				<dt><label for="categoryName">{lang}wcf.global.name{/lang}</label></dt>
				<dd>
					<input type="text" id="categoryName" name="categoryName" value="{$categoryName}" required="required" autofocus="autofocus" class="long" />
					{if $errorField == 'categoryName'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
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
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>


{include file='footer'}
