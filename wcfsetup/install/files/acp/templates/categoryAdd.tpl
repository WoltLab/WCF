{include file='header'}

{if $aclObjectTypeID}
	{include file='aclPermissions'}
{/if}
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		{if $aclObjectTypeID}
			new WCF.ACL.List($('#groupPermissions'), {@$aclObjectTypeID}{if $category|isset}, '', {@$category->categoryID}{/if});
		{/if}
		
		var $availableLanguages = { {implode from=$availableLanguages key=languageID item=languageName}{@$languageID}: '{$languageName}'{/implode} };
		
		var $titleValues = { {implode from=$i18nValues['title'] key=languageID item=value}'{@$languageID}': '{$value}'{/implode} };
		new WCF.MultipleLanguageInput('title', false, $titleValues, $availableLanguages);
		
		var $descriptionValues = { {implode from=$i18nValues['description'] key=languageID item=value}'{@$languageID}': '{$value}'{/implode} };
		new WCF.MultipleLanguageInput('description', false, $descriptionValues, $availableLanguages);
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{@$objectType->getProcessor()->getLanguageVariable($action)}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.form.{@$action}.success{/lang}</p>	
{/if}

{hascontent}
	<div class="contentNavigation">
		<nav>
			<ul>
				{content}
					{if $objectType->getProcessor()->canDeleteCategory() || $objectType->getProcessor()->canEditCategory()}
						<li><a href="{link controller=$listController}{/link}" title="{$objectType->getProcessor()->getLanguageVariable('button.list')}" class="button"><img src="{@$__wcf->getPath()}icon/list.svg" alt="" class="icon24" /> <span>{@$objectType->getProcessor()->getLanguageVariable('button.list')}</span></a></li>
					{/if}
					
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	</div>
{/hascontent}

<form method="post" action="{if $action == 'add'}{link controller=$addController}{/link}{else}{link controller=$editController id=$category->categoryID title=$category->getTitle()}{/link}{/if}">
	<div class="container containerPadding marginTop shadow">
		<fieldset>
			<legend>{@$objectType->getProcessor()->getLanguageVariable('data')}</legend>
			
			{if $categoryNodeList|count}
				<dl{if $errorField == 'parentCategoryID'} class="formError"{/if}>
					<dt><label for="parentCategoryID">{@$objectType->getProcessor()->getLanguageVariable('parentCategoryID')}</label></dt>
					<dd>
						<select id="parentCategoryID" name="parentCategoryID">
							<option value="0"></option>
							{include file='categorySelectList' categoryID=$parentCategoryID}
						</select>
						{if $errorField == 'parentCategoryID'}
							<small class="innerError">
								{assign var=__languageVariable value='parentCategoryID.error.'|concat:$errorType}
								{@$objectType->getProcessor()->getLanguageVariable($__languageVariable)}
							</small>
						{/if}
						{hascontent}<small>{content}{@$objectType->getProcessor()->getLanguageVariable('parentCategoryID.description', true)}{/content}</small>{/hascontent}
					</dd>
				</dl>
			{/if}
			
			<dl{if $errorField == 'title'} class="formError"{/if}>
				<dt><label for="title">{@$objectType->getProcessor()->getLanguageVariable('title')}</label></dt>
				<dd>
					<input type="text" id="title" name="title" value="{$i18nPlainValues['title']}" class="long" />
					{if $errorField == 'title'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{assign var=__languageVariable value='title.error.'|concat:$errorType}
								{@$objectType->getProcessor()->getLanguageVariable($__languageVariable)}
							{/if}
						</small>
					{/if}
					{hascontent}<small>{content}{@$objectType->getProcessor()->getLanguageVariable('title.description', true)}{/content}</small>{/hascontent}
				</dd>
			</dl>
			
			<dl{if $errorField == 'description'} class="formError"{/if}>
				<dt><label for="description">{@$objectType->getProcessor()->getLanguageVariable('description')}</label></dt>
				<dd>
					<textarea cols="40" rows="10" id="description" name="description">{$i18nPlainValues['description']}</textarea>
					{if $errorType == 'description'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{assign var=__languageVariable value='description.error.'|concat:$errorType}
								{@$objectType->getProcessor()->getLanguageVariable($__languageVariable)}
							{/if}
						</small>
					{/if}
					{hascontent}<small>{content}{@$objectType->getProcessor()->getLanguageVariable('description.description', true)}{/content}</small>{/hascontent}
				</dd>
			</dl>
			
			<dl{if $errorField == 'isDisabled'} class="formError"{/if}>
				<dt class="reversed"><label for="isDisabled">{@$objectType->getProcessor()->getLanguageVariable('isDisabled')}</label></dt>
				<dd>
					<input type="checkbox" id="isDisabled" name="isDisabled"{if $isDisabled} checked="checked"{/if} />
					{hascontent}<small>{content}{@$objectType->getProcessor()->getLanguageVariable('isDisabled.description', true)}{/content}</small>{/hascontent}
				</dd>
			</dl>
			
			<dl{if $errorField == 'showOrder'} class="formError"{/if}>
				<dt><label for="showOrder">{@$objectType->getProcessor()->getLanguageVariable('showOrder')}</label></dt>
				<dd>
					<input type="text" id="showOrder" name="showOrder" value="{$showOrder}" class="short" />
					{if $errorField == 'showOrder'}
						<small class="innerError">
							{assign var=__languageVariable value='showOrder.error.'|concat:$errorType}
							{@$objectType->getProcessor()->getLanguageVariable($__languageVariable)}
						</small>
					{/if}
					{hascontent}<small>{content}{@$objectType->getProcessor()->getLanguageVariable('showOrder.description', true)}{/content}</small>{/hascontent}
				</dd>
			</dl>
			
			{if $aclObjectTypeID}
				<dl id="groupPermissions">
					<dt>{lang}wcf.acp.acl.permissions{/lang}</dt>
					<dd></dd>
				</dl>
			{/if}
			
			{event name='fields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
 	</div>
</form>

{include file='footer'}