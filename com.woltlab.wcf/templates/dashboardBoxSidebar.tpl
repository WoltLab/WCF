<section class="dashboardBox" data-box-name="{$box->boxName}">
	<h1>{if $titleLink}<a href="{$titleLink}">{lang}wcf.dashboard.box.{$box->boxName}{/lang}</a>{else}{lang}wcf.dashboard.box.{$box->boxName}{/lang}{/if}</h1>
	
	<div>
		{@$template}
	</div>
</section>