{capture assign='attributeTemplate'}
	<section class="section">
		<h2 class="sectionTitle">
			<button type="button" class="jsDeleteButton jsTooltip" title="{lang}wcf.global.button.delete{/lang}">
				{icon name='xmark'}
			</button>
			<span>{lang __literal=true}wcf.acp.bbcode.numberedAttribute{/lang}</span>
		</h2>
		
		<dl>
			<dt>
				<label for="{$field->getPrefixedId()}[{ldelim}@$attributeNumber}][attributeHtml]">{lang}wcf.acp.bbcode.attribute.attributeHtml{/lang}</label>
			</dt>
			<dd>
				<input type="text" id="{$field->getPrefixedId()}[{ldelim}@$attributeNumber}][attributeHtml]" name="{$field->getPrefixedId()}[{ldelim}@$attributeNumber}][attributeHtml]" value="" class="long" maxlength="255">
			</dd>
		</dl>
		
		<dl>
			<dt>
				<label for="{$field->getPrefixedId()}[{ldelim}@$attributeNumber}][validationPattern]">{lang}wcf.acp.bbcode.attribute.validationPattern{/lang}</label>
			</dt>
			<dd>
				<input type="text" id="{$field->getPrefixedId()}[{ldelim}@$attributeNumber}][validationPattern]" name="{$field->getPrefixedId()}[{ldelim}@$attributeNumber}][validationPattern]" value="" class="long" maxlength="255">
			</dd>
		</dl>
		
		<dl>
			<dt>
				<label for="{$field->getPrefixedId()}_required_{ldelim}@$attributeNumber}">{lang}wcf.acp.bbcode.attribute.required{/lang}</label>
			</dt>
			<dd>
				<ol class="flexibleButtonGroup">
					<li>
						<input type="radio" {*
							*}id="{$field->getPrefixedId()}_required_{ldelim}@$attributeNumber}" {*
							*}name="{$field->getPrefixedId()}[{ldelim}@$attributeNumber}][required]" {*
							*}value="1" {*
							*}data-no-input-id="{$field->getPrefixedId()}_required_{ldelim}@$attributeNumber}_no"{*
						*}>
						<label for="{$field->getPrefixedId()}_required_{ldelim}@$attributeNumber}" class="green">
							{icon name='check'} {lang}wcf.global.form.boolean.yes{/lang}
						</label>
					</li>
					<li>
						<input type="radio" {*
							*}id="{$field->getPrefixedId()}_required_{ldelim}@$attributeNumber}_no" {*
							*}name="{$field->getPrefixedId()}[{ldelim}@$attributeNumber}][required]" {*
							*}value="0" {*
							*}name="{$field->getPrefixedId()}[{ldelim}@$attributeNumber}][required]" {*
							*}checked{*
						*}>
						<label for="{$field->getPrefixedId()}_required_{ldelim}@$attributeNumber}_no" class="red">
							{icon name='xmark'} {lang}wcf.global.form.boolean.no{/lang}
						</label>
					</li>
				</ol>
			</dd>
		</dl>
		
		<dl>
			<dt>
				<label for="{$field->getPrefixedId()}_useText_{ldelim}@$attributeNumber}">{lang}wcf.acp.bbcode.attribute.useText{/lang}</label>
			</dt>
			<dd>
				<ol class="flexibleButtonGroup">
					<li>
						<input type="radio" {*
							*}id="{$field->getPrefixedId()}_useText_{ldelim}@$attributeNumber}" {*
							*}name="{$field->getPrefixedId()}[{ldelim}@$attributeNumber}][useText]" {*
							*}value="1" {*
							*}data-no-input-id="{$field->getPrefixedId()}_useText_{ldelim}@$attributeNumber}_no"{*
						*}>
						<label for="{$field->getPrefixedId()}_useText_{ldelim}@$attributeNumber}" class="green">
							{icon name='check'} {lang}wcf.global.form.boolean.yes{/lang}
						</label>
					</li>
					<li>
						<input type="radio" {*
							*}id="{$field->getPrefixedId()}_useText_{ldelim}@$attributeNumber}_no" {*
							*}name="{$field->getPrefixedId()}[{ldelim}@$attributeNumber}][useText]" {*
							*}value="0" {*
							*}name="{$field->getPrefixedId()}[{ldelim}@$attributeNumber}][useText]" {*
							*}checked{*
						*}>
						<label for="{$field->getPrefixedId()}_useText_{ldelim}@$attributeNumber}_no" class="red">
							{icon name='xmark'} {lang}wcf.global.form.boolean.no{/lang}
						</label>
					</li>
				</ol>
			</dd>
		</dl>
		
		{event name='attributeFields'}
	</section>
{/capture}

