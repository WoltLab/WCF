{include file='header' pageTitle='wcf.acp.stat'}

<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/flot/jquery.flot.js"></script>
<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/flot/jquery.flot.time.js"></script>
<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/flot/jquery.flot.resize.js"></script>
<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.stat.timeFormat.daily': '{lang}wcf.acp.stat.timeFormat.daily{/lang}',
			'wcf.acp.stat.timeFormat.weekly': '{lang}wcf.acp.stat.timeFormat.weekly{/lang}',
			'wcf.acp.stat.timeFormat.monthly': '{lang}wcf.acp.stat.timeFormat.monthly{/lang}',
			'wcf.acp.stat.timeFormat.yearly': '{lang}wcf.acp.stat.timeFormat.yearly{/lang}',
			'wcf.acp.stat.noData': '{lang}wcf.acp.stat.noData{/lang}'
		});
		
		new WCF.ACP.Stat.Chart();
	});
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.stat{/lang}</h1>
</header>

<div class="section">
	<div id="chart" style="height: 400px"></div>
</div>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.stat.settings{/lang}</h2>
	
	<dl>
		<dt><label for="startDate">{lang}wcf.acp.stat.period{/lang}</label></dt>
		<dd>
			<input type="date" id="startDate" name="startDate" value="{$startDate}" data-placeholder="{lang}wcf.date.period.start{/lang}" />
			&ndash;
			<input type="date" id="endDate" name="endDate" value="{$endDate}" data-placeholder="{lang}wcf.date.period.end{/lang}" />
		</dd>
	</dl>
	
	<dl>
		<dt><label>{lang}wcf.acp.stat.dateGrouping{/lang}</label></dt>
		<dd>
			<label><input type="radio" name="dateGrouping" value="daily" checked="checked" /> {lang}wcf.acp.stat.dateGrouping.daily{/lang}</label>
			<label><input type="radio" name="dateGrouping" value="weekly" /> {lang}wcf.acp.stat.dateGrouping.weekly{/lang}</label>
			<label><input type="radio" name="dateGrouping" value="monthly" /> {lang}wcf.acp.stat.dateGrouping.monthly{/lang}</label>
			<label><input type="radio" name="dateGrouping" value="yearly" /> {lang}wcf.acp.stat.dateGrouping.yearly{/lang}</label>
		</dd>
	</dl>
	
	<dl>
		<dt><label>{lang}wcf.acp.stat.value{/lang}</label></dt>
		<dd>
			<label><input type="radio" name="value" value="counter" checked="checked" /> {lang}wcf.acp.stat.value.counter{/lang}</label>
			<label><input type="radio" name="value" value="total" /> {lang}wcf.acp.stat.value.total{/lang}</label>
		</dd>
	</dl>
</section>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.stat.types{/lang}</h2>
	
	{foreach from=$availableObjectTypes key=categoryName item=objectTypes}
		<dl>
			<dt><label>{lang}wcf.acp.stat.category.{@$categoryName}{/lang}</label></dt>
			<dd>
				{foreach from=$objectTypes item=objectType}
					<label><input type="checkbox" name="objectTypeID" value="{@$objectType->objectTypeID}" {if $objectType->default}checked="checked" {/if}/> {lang}wcf.acp.stat.{@$objectType->objectType}{/lang}</label>
				{/foreach}
			</dd>
		</dl>
	{/foreach}
</section>

<div class="formSubmit">
	<button class="buttonPrimary" id="statRefreshButton">{lang}wcf.global.button.refresh{/lang}</button>
</div>

<div id="chartTooltip" class="balloonTooltip" style="display: none"></div>

{include file='footer'}
