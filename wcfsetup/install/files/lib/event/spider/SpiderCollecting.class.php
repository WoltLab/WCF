<?php

namespace wcf\event\spider;

use wcf\event\IPsr14Event;
use wcf\system\spider\Spider;

/**
 * Requests the collection of spiders.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class SpiderCollecting implements IPsr14Event
{
    /**
     * @var Spider[]
     */
    private array $spiders = [];

    public function __construct()
    {
        $this->register(new Spider('AhrefsBot', 'Ahrefs', 'http://ahrefs.com/robot/'));
        $this->register(new Spider('Amazonbot', 'Amazonbot', 'https://developer.amazon.com/support/amazonbot'));
        $this->register(new Spider('Barkrowler', 'Barkrowler', 'https://www.babbar.tech/crawler'));
        $this->register(new Spider('Applebot', 'Applebot', 'https://support.apple.com/en-gb/HT204683'));
        $this->register(new Spider('archive.org_bot', 'Archive.org', 'https://archive.org/details/archive.org_bot'));
        $this->register(new Spider('AwarioSmartBot', 'AwarioSmartBot', 'https://awario.com/bots.html'));
        $this->register(new Spider('AwarioRssBot', 'AwarioRssBot', 'https://awario.com/bots.html'));
        $this->register(new Spider('Baiduspider', 'Baiduspider', 'http://www.baidu.com/search/spider.htm'));
        $this->register(new Spider('bingbot', 'Bing', 'http://www.bing.com/bingbot.htm'));
        $this->register(new Spider('BitBot', 'BitBot', 'https://bitbot.dev'));
        $this->register(new Spider('BLEXBot', 'BLEXBot', 'http://webmeup.com/crawler.html'));
        $this->register(new Spider('BoardReader', 'BoardReader', 'http://www.boardreader.com/aboutus.asp'));
        $this->register(new Spider('BublupBot', 'BublupBot', 'https://www.bublup.com/bublup-bot.html'));
        $this->register(new Spider('Buck/2.2', 'Buck', 'https://app.hypefactors.com/media-monitoring/about.html'));
        $this->register(new Spider('CensysInspect', 'Censys', 'https://about.censys.io/'));
        $this->register(new Spider('ColdFusion', 'ColdFusion'));
        $this->register(new Spider('Cookiebot', 'Cookiebot', 'https://www.cookiebot.com/'));
        $this->register(new Spider('Discordbot', 'Discord', 'https://discordapp.com/'));
        $this->register(new Spider('Domains Project', 'Domains Project', 'https://domainsproject.org/'));
        $this->register(new Spider('DotBot', 'Moz DotBot', 'http://www.opensiteexplorer.org/dotbot'));
        $this->register(
            new Spider(
                'dragonmetrics',
                'Dragon Metrics',
                'https://help.dragonmetrics.com/en/articles/213883-about-dragonbot'
            )
        );
        $this->register(
            new Spider(
                'Dubbotbot',
                'DubBot',
                'https://help.dubbot.com/en/articles/2923576-dubbot-s-crawler-ip-address-and-user-agent'
            )
        );
        $this->register(
            new Spider(
                'DuckDuckBot',
                'DuckDuckGo',
                'https://help.duckduckgo.com/duckduckgo-help-pages/results/duckduckbot/'
            )
        );
        $this->register(new Spider('ev-crawler', 'Headline Web Crawler', 'https://headline.com/legal/crawler'));
        $this->register(
            new Spider(
                'facebookexternalhit',
                'Facebook',
                'https://developers.facebook.com/docs/sharing/webmasters/crawler/'
            )
        );
        $this->register(new Spider('FeedBlitz', 'FeedBlitz', 'http://www.feedblitz.com'));
        $this->register(new Spider('FeedBurner', 'FeedBurner', 'http://www.FeedBurner.com'));
        $this->register(new Spider('Feedly', 'Feedly', 'http://www.feedly.com/fetcher.html'));
        $this->register(
            new Spider('FeedFetcher-Google', 'FeedFetcher-Google', 'http://www.google.com/feedfetcher.html')
        );
        $this->register(
            new Spider(
                'Google-Read-Aloud',
                'Google Read Aloud',
                'https://developers.google.com/search/docs/crawling-indexing/read-aloud-user-agent'
            )
        );
        $this->register(
            new Spider(
                'Google-Site-Verification/1.0',
                'Google Site Verifier',
                'https://support.google.com/webmasters/answer/9008080'
            )
        );
        $this->register(new Spider('UniversalFeedParser', 'FeedParser', 'http://www.feedparser.org'));
        $this->register(new Spider('findlinks', 'FindLinks', 'http://wortschatz.uni-leipzig.de/findlinks/'));
        $this->register(
            new Spider(
                'FreeWebMonitoring SiteChecker',
                'FreeWebMonitoring SiteChecker',
                'https://www.freewebmonitoring.com/bot.html'
            )
        );
        $this->register(new Spider('FreshRSS', 'FreshRSS', 'https://freshrss.org'));
        $this->register(new Spider('Googlebot/', 'Google', 'http://www.google.com/bot.html'));
        $this->register(new Spider('Mediapartners-Google', 'Google AdSense', 'https://www.google.com/adsense/faq'));
        $this->register(new Spider('Googlebot-Image', 'Googlebot-Image', 'http://www.googlebot.com/bot.html'));
        $this->register(
            new Spider(
                'Googlebot-Video/1.0',
                'Googlebot-Video',
                'https://developers.google.com/search/docs/advanced/crawling/overview-google-crawlers'
            )
        );
        $this->register(
            new Spider(
                'APIs-Google',
                'APIs-Google',
                'https://developers.google.com/search/docs/crawling-indexing/apis-user-agent'
            )
        );
        $this->register(
            new Spider(
                'Google Favicon',
                'Google Favicon',
                'https://developers.google.com/search/docs/appearance/favicon-in-search'
            )
        );
        $this->register(
            new Spider('Storebot-Google/1.0', 'Google StoreBot', 'https://support.google.com/merchants/answer/13294660')
        );
        $this->register(
            new Spider(
                'Google-InspectionTool/1.0',
                'Google-InspectionTool',
                'https://support.google.com/webmasters/answer/9012289'
            )
        );
        $this->register(
            new Spider(
                'GoogleStackdriverMonitoring-UptimeChecks',
                'Google Stackdriver Monitoring',
                'https://cloud.google.com/monitoring/alerts/uptime-checks'
            )
        );
        $this->register(new Spider('GoogleOther', 'GoogleOther'));
        $this->register(new Spider('Google-Ads-Creatives-Assistant', 'Google-Ads-Creatives-Assistant'));
        $this->register(new Spider('AdsBot-Google', 'Google Ads-Bot', 'http://www.google.com/adsbot.html'));
        $this->register(new Spider('HetrixTools Uptime Monitoring Bot', 'HetrixTools Uptime Monitoring Bot', 'https://hetrix.tools/uptime-monitoring-bot.html'));
        $this->register(new Spider('heritrix', 'Heritrix', 'https://github.com/internetarchive/heritrix3/wiki'));
        $this->register(new Spider('IonCrawl', 'IonCrawl', 'https://www.ionos.de/terms-gtc/faq-crawler-en'));
        $this->register(new Spider('linkdexbot', 'Linkdex', 'http://www.linkdex.com/bots/'));
        $this->register(new Spider('Magpie', 'Magpie'));
        $this->register(new Spider('MJ12bot', 'Majestics MJ12bot'));
        $this->register(new Spider('Yeti/', 'Naver Yeti', 'http://naver.me/spd'));
        $this->register(new Spider('Netcraft', 'Netcraft Web Server Survey', 'http://news.netcraft.com/'));
        $this->register(new Spider('Neticle Crawler', 'Neticle Crawler', 'https://neticle.com/bot/en/'));
        $this->register(new Spider('PagePeeker', 'PagePeeker', 'https://pagepeeker.com/robots/'));
        $this->register(new Spider('Paqlebot', 'Pagle', 'http://www.paqle.dk/about/paqlebot'));
        $this->register(new Spider('Pandalytics', 'Pandalytics', 'https://domainsbot.com/pandalytics/'));
        $this->register(new Spider('PetalBot', 'PetalBot', 'https://webmaster.petalsearch.com/site/petalbot'));
        $this->register(
            new Spider('Qualidator', 'Qualidator', 'www.qualidator.com/Web/de/Support/FAQ_OnlineTestStatistiken.htm')
        );
        $this->register(new Spider('Qwant', 'Qwant', 'https://help.qwant.com/bot/'));
        $this->register(
            new Spider(
                'SBSearch',
                'SecretSearchEngineLabs.com',
                'http://www.secretsearchenginelabs.com/secret-web-crawler.php'
            )
        );
        $this->register(new Spider('SemrushBot', 'SemrushBot', 'http://semrush.com/bot/'));
        $this->register(new Spider('SearchAtlas', 'SearchAtlas', 'https://searchatlas.com/'));
        $this->register(new Spider('SeekportBot', 'Seekport Bot', 'https://bot.seekport.com'));
        $this->register(new Spider('SentiBot', 'SentiBot', 'http://www.sentibot.eu'));
        $this->register(new Spider('SeobilityBot', 'SeobilityBot', 'https://www.seobility.net/sites/bot.html'));
        $this->register(new Spider('SEOkicks', 'SEOkicks', 'https://www.seokicks.de/robot.html'));
        $this->register(new Spider('serpstatbot', 'serpstatbot', 'http://serpstatbot.com/'));
        $this->register(
            new Spider('SeznamBot', 'Seznam Bot', 'https://o-seznam.cz/napoveda/vyhledavani/en/seznambot-crawler/')
        );
        $this->register(new Spider('SimplePie', 'SimplePie', 'https://simplepie.org'));
        $this->register(new Spider('SkypeUriPreview', 'Skype Preview', 'https://www.skype.com/'));
        $this->register(new Spider('Slackbot', 'Slackbot', 'https://api.slack.com/robots'));
        $this->register(new Spider('Sogou web spider', 'Sogou Web Spider', 'http://www.sogou.com/docs/help/webmasters.htm#07'));
        $this->register(
            new Spider('Speedy Spider', 'Speedy Spider', 'http://www.entireweb.com/about/search_tech/speedyspider/')
        );
        $this->register(new Spider('Superfeedr', 'Superfeedr', 'http://superfeedr.com'));
        $this->register(new Spider('SurdotlyBot', 'SurdotlyBot', 'http://sur.ly/bot.html'));
        $this->register(new Spider('TinEye-bot', 'TinEye', 'https://tineye.com/crawler.html'));
        $this->register(new Spider('TikTokSpider', 'TikTokSpider', 'https://www.tiktok.com/about'));
        $this->register(new Spider('Tapatalk CloudSearch', 'Tapatalk CloudSearch'));
        $this->register(new Spider('trovitBot', 'trovitBot', 'http://www.trovit.com/bot.html'));
        $this->register(
            new Spider('TelegramBot (like TwitterBot)', 'TelegramBot (like TwitterBot)', 'https://telegram.org/')
        );
        $this->register(new Spider('Testomatobot', 'TestomatoBot', 'https://www.testomato.com/bot'));
        $this->register(new Spider('trendictionbot', 'Trendiction-Bot', 'http://www.trendiction.com/bot'));
        $this->register(new Spider('TurnitinBot', 'TurnitinBot', 'http://www.turnitin.com/robot/crawlerinfo.html'));
        $this->register(new Spider('Twitterbot', 'Twitterbot', 'https://twitter.com/'));
        $this->register(new Spider('Uptime-Kuma', 'Uptime-Kuma', 'https://uptime.kuma.pet/'));
        $this->register(new Spider('UptimeRobot/2.0', 'Uptime Robot', 'http://uptimerobot.com/'));
        $this->register(new Spider('VelenPublicWebCrawler', 'Velen Crawler', 'https://velen.io'));
        $this->register(new Spider('WebCopy', 'WebCopy'));
        $this->register(new Spider('SurveyBot', 'Whois Source', 'http://www.whois.sc/info/webmasters/surveybot.html'));
        $this->register(new Spider('WinHTTP', 'WinHTTP'));
        $this->register(new Spider('WoltLabSuite', 'WoltLab Suite'));
        $this->register(new Spider('yacybot', 'YaCy-Bot', 'https://yacy.net/bot.html'));
        $this->register(new Spider('Yahoo! Slurp', 'Yahoo! Slurp', 'http://help.yahoo.com/help/us/ysearch/slurp'));
        $this->register(new Spider('Yandex', 'Yandex', 'https://yandex.com/bots'));
        $this->register(
            new Spider('ZoominfoBot', 'ZoominfoBot', 'https://www.zoominfo.com/about-zoominfo/zoominfobot')
        );
        $this->register(new Spider('360Spider', '360Spider'));
        $this->register(new Spider('GPTBot', 'GPTBot', 'https://openai.com/gptbot'));
        $this->register(new Spider('OAI-SearchBot', 'OAI-SearchBot', 'https://openai.com/searchbot'));
        $this->register(new Spider('ChatGPT-User', 'ChatGPT-User', 'https://openai.com/bot'));
        $this->register(new Spider('PerplexityBot', 'PerplexityBot', 'https://perplexity.ai/perplexitybot'));
        $this->register(new Spider('Perplexity-User', 'Perplexity-User', 'https://perplexity.ai/perplexity-user'));
        $this->register(
            new Spider(
                'meta-webindexer',
                'Meta-WebIndexer',
                'https://developers.facebook.com/docs/sharing/webmasters/web-crawlers'
            )
        );
        $this->register(
            new Spider(
                'meta-externalads',
                'Meta-ExternalAds',
                'https://developers.facebook.com/docs/sharing/webmasters/web-crawlers'
            )
        );
        $this->register(
            new Spider(
                'meta-externalagent',
                'Meta-ExternalAgent',
                'https://developers.facebook.com/docs/sharing/webmasters/web-crawlers'
            )
        );
        $this->register(
            new Spider(
                'meta-externalfetcher',
                'Meta-ExternalFetcher',
                'https://developers.facebook.com/docs/sharing/webmasters/web-crawlers'
            )
        );
        $this->register(new Spider('Bytespider', 'Bytespider'));
        $this->register(new Spider('PlagAwareBot', 'PlagAware', 'https://www.plagaware.com/bot'));
        $this->register(
            new Spider(
                'DuckAssistBot',
                'DuckAssistBot',
                'https://duckduckgo.com/duckduckgo-help-pages/results/duckassistbot'
            )
        );
        $this->register(new Spider('CCBot', 'Common Crawl Bot', 'https://commoncrawl.org/faq'));
        $this->register(new Spider('DataForSeoBot', 'DataForSEO Link Bot', 'https://dataforseo.com/dataforseo-bot'));
        $this->register(
            new Spider(
                'Gemini-Deep-Research',
                'Gemini Deep Research',
                'https://gemini.google/overview/deep-research/'
            )
        );
        $this->register(new Spider('quillbot', 'Quillbot', 'https://quillbot.com/'));
        $this->register(new Spider('CriteoBot', 'Criteo', 'https://www.criteo.com/criteo-crawler/'));
        $this->register(new Spider('MojeekBot', 'Mojeek', 'https://www.mojeek.com/bot.html'));
    }

    /**
     * Registers a spider.
     */
    public function register(Spider $spider): void
    {
        if (\array_key_exists($spider->identifier, $this->spiders)) {
            throw new \InvalidArgumentException('Spider with identifier ' . $spider->identifier . ' already exists');
        }
        $this->spiders[$spider->identifier] = $spider;
    }

    /**
     * @return Spider[]
     */
    public function getSpiders(): array
    {
        return $this->spiders;
    }
}
