{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.ToggleOptions('import', [ 'importDiv' ], [ 'copyDiv' ]);
		new WCF.ToggleOptions('copy', [ 'copyDiv' ], [ 'importDiv' ]);
	});
	//]]>
</script>

<header class="wcf-mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.language.add{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="wcf-error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="wcf-success">{lang}wcf.global.form.add.success{/lang}</p>	
{/if}

<div class="wcf-contentHeader">
	<nav>
		<ul class="wcf-largeButtons">
			<li><a href="{link controller='LanguageList'}{/link}" title="{lang}wcf.acp.menu.link.language.list{/lang}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/language1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.language.list{/lang}</span></a></li>
		</ul>
	</nav>
</div>

<form enctype="multipart/form-data" method="post" action="{link controller='LanguageAdd'}{/link}">
	<div class="wcf-border wcf-content">
		<dl>
			<dt><label for="import">{lang}wcf.acp.language.add.mode{/lang}</label></dt>
			<dd>
				<label><input type="radio" name="mode" value="import" id="import" {if $mode == 'import'}checked="checked" {/if}/> {lang}wcf.acp.language.add.mode.import{/lang}</label>
			</dd>
			<dd>
				<label><input type="radio" name="mode" value="copy" id="copy" {if $mode == 'copy'}checked="checked" {/if}/> {lang}wcf.acp.language.add.mode.copy{/lang}</label>
			</dd>
		</dl>
		
		<fieldset id="importDiv">
			<legend>{lang}wcf.acp.language.import.source{/lang}</legend>
			
			<dl{if $errorField == 'languageFile'} class="wcf-formError"{/if}>
				<dt><label for="languageFile">{lang}wcf.acp.language.import.source.file{/lang}</label></dt>
				<dd>
					<input type="text" id="languageFile" name="languageFile" value="{$languageFile}" class="long" />
					{if $errorField == 'languageFile'}
						<small class="wcf-innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.error.empty{/lang}
							{else}
								{lang}wcf.acp.language.import.error{/lang} {$errorType}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.language.import.source.file.description{/lang}</small>
				</dd>
			</dl>
			
			<dl{if $errorField == 'languageUpload'} class="wcf-formError"{/if}>
				<dt><label for="languageUpload">{lang}wcf.acp.language.import.source.upload{/lang}</label></dt>
				<dd>
					<input type="file" id="languageUpload" name="languageUpload" />
					{if $errorField == 'languageUpload'}
						<small class="wcf-innerError">
							{lang}wcf.acp.language.import.error{/lang} {$errorType}
						</small>
					{/if}
				</dd>
			</dl>
		</fieldset>
	
		<fieldset id="copyDiv">
			<legend>{lang}wcf.acp.language.add.new{/lang}</legend>
			
			<dl{if $errorField == 'languageCode'} class="wcf-formError"{/if}>
				<dt><label for="languageCode">{lang}wcf.acp.language.code{/lang}</label></dt>
				<dd>
					<input type="text" id="languageCode" name="languageCode" value="{$languageCode}" class="long" />
					{if $errorField == 'languageCode'}
						<small class="wcf-innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							{if $errorType == 'notUnique'}{lang}wcf.acp.language.add.languageCode.error.notUnique{/lang}{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.language.code.description{/lang}</small>
				</dd>
			</dl>
			
			<dl{if $errorField == 'sourceLanguageID'} class="wcf-formError"{/if}>
				<dt><label for="sourceLanguageID">{lang}wcf.acp.language.add.source{/lang}</label></dt>
				<dd>
					<select id="sourceLanguageID" name="sourceLanguageID">
						{foreach from=$languages item=language}
							<option value="{@$language->languageID}"{if $language->languageID == $sourceLanguageID} selected="selected"{/if}>{$language->languageName} ({$language->languageCode})</option>
						{/foreach}
					</select>
					{if $errorField == 'sourceLanguageID'}
						<small class="wcf-innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						</small>
					{/if}
				</dd>
			</dl>
		</fieldset>
		
		{if $additionalFields|isset}{@$additionalFields}{/if}
	</div>
	
	<div class="wcf-formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}
