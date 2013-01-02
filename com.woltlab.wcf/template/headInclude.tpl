<base href="{$baseHref}" />
<meta charset="utf-8" />
<meta name="description" content="{META_DESCRIPTION}" />
<meta name="keywords" content="{META_KEYWORDS}" />

<script type="text/javascript">
	//<![CDATA[
	var SID_ARG_2ND	= '{@SID_ARG_2ND_NOT_ENCODED}';
	var RELATIVE_WCF_DIR = '{@$__wcf->getPath()}';
	var SECURITY_TOKEN = '{@SECURITY_TOKEN}';
	var LANGUAGE_ID = {@$__wcf->getLanguage()->languageID};
	//]]>
</script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/jquery.min.js"></script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/jquery-ui.min.js"></script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/jquery.tools.min.js"></script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/jquery-ui.nestedSortable.js"></script>
<script type="text/javascript" src="{@$__wcf->getPath()}js/WCF.js"></script>
<script type="text/javascript">
	//<![CDATA[
	WCF.User.init({@$__wcf->user->userID}, '{@$__wcf->user->username|encodeJS}');
	//]]>
</script>
{event name='javascriptInclude'}

<!-- Stylesheets -->
{@$__wcf->getStyleHandler()->getStylesheet()}

<noscript>
	<style type="text/css">
		.javascriptOnly {
			display: none !important;
		}
	</style>