<script data-relocate="true">
	require(['Dom/ChangeListener', 'Dom/Traverse', 'Dom/Util', 'WoltLabSuite/Core/Template'], function(DomChangeListener, DomTraverse, DomUtil, Template) {
		var parentContainer = elById('{@$field->getParent()->getPrefixedId()|encodeJS}Container');
		
		var parentTitle = DomTraverse.childBySel(parentContainer, 'h2.sectionTitle');
		parentTitle.innerHTML = `
			<button type="button" class="jsTooltip" id="{$field->getPrefixedId()}AddAttribute" title="{lang}wcf.global.button.add{/lang}">
				{icon name='plus'}
			</button>
			${ parentTitle.innerHTML }
		`;
		
		DomChangeListener.trigger();
		
		var addDeleteButtonListeners = function() {
			var deleteButtonCallback = function(event) {
				elRemove(event.currentTarget.closest('section'));
			};
			
			elBySelAll('.jsDeleteButton', parentContainer, function(deleteButton) {
				deleteButton.classList.remove('jsDeleteButton');
				deleteButton.addEventListener('click', deleteButtonCallback);
			});
		};
		addDeleteButtonListeners();
		
		var attributeNumber = {if $field->getValue()|empty}0{else}{$field->getValue()|count}{/if};
		var attributeTemplate = new Template('{@$attributeTemplate|encodeJS}');
		
		elById('{@$field->getPrefixedId()|encodeJS}AddAttribute').addEventListener('click', function(event) {
			var html = attributeTemplate.fetch({ attributeNumber: attributeNumber++ });
			
			DomUtil.insertHtml(html, parentContainer, 'append');
			
			addDeleteButtonListeners();
			
			DomChangeListener.trigger();
		});
	});
</script>

