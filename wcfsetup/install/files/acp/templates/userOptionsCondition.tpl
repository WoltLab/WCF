{foreach from=$optionTree item='category'}
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.user.option.category.{@$category[object]->categoryName}{/lang}</h2>
			{hascontent}<small class="sectionDescription">{content}{lang __optional=true}wcf.user.option.category.{@$category[object]->categoryName}.description{/lang}{/content}</small>{/hascontent}
		</header>
		
		{include file='optionFieldList' options=$category[options] langPrefix='wcf.user.option.'}
	</section>
{/foreach}
