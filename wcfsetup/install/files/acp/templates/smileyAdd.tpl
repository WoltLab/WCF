{include file='header' pageTitle='wcf.acp.smiley.'|concat:$action}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.smiley.{$action}{/lang}</h1>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					<li><a href="{link controller='SmileyList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.smiley.list{/lang}</span></a></li>
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form method="post" action="{if $action == 'add'}{link controller='SmileyAdd'}{/link}{else}{link controller='SmileyEdit' id=$smiley->smileyID}{/link}{/if}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.global.form.data{/lang}</legend>
			
			<dl{if $errorField == 'smileyTitle'} class="formError"{/if}>
				<dt><label for="smileyTitle">{lang}wcf.acp.smiley.title{/lang}</label></dt>
				<dd>
					<input type="text" id="smileyTitle" name="smileyTitle" value="{$smileyTitle}" autofocus="autofocus" class="long" />
					
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
			
			{include file='multipleLanguageInputJavascript' elementIdentifier='smileyTitle' forceSelection=false}
			
			<dl{if $errorField == 'categoryID'} class="formError"{/if}>
				<dt><label for="categoryID">{lang}wcf.acp.smiley.categoryID{/lang}</label></dt>
				<dd>
					<select id="categoryID" name="categoryID">
						<option value="0"{if $categoryID === null} selected="selected"{/if}>{lang}wcf.acp.smiley.categoryID.default{/lang}</option>
						{include file='categoryOptionList' maximumNestingLevel=0}
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
					<input type="text" id="smileyCode" name="smileyCode" value="{$smileyCode}" required="required" class="medium" />
					
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
			
			<dl{if $errorField == 'smileyPath'} class="formError"{/if}>
				<dt><label for="smileyPath">{lang}wcf.acp.smiley.smileyPath{/lang}</label></dt>
				<dd>
					<input type="text" id="smileyPath" name="smileyPath" value="{$smileyPath}" required="required" class="long" />
					
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
			
			<dl{if $errorField == 'showOrder'} class="formError"{/if}>
				<dt><label for="showOrder">{lang}wcf.acp.smiley.showOrder{/lang}</label></dt>
				<dd>
					<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" min="0" class="short" />
					
					{if $errorField == 'showOrder'}
						<small class="innerError">
							{lang}wcf.acp.smiley.showOrder.error.{@$errorType}{/lang}
						</small>
					{/if}
				</dd>
			</dl>
		</fieldset>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>

{include file='footer'}