{foreach from=$field->getValue() key=attributeNumber item=attributeData name=bbcodeAttributes}
	<section class="section">
		<h2 class="sectionTitle">
			<button type="button" class="jsDeleteButton jsTooltip" title="{lang}wcf.global.button.delete{/lang}">
				{icon name='xmark'}
			</button>
			<span>{lang}wcf.acp.bbcode.numberedAttribute{/lang}</span>
		</h2>
		
		<dl>
			<dt>
				<label for="{$field->getPrefixedId()}[{@$attributeNumber}][attributeHtml]">{lang}wcf.acp.bbcode.attribute.attributeHtml{/lang}</label>
			</dt>
			<dd>
				<input type="text" name="{$field->getPrefixedId()}[{@$attributeNumber}][attributeHtml]" value="{if $attributeData[attributeHtml]|isset}{$attributeData[attributeHtml]}{/if}" class="long" maxlength="255">
			</dd>
		</dl>
		
		{assign var='__attributeValidationError' value=null}
		{foreach from=$field->getValidationErrors() item=validationError}
			{if $validationError->getType() === $field->getPrefixedId()|concat:'_validationPattern_':$attributeNumber}
				{assign var='__attributeValidationError' value=$validationError}
			{/if}
		{/foreach}
		<dl{if $__attributeValidationError !== null} class="formError"{/if}>
			<dt>
				<label for="{$field->getPrefixedId()}[{@$attributeNumber}][validationPattern]">{lang}wcf.acp.bbcode.attribute.validationPattern{/lang}</label>
			</dt>
			<dd>
				<input type="text" name="{$field->getPrefixedId()}[{@$attributeNumber}][validationPattern]" value="{if $attributeData[validationPattern]|isset}{$attributeData[validationPattern]}{/if}" class="long" maxlength="255">
				{if $__attributeValidationError !== null}
					<small class="innerError">{@$__attributeValidationError->getMessage()}</small>
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt>
				<label for="{$field->getPrefixedId()}_required_{@$attributeNumber}">{lang}wcf.acp.bbcode.attribute.required{/lang}</label>
			</dt>
			<dd>
				<ol class="flexibleButtonGroup">
					<li>
						<input type="radio" {*
							*}id="{$field->getPrefixedId()}_required_{@$attributeNumber}" {*
							*}name="{$field->getPrefixedId()}[{@$attributeNumber}][required]" {*
							*}value="1" {*
							*}data-no-input-id="{$field->getPrefixedId()}_required_{@$attributeNumber}_no"{*
							*}{if !$attributeData[required]|empty} checked{/if}{*
						*}>
						<label for="{$field->getPrefixedId()}_required_{@$attributeNumber}" class="green">
							{icon name='check'} {lang}wcf.global.form.boolean.yes{/lang}
						</label>
					</li>
					<li>
						<input type="radio" {*
							*}id="{$field->getPrefixedId()}_required_{@$attributeNumber}_no" {*
							*}name="{$field->getPrefixedId()}[{@$attributeNumber}][required]" {*
							*}value="0" {*
							*}name="{$field->getPrefixedId()}[{@$attributeNumber}][required]"{*
							*}{if $attributeData[required]|empty} checked{/if}{*
						*}>
						<label for="{$field->getPrefixedId()}_required_{@$attributeNumber}_no" class="red">
							{icon name='xmark'} {lang}wcf.global.form.boolean.no{/lang}
						</label>
					</li>
				</ol>
			</dd>
		</dl>
		
		<dl>
			<dt>
				<label for="{$field->getPrefixedId()}_useText_{@$attributeNumber}">{lang}wcf.acp.bbcode.attribute.useText{/lang}</label>
			</dt>
			<dd>
				<ol class="flexibleButtonGroup">
					<li>
						<input type="radio" {*
							*}id="{$field->getPrefixedId()}_useText_{@$attributeNumber}" {*
							*}name="{$field->getPrefixedId()}[{@$attributeNumber}][useText]" {*
							*}value="1" {*
							*}data-no-input-id="{$field->getPrefixedId()}_useText_{@$attributeNumber}_no"{*
							*}{if !$attributeData[useText]|empty} checked{/if}{*
						*}>
						<label for="{$field->getPrefixedId()}_useText_{@$attributeNumber}" class="green">
							{icon name='check'} {lang}wcf.global.form.boolean.yes{/lang}
						</label>
					</li>
					<li>
						<input type="radio" {*
							*}id="{$field->getPrefixedId()}_useText_{@$attributeNumber}_no" {*
							*}name="{$field->getPrefixedId()}[{@$attributeNumber}][useText]" {*
							*}value="0" {*
							*}name="{$field->getPrefixedId()}[{@$attributeNumber}][useText]"{*
							*}{if $attributeData[useText]|empty} checked{/if}{*
						*}>
						<label for="{$field->getPrefixedId()}_useText_{@$attributeNumber}_no" class="red">
							{icon name='xmark'} {lang}wcf.global.form.boolean.no{/lang}
						</label>
					</li>
				</ol>
				<small>{lang}wcf.acp.bbcode.attribute.useText.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='attributeFields'}
	</section>
{/foreach}
