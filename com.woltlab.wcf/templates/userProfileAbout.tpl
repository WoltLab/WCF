{hascontent}
	{content}
		{event name='beforeUserOptions'}

		{foreach from=$view->getOptionValuesOutput() key=categoryName item=userOptions}
			<section class="section">
				<h2 class="sectionTitle">{$view->getCategoryTitle($categoryName)}</h2>

				{foreach from=$userOptions item=userOption}
					<dl data-option-id="{$userOption[object]->optionID}">
						<dt>{$userOption[object]->getTitle()}</dt>
						<dd>{unsafe:$userOption[output]}</dd>
					</dl>
				{/foreach}
			</section>
		{/foreach}

		{event name='afterUserOptions'}
	{/content}
{hascontentelse}
	<div class="section">
		<woltlab-core-notice type="info">{lang}wcf.user.profile.content.about.noPublicData{/lang}</woltlab-core-notice>
	</div>
{/hascontent}
