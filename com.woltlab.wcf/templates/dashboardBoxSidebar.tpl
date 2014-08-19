<fieldset class="dashboardBox" data-box-name="{$box->boxName}">
	<legend>{if $titleLink}<a href="{$titleLink}">{lang}wcf.dashboard.box.{$box->boxName}{/lang}</a>{else}{lang}wcf.dashboard.box.{$box->boxName}{/lang}{/if}</legend>
	
	<div>
		{@$template}
	</div>
</fieldset>