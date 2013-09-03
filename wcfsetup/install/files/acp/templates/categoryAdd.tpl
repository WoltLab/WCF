{include file='header'}

{if $aclObjectTypeID}
	{include file='aclPermissions'}
	
	{if !$category|isset}
		{include file='aclPermissionJavaScript' containerID='groupPermissions' objectTypeID=$aclObjectTypeID}
	{else}
		{include file='aclPermissionJavaScript' containerID='groupPermissions' objectTypeID=$aclObjectTypeID objectID=$category->categoryID}
	{/if}
{/if}

{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}
{if $objectType->getProcessor()->hasDescription()}
	{include file='multipleLanguageInputJavascript' elementIdentifier='description' forceSelection=false}
{/if}

<header class="boxHeadline">
	<h1>{@$objectType->getProcessor()->getLanguageVariable($action)}</h1>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{@$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $action == 'edit' && $availableCategories->hasChildren()}
						<li class="dropdown">
							<a class="button dropdownToggle"><span class="icon icon16 icon-sort"></span> <span>{@$objectType->getProcessor()->getLanguageVariable('button.choose')}</span></a>
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
						<li><a href="{link controller=$listController application=$objectType->getProcessor()->getApplication()}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{@$objectType->getProcessor()->getLanguageVariable('button.list')}</span></a></li>
					{/if}
					
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form method="post" action="{if $action == 'add'}{link controller=$addController application=$objectType->getProcessor()->getApplication()}{/link}{else}{link controller=$editController application=$objectType->getProcessor()->getApplication() object=$category}{/link}{/if}">
	<div class="container containerPadding marginTop">
		{event name='beforeFieldsets'}
		
		<fieldset>
			<legend>{lang}wcf.global.form.data{/lang}</legend>
			
			<dl{if $errorField == 'title'} class="formError"{/if}>
				<dt><label for="title">{@$objectType->getProcessor()->getLanguageVariable('title')}</label></dt>
				<dd>
					<input type="text" id="title" name="title" value="{$i18nPlainValues['title']}" class="long" />
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
			{/if}
			
			<dl{if $errorField == 'isDisabled'} class="formError"{/if}>
				<dt class="reversed"><label for="isDisabled">{@$objectType->getProcessor()->getLanguageVariable('isDisabled')}</label></dt>
				<dd>
					<input type="checkbox" id="isDisabled" name="isDisabled"{if $isDisabled} checked="checked"{/if} />
					{hascontent}<small>{content}{@$objectType->getProcessor()->getLanguageVariable('isDisabled.description', true)}{/content}</small>{/hascontent}
				</dd>
			</dl>
			
			{event name='dataFields'}
		</fieldset>
		
		<fieldset>
			<legend>{@$objectType->getProcessor()->getLanguageVariable('position')}</legend>
			
			{if $categoryNodeList->hasChildren() && $objectType->getProcessor()->getMaximumNestingLevel()}
				<dl{if $errorField == 'parentCategoryID'} class="formError"{/if}>
					<dt><label for="parentCategoryID">{@$objectType->getProcessor()->getLanguageVariable('parentCategoryID')}</label></dt>
					<dd>
						<select id="parentCategoryID" name="parentCategoryID">
							<option value="0">{lang}wcf.global.noSelection{/lang}</option>
							{include file='categoryOptionList' categoryID=$parentCategoryID maximumNestingLevel=$objectType->getProcessor()->getMaximumNestingLevel()}
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
				<dt><label for="showOrder">{@$objectType->getProcessor()->getLanguageVariable('showOrder')}</label></dt>
				<dd>
					<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" min="0" class="short" />
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
		</fieldset>
		
		{if $aclObjectTypeID}
			<fieldset>
				<legend>{lang}wcf.acl.permissions{/lang}</legend>
				
				<dl id="groupPermissions" class="wide">
					<dt>{lang}wcf.acl.permissions{/lang}</dt>
					<dd></dd>
				</dl>
				
				{event name='permissionFields'}
			</fieldset>
		{/if}
		
		{event name='afterFieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
 	</div>
</form>

{include file='footer'}