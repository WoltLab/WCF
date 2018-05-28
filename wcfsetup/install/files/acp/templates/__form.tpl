<form method="{@$form->getMethod()}" action="{@$form->getAction()}" id="{@$form->getId()}"{if !$form->getClasses()|empty} class="{implode from=$form->getClasses() item='class'}{$class}{/implode}"{/if}{foreach from=$form->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}>
	{foreach from=$form item='child'}
		{@$child->getHtml()}
	{/foreach}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>
