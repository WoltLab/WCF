{include file='header' pageTitle='wcf.acp.firstTimeSetup.completed'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.firstTimeSetup.completed{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					<li><a href="{link}{/link}" class="button">{icon name='house'} <span>{lang}wcf.global.acp{/lang}</span></a></li>
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<section class="firstTimeSetup__section section">
	<h2 class="firstTimeSetup__title sectionTitle">
		{lang}wcf.acp.firstTimeSetup.completed.nextSteps{/lang}
	</h2>

	<div class="firstTimeSetup__nextSteps">
		<div class="firstTimeSetup__nextStep">
			<div class="firstTimeSetup__nextStep__icon">
				{icon name='plus' size=64}
			</div>
			<div class="firstTimeSetup__nextStep__content">
				<h3 class="firstTimeSetup__nextStep__title">
					<a href="{link controller='PackageStartInstall'}{/link}" class="externalURL">
						{lang}wcf.acp.firstTimeSetup.completed.apps.title{/lang}
					</a>
				</h3>
				<p class="firstTimeSetup__nextStep__description">
					{lang}wcf.acp.firstTimeSetup.completed.apps.description{/lang}
				</p>
			</div>
		</div>
		<div class="firstTimeSetup__nextStep">
			<div class="firstTimeSetup__nextStep__icon">
				{icon name='book' size=64}
			</div>
			<div class="firstTimeSetup__nextStep__content">
				<h3 class="firstTimeSetup__nextStep__title">
					<a href="https://manual.woltlab.com/{if $__wcf->language->getFixedLanguageCode() === 'de'}de{else}en{/if}/" class="externalURL">
						{lang}wcf.acp.firstTimeSetup.completed.manual.title{/lang}
					</a>
				</h3>
				<p class="firstTimeSetup__nextStep__description">
					{lang}wcf.acp.firstTimeSetup.completed.manual.description{/lang}
				</p>
			</div>
		</div>
		<div class="firstTimeSetup__nextStep">
			<div class="firstTimeSetup__nextStep__icon">
				{icon name='plug' size=64}
			</div>
			<div class="firstTimeSetup__nextStep__content">
				<h3 class="firstTimeSetup__nextStep__title">
					<a href="https://www.woltlab.com/pluginstore/" class="externalURL">
						{lang}wcf.acp.firstTimeSetup.completed.pluginstore.title{/lang}
					</a>
				</h3>
				<p class="firstTimeSetup__nextStep__description">
					{lang}wcf.acp.firstTimeSetup.completed.pluginstore.description{/lang}
				</p>
			</div>
		</div>
		<div class="firstTimeSetup__nextStep">
			<div class="firstTimeSetup__nextStep__icon">
				{icon name='circle-question' size=64}
			</div>
			<div class="firstTimeSetup__nextStep__content">
				<h3 class="firstTimeSetup__nextStep__title">
					<a href="https://www.woltlab.com/ticket-add/" class="externalURL">
						{lang}wcf.acp.firstTimeSetup.completed.support.title{/lang}
					</a>
				</h3>
				<p class="firstTimeSetup__nextStep__description">
					{lang}wcf.acp.firstTimeSetup.completed.support.description{/lang}
				</p>
			</div>
		</div>
	</div>
</section>

{include file='footer'}