</noscript>

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'__days': [ '{lang}wcf.date.day.sunday{/lang}', '{lang}wcf.date.day.monday{/lang}', '{lang}wcf.date.day.tuesday{/lang}', '{lang}wcf.date.day.wednesday{/lang}', '{lang}wcf.date.day.thursday{/lang}', '{lang}wcf.date.day.friday{/lang}', '{lang}wcf.date.day.saturday{/lang}' ],
			'wcf.date.relative.minutes': '{capture assign=relativeMinutes}{lang}wcf.date.relative.minutes{/lang}{/capture}{@$relativeMinutes|encodeJS}',
			'wcf.date.relative.hours': '{capture assign=relativeHours}{lang}wcf.date.relative.hours{/lang}{/capture}{@$relativeHours|encodeJS}',
			'wcf.date.relative.pastDays': '{capture assign=relativePastDays}{lang}wcf.date.relative.pastDays{/lang}{/capture}{@$relativePastDays|encodeJS}',
			'wcf.date.dateTimeFormat': '{lang}wcf.date.dateTimeFormat{/lang}',
			'wcf.global.button.add': '{lang}wcf.global.button.add{/lang}',
			'wcf.global.button.cancel': '{lang}wcf.global.button.cancel{/lang}',
			'wcf.global.button.close': '{lang}wcf.global.button.close{/lang}',
			'wcf.global.button.collapsible': '{lang}wcf.global.button.collapsible{/lang}',
			'wcf.global.button.delete': '{lang}wcf.global.button.delete{/lang}',
			'wcf.global.button.disable': '{lang}wcf.global.button.disable{/lang}',
			'wcf.global.button.disabledI18n': '{lang}wcf.global.button.disabledI18n{/lang}',
			'wcf.global.button.edit': '{lang}wcf.global.button.edit{/lang}',
			'wcf.global.button.enable': '{lang}wcf.global.button.enable{/lang}',
			'wcf.global.button.next': '{lang}wcf.global.button.next{/lang}',
			'wcf.global.button.preview': '{lang}wcf.global.button.preview{/lang}',
			'wcf.global.button.reset': '{lang}wcf.global.button.reset{/lang}',
			'wcf.global.button.save': '{lang}wcf.global.button.save{/lang}',
			'wcf.global.button.search': '{lang}wcf.global.button.search{/lang}',
			'wcf.global.button.submit': '{lang}wcf.global.button.submit{/lang}',
			'wcf.global.confirmation.cancel': '{lang}wcf.global.confirmation.cancel{/lang}',
			'wcf.global.confirmation.confirm': '{lang}wcf.global.confirmation.confirm{/lang}',
			'wcf.global.confirmation.title': '{lang}wcf.global.confirmation.title{/lang}',
			'wcf.global.decimalPoint': '{capture assign=decimalPoint}{lang}wcf.global.decimalPoint{/lang}{/capture}{$decimalPoint|encodeJS}',
			'wcf.global.error.title': '{lang}wcf.global.error.title{/lang}',
			'wcf.global.form.edit.success': '{lang}wcf.global.form.edit.success{/lang}',
			'wcf.global.language.noSelection': '{lang}wcf.global.language.noSelection{/lang}',
			'wcf.global.loading': '{lang}wcf.global.loading{/lang}',
			'wcf.global.page.jumpTo': '{lang}wcf.global.page.jumpTo{/lang}',
			'wcf.global.page.jumpTo.description': '{lang}wcf.global.page.jumpTo.description{/lang}',
			'wcf.global.page.pageNavigation': '{lang}wcf.global.page.pageNavigation{/lang}',
			'wcf.global.page.next': '{capture assign=pageNext}{lang}wcf.global.page.next{/lang}{/capture}{@$pageNext|encodeJS}',
			'wcf.global.page.previous': '{capture assign=pagePrevious}{lang}wcf.global.page.previous{/lang}{/capture}{@$pagePrevious|encodeJS}',
			'wcf.global.thousandsSeparator': '{capture assign=thousandsSeparator}{lang}wcf.global.thousandsSeparator{/lang}{/capture}{@$thousandsSeparator|encodeJS}',
			'wcf.sitemap.title': '{lang}wcf.sitemap.title{/lang}',
			'wcf.style.changeStyle': '{lang}wcf.style.changeStyle{/lang}'
			{event name='javascriptLanguageImport'}
		});
		
		WCF.Icon.addObject({
			'wcf.icon.add': '{icon}add{/icon}',
			'wcf.icon.arrowDown': '{icon}arrowDown{/icon}',
			'wcf.icon.arrowLeft': '{icon}arrowLeft{/icon}',
			'wcf.icon.arrowRight': '{icon}arrowRight{/icon}',
			'wcf.icon.arrowUp': '{icon}arrowUp{/icon}',
			'wcf.icon.circleArrowDown': '{icon}circleArrowDown{/icon}',
			'wcf.icon.circleArrowLeft': '{icon}circleArrowLeft{/icon}',
			'wcf.icon.circleArrowRight': '{icon}circleArrowRight{/icon}',
			'wcf.icon.circleArrowUp': '{icon}circleArrowUp{/icon}',
			'wcf.icon.closed': '{icon}arrowRightInverse{/icon}',
			'wcf.icon.dropdown': '{icon}dropdown{/icon}',
			'wcf.icon.delete': '{icon}delete{/icon}',
			'wcf.icon.edit': '{icon}edit{/icon}',
			'wcf.icon.error': '{icon}errorRed{/icon}',
			'wcf.icon.loading': '{icon}spinner{/icon}',
			'wcf.icon.opened': '{icon}arrowDownInverse{/icon}'
			{event name='javascriptIconImport'}
		});
		
		new WCF.Date.Time();
		new WCF.Effect.SmoothScroll();
		new WCF.Effect.BalloonTooltip();
		new WCF.Sitemap();
		{if $__wcf->getStyleHandler()->countStyles() > 1}new WCF.Style.Chooser();{/if}
		WCF.Dropdown.init();
		WCF.System.PageNavigation.init('.pageNavigation');
		WCF.Date.Picker.init();
		
		{event name='javascriptInit'}
		
		{if $executeCronjobs}
			new WCF.Action.Proxy({
				autoSend: true,
				data: {
					className: 'wcf\\data\\cronjob\\CronjobAction',
					actionName: 'executeCronjobs'
				},
				showLoadingOverlay: false,
				failure: function() {
					return false;
				}
			});
		{/if}
		{if $__sessionKeepAlive|isset}
			new WCF.System.KeepAlive({@$__sessionKeepAlive});
		{/if}
	});
	//]]>
</script>
