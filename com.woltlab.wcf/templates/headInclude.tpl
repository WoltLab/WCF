<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
{include file='headIncludeRobotsMetaTag'}
{implode from=$__wcf->getMetaTagHandler() item=__metaTag glue="\n"}{@$__metaTag}{/implode}
{event name='metaTags'}

<!-- Stylesheets -->
{@$__wcf->getStyleHandler()->getStylesheet()}
{event name='stylesheets'}

<meta name="timezone" content="{$__wcf->user->getTimeZone()->getName()}">

{include file='headIncludeJavaScript'}
{include file='headIncludeIcons'}

{@HEAD_CODE}
