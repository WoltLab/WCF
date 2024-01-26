{include file='header' pageTitle='wcf.acp.smiley.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.smiley.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='SmileyList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.smiley.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='SmileyAdd'}{/link}{else}{link controller='SmileyEdit' id=$smiley->smileyID}{/link}{/if}" enctype="multipart/form-data">
	<section class="section">
		<dl{if $errorField == 'smileyTitle'} class="formError"{/if}>
			<dt><label for="smileyTitle">{lang}wcf.acp.smiley.title{/lang}</label></dt>
			<dd>
				<input type="text" id="smileyTitle" name="smileyTitle" value="{$i18nPlainValues['smileyTitle']}" autofocus class="long">
				
				{if $errorField == 'smileyTitle'}
					<small class="innerError">
						{if $errorType == 'empty' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{$errorType}{/lang}
						{else}
							{lang}wcf.acp.smiley.smileyTitle.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		{include file='shared_multipleLanguageInputJavascript' elementIdentifier='smileyTitle' forceSelection=false}
		
		<dl{if $errorField == 'categoryID'} class="formError"{/if}>
			<dt><label for="categoryID">{lang}wcf.global.category{/lang}</label></dt>
			<dd>
				<select id="categoryID" name="categoryID">
					<option value="0"{if $categoryID === null} selected{/if}>{lang}wcf.acp.smiley.categoryID.default{/lang}</option>
					{include file='shared_categoryOptionList'}
				</select>
				
				{if $errorField == 'categoryID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.smiley.categoryID.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'smileyCode'} class="formError"{/if}>
			<dt><label for="smileyCode">{lang}wcf.acp.smiley.smileyCode{/lang}</label></dt>
			<dd>
				<input type="text" id="smileyCode" name="smileyCode" value="{$smileyCode}" required class="medium">
				
				{if $errorField == 'smileyCode'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.smiley.smileyCode.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'aliases'} class="formError"{/if}>
			<dt><label for="aliases">{lang}wcf.acp.smiley.aliases{/lang}</label></dt>
			<dd>
				<textarea id="aliases" name="aliases" cols="40" rows="10">{$aliases}</textarea>
				
				{if $errorField == 'aliases'}
					<small class="innerError">
						{lang}wcf.acp.smiley.aliases.error.{@$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'showOrder'} class="formError"{/if}>
			<dt><label for="showOrder">{lang}wcf.global.showOrder{/lang}</label></dt>
			<dd>
				<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" min="0" class="short">
				
				{if $errorField == 'showOrder'}
					<small class="innerError">
						{lang}wcf.acp.smiley.showOrder.error.{@$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='dataFields'}
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.smiley.smileyFile{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-6{if $errorField == 'fileUpload'} formError{/if}">
				<dt><label for="fileUpload">{lang}wcf.acp.smiley.fileUpload{/lang}</label></dt>
				<dd>
					{if $uploadedFilename}
						<input type="hidden" name="uploadedFilename" value="{$uploadedFilename}">
					{/if}
					<input type="file" id="fileUpload" name="fileUpload" value="">
					
					{if $errorField == 'fileUpload'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.smiley.fileUpload.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.smiley.fileUpload.description{/lang}</small>
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-6{if $errorField == 'smileyPath'} formError{/if}">
				<dt><label for="smileyPath">{lang}wcf.acp.smiley.smileyPath{/lang}</label></dt>
				<dd>
					<input type="text" id="smileyPath" name="smileyPath" value="{$smileyPath}" class="long">
					
					{if $errorField == 'smileyPath'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.smiley.smileyPath.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.smiley.smileyPath.description{/lang}</small>
				</dd>
			</dl>
		</div>
		
		{event name='smileyFileFields'}
	</section>
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.smiley.smileyFile2x{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.acp.smiley.smileyFile2x.description{/lang}</p>
		</header>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-6{if $errorField == 'fileUpload2x'} formError{/if}">
				<dt><label for="fileUpload2x">{lang}wcf.acp.smiley.fileUpload{/lang}</label></dt>
				<dd>
					{if $uploadedFilename2x}
						<input type="hidden" name="uploadedFilename2x" value="{$uploadedFilename2x}">
					{/if}
					<input type="file" id="fileUpload2x" name="fileUpload2x" value="">
					
					{if $errorField == 'fileUpload2x'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.smiley.fileUpload.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.smiley.fileUpload.description{/lang}</small>
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-6{if $errorField == 'smileyPath2x'} formError{/if}">
				<dt><label for="smileyPath2x">{lang}wcf.acp.smiley.smileyPath{/lang}</label></dt>
				<dd>
					<input type="text" id="smileyPath2x" name="smileyPath2x" value="{$smileyPath2x}" class="long">
					
					{if $errorField == 'smileyPath2x'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.smiley.smileyPath.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.smiley.smileyPath.description{/lang}</small>
				</dd>
			</dl>
		</div>
		
		{event name='smileyFile2xFields'}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
