{foreach from=$optionTree item='category'}
	<fieldset>
		<legend>{lang}wcf.user.option.category.{@$category[object]->categoryName}{/lang}</legend>
		{hascontent}<p>{content}{lang __optional=true}wcf.user.option.category.{@$category[object]->categoryName}.description{/lang}{/content}</p>{/hascontent}
		
		{include file='optionFieldList' options=$category[options] langPrefix='wcf.user.option.'}
	</fieldset>
{/foreach}
