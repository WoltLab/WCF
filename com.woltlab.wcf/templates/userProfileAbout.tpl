{hascontent}
	{content}
		{foreach from=$options item=category}
			{foreach from=$category[categories] item=optionCategory}
				<section class="section">
					<h2 class="sectionTitle">{lang}wcf.user.option.category.{@$optionCategory[object]->categoryName}{/lang}</h2>
					
					{foreach from=$optionCategory[options] item=userOption}
						<dl>
							<dt>{lang}wcf.user.option.{@$userOption[object]->optionName}{/lang}</dt>
							<dd>{@$userOption[object]->optionValue}</dd>
						</dl>
					{/foreach}
				</section>
			{/foreach}
		{/foreach}
	{/content}
{hascontentelse}
	<div class="section">
		<p class="info" role="status">{lang}wcf.user.profile.content.about.noPublicData{/lang}</p>
	</div>
{/hascontent}
