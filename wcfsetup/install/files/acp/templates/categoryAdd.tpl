{include file='header'}

{if $aclObjectTypeID}
	{include file='aclPermissions'}
	
	{if !$category|isset}
		{include file='shared_aclPermissionJavaScript' containerID='groupPermissions' objectTypeID=$aclObjectTypeID}
	{else}
		{include file='shared_aclPermissionJavaScript' containerID='groupPermissions' objectTypeID=$aclObjectTypeID objectID=$category->categoryID}
	{/if}
{/if}

{include file='shared_multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}
{if $objectType->getProcessor()->hasDescription()}
	{include file='shared_multipleLanguageInputJavascript' elementIdentifier='description' forceSelection=false}
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{@$objectType->getProcessor()->getLanguageVariable($action)}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $action == 'edit' && $availableCategories->hasChildren()}
						<li class="dropdown">
							<a class="button dropdownToggle">{icon name='sort'} <span>{@$objectType->getProcessor()->getLanguageVariable('button.choose')}</span></a>
							<div class="dropdownMenu">
								<ul class="scrollableDropdownMenu">
									{foreach from=$availableCategories item='availableCategory'}
										<li{if $availableCategory->categoryID == $category->categoryID} class="active"{/if}><a href="{link controller=$editController application=$objectType->getProcessor()->getApplication() object=$availableCategory}{/link}">{section name=i loop=$availableCategories->getDepth()}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$availableCategory->getTitle()}</a></li>
									{/foreach}
								</ul>
							</div>
						</li>
					{/if}
					
					{if $objectType->getProcessor()->canDeleteCategory() || $objectType->getProcessor()->canEditCategory()}
						<li><a href="{link controller=$listController application=$objectType->getProcessor()->getApplication()}{/link}" class="button">{icon name='list'} <span>{@$objectType->getProcessor()->getLanguageVariable('button.list')}</span></a></li>
					{/if}
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller=$addController application=$objectType->getProcessor()->getApplication()}{/link}{else}{link controller=$editController application=$objectType->getProcessor()->getApplication() object=$category}{/link}{/if}">
	{event name='beforeSections'}
		
	<div class="section">
		<dl{if $errorField == 'title'} class="formError"{/if}>
			<dt><label for="title">{@$objectType->getProcessor()->getLanguageVariable('title')}</label></dt>
			<dd>
				<input type="text" id="title" name="title" value="{$i18nPlainValues['title']}" class="long">
				{if $errorField == 'title'}
					<small class="innerError">
						{if $errorType == 'empty' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{$errorType}{/lang}
						{else}
							{assign var=__languageVariable value='title.error.'|concat:$errorType}
							{@$objectType->getProcessor()->getLanguageVariable($__languageVariable)}
						{/if}
					</small>
				{/if}
				{hascontent}<small>{content}{@$objectType->getProcessor()->getLanguageVariable('title.description', true)}{/content}</small>{/hascontent}
			</dd>
		</dl>
		
		{if $objectType->getProcessor()->hasDescription()}
			<dl{if $errorField == 'description'} class="formError"{/if}>
				<dt><label for="description">{@$objectType->getProcessor()->getLanguageVariable('description')}</label></dt>
				<dd>
					<textarea cols="40" rows="10" id="description" name="description">{$i18nPlainValues['description']}</textarea>
					{if $errorField == 'description'}
						<small class="innerError">
							{if $errorType == 'empty' || $errorType == 'multilingual'}
								{lang}wcf.global.form.error.{$errorType}{/lang}
							{else}
								{assign var=__languageVariable value='description.error.'|concat:$errorType}
								{@$objectType->getProcessor()->getLanguageVariable($__languageVariable)}
							{/if}
						</small>
					{/if}
					{hascontent}<small>{content}{@$objectType->getProcessor()->getLanguageVariable('description.description', true)}{/content}</small>{/hascontent}
				</dd>
			</dl>
			
			{if $objectType->getProcessor()->supportsHtmlDescription()}
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="descriptionUseHtml" value="1"{if $descriptionUseHtml} checked{/if}> {@$objectType->getProcessor()->getLanguageVariable('descriptionUseHtml')}</label>
					</dd>
				</dl>
			{/if}
		{/if}
		
		<dl{if $errorField == 'isDisabled'} class="formError"{/if}>
			<dt></dt>
			<dd>
				<label><input type="checkbox" id="isDisabled" name="isDisabled"{if $isDisabled} checked{/if}> {@$objectType->getProcessor()->getLanguageVariable('isDisabled')}</label>
				{hascontent}<small>{content}{@$objectType->getProcessor()->getLanguageVariable('isDisabled.description', true)}{/content}</small>{/hascontent}
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	<section class="section">
		<h2 class="sectionTitle">{@$objectType->getProcessor()->getLanguageVariable('position')}</h2>
		
		{if $categoryNodeList->hasChildren() && $objectType->getProcessor()->getMaximumNestingLevel()}
			<dl{if $errorField == 'parentCategoryID'} class="formError"{/if}>
				<dt><label for="parentCategoryID">{@$objectType->getProcessor()->getLanguageVariable('parentCategoryID')}</label></dt>
				<dd>
					<select id="parentCategoryID" name="parentCategoryID">
						<option value="0">{lang}wcf.global.noSelection{/lang}</option>
						{include file='shared_categoryOptionList' categoryID=$parentCategoryID maximumNestingLevel=$objectType->getProcessor()->getMaximumNestingLevel()}
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
		
		<dl{if $errorField == 'showOrder'} class="formError"{/if}>
			<dt><label for="showOrder">{lang}wcf.global.showOrder{/lang}</label></dt>
			<dd>
				<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" min="0" class="short">
				{if $errorField == 'showOrder'}
					<small class="innerError">
						{assign var=__languageVariable value='showOrder.error.'|concat:$errorType}
						{@$objectType->getProcessor()->getLanguageVariable($__languageVariable)}
					</small>
				{/if}
				{hascontent}<small>{content}{@$objectType->getProcessor()->getLanguageVariable('showOrder.description', true)}{/content}</small>{/hascontent}
			</dd>
		</dl>
		
		{event name='positionFields'}
	</section>
	
	{if $aclObjectTypeID}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acl.permissions{/lang}</h2>
			
			<dl id="groupPermissions" class="wide">
				<dt>{lang}wcf.acl.permissions{/lang}</dt>
				<dd></dd>
			</dl>
			
			{event name='permissionFields'}
		</section>
	{/if}
	
	{event name='afterSections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
