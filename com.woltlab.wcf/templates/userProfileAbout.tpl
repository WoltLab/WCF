{hascontent}
	{content}
		{event name='beforeUserOptions'}

		{foreach from=$options item=category}
			{foreach from=$category[categories] item=optionCategory}
				<section class="section">
					<h2 class="sectionTitle">{lang}wcf.user.option.category.{@$optionCategory[object]->categoryName}{/lang}</h2>
					
					{foreach from=$optionCategory[options] item=userOption}
						<dl>
							<dt>{$userOption[object]->getTitle()}</dt>
							<dd>{@$userOption[object]->optionValue}</dd>
						</dl>
					{/foreach}
				</section>
			{/foreach}
		{/foreach}

		{event name='afterUserOptions'}
	{/content}
{hascontentelse}
	<div class="section">
		<woltlab-core-notice type="info">{lang}wcf.user.profile.content.about.noPublicData{/lang}</woltlab-core-notice>
	</div>
{/hascontent}
