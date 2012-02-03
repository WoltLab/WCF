{include file='header'}
{if $languageItems|count}
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			var $languageItems = [ ];
			
			// read language item names
			$('fieldset.languageItem').each(function(index, languageItem) {
				$languageItems.push($(languageItem).data('languageItem').toLowerCase());
			});
			
			// enable quick search
			$('#quicksearch').bind('keyup', function() {
				var $searchString = $('#quicksearch').val().toLowerCase();
				
				for (var $i = 0, $length = $languageItems.length; $i < $length; $i++) {
					var $item = $languageItems[$i];
					
					if ($item.indexOf($searchString) >= 0) {
						$('#item' + $.wcfEscapeID($item)).show();
					}
					else {
						$('#item' + $.wcfEscapeID($item)).hide();
					}
				}
			});
			
			// disable form submit on RETURN (= 13)
			$('#quicksearch').bind('keydown', function(event) {
				if (event.which == 13) {
					event.stopPropagation();
					return false;
				}
			});
			
			// custom versions
			$('input.itemUseCustom').each(function(index, item) {
				var $item = $(item);
				
				if (!$item.attr('checked')) {
					$('#languageCustomItems-' + $item.data('languageItem')).hide();
				}
				
				$item.change(function(event) {
					var $item = $(event.targetElement);
					if ($item.attr('checked')) {
						$('#languageCustomItems-' + $item.data('languageItem')).show();
					}
					else {
						$('#languageCustomItems-' + $item.data('languageItem')).hide();
					}
				});
			});
		});
		//]]>
	</script>
{/if}

<div class="wcf-mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/languageEditL.png" alt="" />
	<div>
		<h2>{lang}wcf.acp.language.edit{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="wcf-error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="wcf-success">{lang}wcf.acp.language.edit.success{/lang}</p>	
{/if}

<div class="wcf-contentHeader">
	<div class="wcf-largeButtons">
		<ul>
			<li><a href="index.php?page=LanguageList{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/languageM.png" alt="" title="{lang}wcf.acp.menu.link.language.view{/lang}" class="wcf-button" /> <span>{lang}wcf.acp.menu.link.language.view{/lang}</span></a></li>
			<li><a href="index.php?form=LanguageSearch{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/searchM.png" alt="" title="{lang}wcf.acp.menu.link.language.view{/lang}" class="wcf-button" /> <span>{lang}wcf.acp.menu.link.language.search{/lang}</span></a></li>
		</ul>
	</div>
</div>

<form method="get" action="index.php">
	<div class="wcf-border wcf-content">
		<div>
			<div class="formElement">
				<p class="formField">
					<span>{lang}wcf.global.language.{@$language->languageCode}{/lang} ({@$language->languageCode})</span>
					<img src="{@RELATIVE_WCF_DIR}icon/language{@$language->languageCode|ucfirst}S.png" alt="" />
				</p>
			</div>
			
			<div class="formElement{if $errorField == 'languageCategoryID'} wcf-formError{/if}">
				<div class="formFieldLabel"><!-- Todo: Def. List! -->
					<label for="languageCategoryID">{lang}wcf.acp.language.category{/lang}</label>
				</div>
				<div class="formField">
					<select name="languageCategoryID" id="languageCategoryID" onchange="if (this.options[this.selectedIndex].value != 0) this.form.submit();">
						<option value="0"></option>
						{htmlOptions options=$languageCategories selected=$languageCategoryID}
					</select>
					{if $errorField == 'languageCategoryID'}
						<p class="wcf-innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						</p>
					{/if}
				</div>
			</div>
			
			<div class="formElement"><!-- Todo: Def. List! -->
				<div class="formField">
					<label><input onclick="this.form.submit()" type="checkbox" name="customVariables" value="1" {if $customVariables == 1}checked="checked" {/if}/> {lang}wcf.acp.language.showCustomVariables{/lang}</label>
				</div>
			</div>
		</div>
		
		<input type="hidden" name="form" value="LanguageEdit" />
		<input type="hidden" name="languageID" value="{@$languageID}" />
 		{@SID_INPUT_TAG}
 	</div>
</form>

{if $languageItems|count}
	<form method="post" action="index.php?form=LanguageEdit">
		<fieldset>
			<div class="formElement">
				<div class="formFieldLabel">
					{lang}wcf.acp.language.variable.quicksearch{/lang}
				</div>
				<div class="formField">
					<input class="inputText" type="text" id="quicksearch" value="" />
				</div>
			</div>
		</fieldset>
		
		{foreach from=$languageItems item=category}
			<div class="wcf-border wcf-content">
				<div>
					<h3 class="wcf-subHeadline">{$category.category}</h3>
					{foreach from=$category.items key=$languageItem item=languageItemValue}
						<a id="languageItem{@$languageItemIDs.$languageItem}"></a>
						
						<fieldset data-languageItem="{$languageItem}" class="languageItem" id="item{$languageItem|strtolower}">
							<legend>
								{if $__wcf->getSession()->getPermission('admin.language.canDeleteLanguage')}<a onclick="return confirm('{lang}wcf.acp.language.variable.delete.sure{/lang}')" href="index.php?action=LanguageVariableDelete&amp;languageItem={$languageItem}&amp;languageID={@$languageID}&amp;languageCategoryID={@$languageCategoryID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.language.variable.delete{/lang}" /></a>{/if}
								<label for="languageCustomItems-{$languageItem}">{$languageItem}</label>
							</legend>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="languageItems-{$languageItem}">{lang}wcf.acp.language.value{/lang}</label>
								</div>
								<div class="formField">
									<textarea readonly="readonly" rows="5" cols="60" id="languageItems-{$languageItem}">{$languageItemValue}</textarea>
								</div>
							</div>
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="languageCustomItems-{$languageItem}">{lang}wcf.acp.language.customValue{/lang}</label>
								</div>
								<div class="formField">
									<textarea rows="5" cols="60" name="languageItems[{$languageItem}]" id="languageCustomItems-{$languageItem}">{if $languageCustomItems.$languageItem}{$languageCustomItems.$languageItem}{/if}</textarea>
									<label><input class="itemUseCustom" data-languageItem="{$languageItem}" type="checkbox" name="languageUseCustom[{$languageItem}]" id="languageUseCustom-{$languageItem}" value="1" {if !$languageUseCustom.$languageItem|empty}checked="checked" {/if}/> {lang}wcf.acp.language.useCustomValue{/lang}</label>
								</div>
							</div>
						</fieldset>
						
						{if $languageItemID == $languageItemIDs.$languageItem}
							<script type="text/javascript">
								//<![CDATA[
								document.getElementById('languageItems-{$languageItem}').focus();
								//]]>
							</script>
						{/if}
					{/foreach}
				</div>
			</div>
		{/foreach}
		
		{if $additionalFields|isset}{@$additionalFields}{/if}
		
		<div class="wcf-formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
	 		{@SID_INPUT_TAG}
	 		<input type="hidden" name="languageCategoryID" value="{@$languageCategoryID}" />
	 		<input type="hidden" name="customVariables" value="{@$customVariables}" />
	 		<input type="hidden" name="languageID" value="{@$languageID}" />
	 	</div>
	</form>
{/if}

{include file='footer'}
