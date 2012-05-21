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
		<h1>{hascontent}{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.{@$action}{/lang}{/content}{hascontentelse}{lang}wcf.category.{@$action}{/lang}{/hascontent}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.form.{@$action}.success{/lang}</p>	
{/if}

{capture assign='listLangVar'}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.button.list{/lang}{/capture}
{if !$listLangVar}
	{capture assign='listLangVar'}{lang}wcf.category.button.list{/lang}{/capture}
{/if}

{hascontent}
	<div class="contentNavigation">
		<nav>
			<ul>
				{content}
					{if $objectType->getProcessor()->canDeleteCategory() || $objectType->getProcessor()->canEditCategory()}
						<li><a href="{link controller=$listController}{/link}" title="{$listLangVar}" class="button"><img src="{@$__wcf->getPath()}icon/list.svg" alt="" class="icon24" /> <span>{@$listLangVar}</span></a></li>
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
			<legend>{hascontent}{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.data{/lang}{/content}{hascontentelse}{lang}wcf.category.data{/lang}{/hascontent}</legend>
			
			{if $categoryNodeList|count}
				<dl{if $errorField == 'parentCategoryID'} class="formError"{/if}>
					<dt><label for="parentCategoryID">{hascontent}{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.parentCategoryID{/lang}{/content}{hascontentelse}{lang}wcf.category.parentCategoryID{/lang}{/hascontent}</label></dt>
					<dd>
						<select id="parentCategoryID" name="parentCategoryID">
							<option value="0"></option>
							{include file='categoryOptionList' categoryID=$parentCategoryID}
						</select>
						{if $errorField == 'parentCategoryID'}
							<small class="innerError">
								{hascontent}{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.parentCategoryID.error.{@$errorType}{/lang}{/content}{hascontentelse}{lang}wcf.category.parentCategoryID.error.{@$errorType}{/lang}{/hascontent}
							</small>
						{/if}
						{hascontent}<small>{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.parentCategoryID.description{/lang}{/content}</small>{/hascontent}
					</dd>
				</dl>
			{/if}
			
			<dl{if $errorField == 'title'} class="formError"{/if}>
				<dt><label for="title">{hascontent}{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.title{/lang}{/content}{hascontentelse}{lang}wcf.category.title{/lang}{/hascontent}</label></dt>
				<dd>
					<input type="text" id="title" name="title" value="{$i18nPlainValues['title']}" class="long" />
					{if $errorField == 'title'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}{@$objectType->getProcessor()->getLangVarPrefix()}.title.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					{hascontent}<small>{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.title.description{/lang}{/content}</small>{/hascontent}
				</dd>
			</dl>
			
			<dl{if $errorField == 'description'} class="formError"{/if}>
				<dt><label for="description">{hascontent}{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.description{/lang}{/content}{hascontentelse}{lang}wcf.category.description{/lang}{/hascontent}</label></dt>
				<dd>
					<textarea cols="40" rows="10" id="description" name="description">{$i18nPlainValues['description']}</textarea>
					{if $errorType == 'description'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}{@$objectType->getProcessor()->getLangVarPrefix()}.description.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					{hascontent}<small>{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.description.description{/lang}{/content}</small>{/hascontent}
				</dd>
			</dl>
			
			<dl{if $errorField == 'isDisabled'} class="formError"{/if}>
				<dt class="reversed"><label for="isDisabled">{hascontent}{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.isDisabled{/lang}{/content}{hascontentelse}{lang}wcf.category.isDisabled{/lang}{/hascontent}</label></dt>
				<dd>
					<input type="checkbox" id="isDisabled" name="isDisabled"{if $isDisabled} checked="checked"{/if} />
					{hascontent}<small>{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.isDisabled.description{/lang}{/content}</small>{/hascontent}
				</dd>
			</dl>
			
			<dl{if $errorField == 'showOrder'} class="formError"{/if}>
				<dt><label for="showOrder">{hascontent}{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.showOrder{/lang}{/content}{hascontentelse}{lang}wcf.category.showOrder{/lang}{/hascontent}</label></dt>
				<dd>
					<input type="text" id="showOrder" name="showOrder" value="{$showOrder}" class="short" />
					{if $errorField == 'title'}
						<small class="innerError">
							{lang}{@$objectType->getProcessor()->getLangVarPrefix()}.showOrder.error.{@$errorType}{/lang}
						</small>
					{/if}
					{hascontent}<small>{content}{lang __optional=true}{@$objectType->getProcessor()->getLangVarPrefix()}.showOrder.description{/lang}{/content}</small>{/hascontent}
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