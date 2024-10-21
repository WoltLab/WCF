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
        $this->register(new Spider('ABCdatos', 'ABCdatos BotLink', 'http://www.robotstxt.org/db/abcdatos.html'));
        $this->register(new Spider('AbraveSpider', 'Abrave', 'http://robot.abrave.co.uk/'));
        $this->register(new Spider('Accelatech RSSCrawler', 'Accelatech'));
        $this->register(new Spider('Accoona-AI-Agent', 'Accoona', 'https://www.accoona.com/about/'));
        $this->register(new Spider('aconon Index', 'aconon Index (raubfische.de)'));
        $this->register(new Spider('AcoonBot', 'Acoon', 'http://www.acoon.de/robot.asp'));
        $this->register(new Spider('Ahoy!', 'Ahoy!', 'http://www.robotstxt.org/db/ahoythehomepagefinder.html'));
        $this->register(new Spider('AhrefsBot', 'Ahrefs', 'http://ahrefs.com/robot/'));
        $this->register(new Spider('AlkalineBOT', 'Alkaline', 'http://www.robotstxt.org/db/Alkaline.html'));
        $this->register(new Spider('Amazonbot', 'Amazonbot', 'https://developer.amazon.com/support/amazonbot'));
        $this->register(new Spider('AlexaBOT', 'Alexa', 'http://www.alexa.com/help/webmasters'));
        $this->register(
            new Spider(
                'http://www.almaden.ibm.com/cs/crawler',
                'Almaden Crawler',
                'http://www.almaden.ibm.com/cs/crawler/'
            )
        );
        $this->register(new Spider('Barkrowler', 'Barkrowler', 'https://www.babbar.tech/crawler'));
        $this->register(new Spider('EMC Spider', 'ananzi'));
        $this->register(new Spider('Anthill', 'Anthill', 'http://www.robotstxt.org/db/anthill.html'));
        $this->register(new Spider('Aport', 'Aport', 'http://www.aport.ru/'));
        $this->register(new Spider('AppleSyndication', 'Apple'));
        $this->register(new Spider('Applebot', 'Applebot', 'https://support.apple.com/en-gb/HT204683'));
        $this->register(
            new Spider('Arachnophilia', 'Arachnophilia', 'http://www.robotstxt.org/db/arachnophilia.html')
        );
        $this->register(new Spider('Araneo', 'Araneo', 'http://www.robotstxt.org/db/araneo.html'));
        $this->register(
            new Spider('ArchitextSpider', 'ArchitextSpider', 'http://www.robotstxt.org/db/architext.html')
        );
        $this->register(new Spider('archive.org_bot', 'Archive.org', 'https://archive.org/details/archive.org_bot'));
        $this->register(new Spider('arks/1.0', 'arks', 'http://www.robotstxt.org/db/arks.html'));
        $this->register(new Spider('ASpider', 'ASpider', 'http://www.robotstxt.org/db/aspider.html'));
        $this->register(new Spider('ATN_Worldwide', 'ATN Worldwide', 'http://www.robotstxt.org/db/atn.html'));
        $this->register(new Spider('Atomz', 'Atomz.com', 'http://www.robotstxt.org/db/atomz.html'));
        $this->register(new Spider('AURESYS', 'AURESYS', 'http://www.robotstxt.org/db/auresys.html'));
        $this->register(new Spider('AwarioSmartBot', 'AwarioSmartBot', 'https://awario.com/bots.html'));
        $this->register(new Spider('AwarioRssBot', 'AwarioRssBot', 'https://awario.com/bots.html'));
        $this->register(new Spider('BackRub', 'BackRub', 'http://www.robotstxt.org/db/backrub.html'));
        $this->register(new Spider('Baiduspider', 'Baiduspider', 'http://www.baidu.com/search/spider.htm'));
        $this->register(new Spider('BecomeBot', 'BecomeBot', 'http://www.become.com/webmasters.html'));
        $this->register(new Spider('Big Brother', 'Big Brother', 'http://www.robotstxt.org/db/bigbrother.html'));
        $this->register(new Spider('BigmirSpider', 'Bigmir', 'http://www.bigmir.net/'));
        $this->register(new Spider('bingbot', 'Bing', 'http://www.bing.com/bingbot.htm'));
        $this->register(new Spider('Bitacle bot', 'Bitacle', 'http://bitacle.org/'));
        $this->register(new Spider('BitBot', 'BitBot', 'https://bitbot.dev'));
        $this->register(new Spider('Biz360 Spider', 'Biz', 'http://www.biz360.com'));
        $this->register(new Spider('Bjaaland', 'Bjaaland', 'http://www.robotstxt.org/db/bjaaland.html'));
        $this->register(new Spider('BlackWidow', 'BlackWidow', 'http://www.robotstxt.org/db/blackwidow.html'));
        $this->register(new Spider('BLEXBot', 'BLEXBot', 'http://webmeup.com/crawler.html'));
        $this->register(new Spider('BlogCrawler by Xango', 'BlogCrawler'));
        $this->register(new Spider('blogdb', 'BlogDb', 'http://blogdb.jp'));
        $this->register(new Spider('blog search engine by BlogFan.ORG', 'BlogFan', 'http://www.blogfan.org'));
        $this->register(new Spider('Bloglines', 'Bloglies', 'http://www.bloglines.com'));
        $this->register(new Spider('BlogPulse (ISSpider-3.0)', 'BlogPulse'));
        $this->register(new Spider('BlogSearch', 'BlogSearch', 'http://www.icerocket.com'));
        $this->register(new Spider('BlogsNowBot', 'BlogsNow', 'http://www.blogsnow.com/'));
        $this->register(new Spider('BlogStreetBot', 'BlogStreetBot', 'http://www.blogstreet.com/'));
        $this->register(new Spider('Bulkfeeds', 'BlogStreetBot', 'http://bulkfeeds.net'));
        $this->register(new Spider('BoardPulse', 'BoardPulse', 'http://www.boardpulse.com/'));
        $this->register(new Spider('BoardReader', 'BoardReader', 'http://www.boardreader.com/aboutus.asp'));
        $this->register(new Spider('BoardViewer', 'BoardViewer', 'http://www.boardviewer.com/'));
        $this->register(new Spider('boitho.com-robot', 'Boitho', 'http://www.boitho.com/bot.html'));
        $this->register(new Spider('borg-bot', 'Borg-Bot', 'http://www.robotstxt.org/db/borg-bot.html'));
        $this->register(new Spider('BSpider', 'BSpider', 'http://www.robotstxt.org/db/bspider.html'));
        $this->register(new Spider('BublupBot', 'BublupBot', 'https://www.bublup.com/bublup-bot.html'));
        $this->register(new Spider('Buck/2.2', 'Buck', 'https://app.hypefactors.com/media-monitoring/about.html'));
        $this->register(new Spider('CACTVS Chemistry Spider', 'CACTVS Chemistry'));
        $this->register(new Spider('Calif', 'Calif'));
        $this->register(new Spider('CaRP/3.6Evolution', 'CaRP', 'http://www.biz360.com'));
        $this->register(new Spider('CensysInspect', 'Censys', 'https://about.censys.io/'));
        $this->register(new Spider('Checkbot', 'Checkbot'));
        $this->register(new Spider('ChristCrawler.com', 'ChristCrawler.com'));
        $this->register(new Spider('www.cienciaficcion.net', 'cIeNcIaFiCcIoN.nEt'));
        $this->register(new Spider('Cincraw', 'Cincraw', 'http://cincrawdata.net/bot/'));
        $this->register(new Spider('CipinetBot', 'Cipinet', 'http://www.cipinet.com/bot.html'));
        $this->register(new Spider('CJNetworkQuality', 'CJNetworkQuality', 'http://www.cj.com/networkquality/'));
        $this->register(new Spider('CMC/0.01', 'CMC/0.01'));
        $this->register(new Spider('ColdFusion', 'ColdFusion'));
        $this->register(new Spider('combine', 'Combine System'));
        $this->register(new Spider('Crawler (cometsearch@cometsystems.com)', 'cometsystems.com'));
        $this->register(new Spider('ComputingSite Robi/1.0', 'ComputingSite Robi/1.0'));
        $this->register(new Spider('conceptbot', 'Conceptbot'));
        $this->register(new Spider('Cookiebot', 'Cookiebot', 'https://www.cookiebot.com/'));
        $this->register(new Spider('Cooby.de Crawler', 'Cooby.de Crawler'));
        $this->register(new Spider('CoolBot', 'CoolBot'));
        $this->register(new Spider('Cusco', 'Cusco'));
        $this->register(new Spider('CyberSpyder', 'CyberSpyder'));
        $this->register(new Spider('daypopbot', 'daypop'));
        $this->register(new Spider('DesertRealm.com', 'Desert Realm'));
        $this->register(new Spider('Deweb', 'DeWeb(c)'));
        $this->register(new Spider('Die Blinde Kuh', 'Die Blinde Kuh', 'http://www.robotstxt.org/db/blindekuh.html'));
        $this->register(new Spider('dienstspider', 'DienstSpider'));
        $this->register(new Spider('Digger/1.0 JDK/1.3.0', 'Digger'));
        $this->register(new Spider('Digimarc WebReader', 'Digimarc MarcSpider'));
        $this->register(new Spider('Digimarc CGIReader', 'Digimarc Marcspider/CGI'));
        $this->register(new Spider('DIIbot', 'Digital Integrity Robot'));
        $this->register(new Spider('grabber', 'Direct Hit Grabber'));
        $this->register(new Spider('discobot', 'Discovery', 'http://discoveryengine.com/discobot.html'));
        $this->register(new Spider('Discordbot', 'Discord', 'https://discordapp.com/'));
        $this->register(new Spider('DNAbot/1.0', 'DNAbot'));
        $this->register(new Spider('Domains Project', 'Domains Project', 'https://domainsproject.org/'));
        $this->register(new Spider('DotBot', 'Moz DotBot', 'http://www.opensiteexplorer.org/dotbot'));
        $this->register(new Spider('DragonBot/1.0 libwww/5.0', 'DragonBot'));
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
        $this->register(new Spider('DWCP/2.0', 'DWCP (Dridus\' Web Cataloging Project)'));
        $this->register(
            new Spider('e-SocietyRobot', 'e-Society', 'http://www.yama.info.waseda.ac.jp/~yamana/es/index_eng.htm')
        );
        $this->register(new Spider('exactseek-pagereaper', 'eaxactseek-page'));
        $this->register(new Spider('EbiNess/0.01a', 'EbiNess'));
        $this->register(new Spider('edgeio-retriever', 'Edgeio', 'http://www.edgeio.com'));
        $this->register(new Spider('EIT-Link-Verifier-Robot/0.2', 'EIT Link Verifier Robot'));
        $this->register(new Spider('elfinbot', 'ELFINBOT'));
        $this->register(new Spider('Emacs-w3/v[0-9\\.]+', 'Emacs-w3 Search Engine'));
        $this->register(new Spider('esther', 'Esther'));
        $this->register(new Spider('EuripBot/', 'EuripBot'));
        $this->register(new Spider('ev-crawler', 'Headline Web Crawler', 'https://headline.com/legal/crawler'));
        $this->register(new Spider('Evliya Celebi', 'Evliya Celebi'));
        $this->register(new Spider('ExactSeek_Spider', 'ExactSeek_Spider', 'http://www.askjeevs.com'));
        $this->register(new Spider('NG/2.0', 'ExaLead', 'http://botspotter.net/bs-389.html'));
        $this->register(new Spider('ExaBot', 'ExaLead Beta', 'http://beta.exalead.com/search/C=0/2p=Help.7'));
        $this->register(new Spider('ExaLead', 'ExaLead', 'http://www.exalead.com/search'));
        $this->register(
            new Spider(
                'facebookexternalhit',
                'Facebook',
                'https://developers.facebook.com/docs/sharing/webmasters/crawler/'
            )
        );
        $this->register(
            new Spider('fast-webcrawler', 'FAST / AlltheWeb', 'http://help.yahoo.com/help/us/ysearch/slurp/index.html')
        );
        $this->register(new Spider('FastCrawler', 'FastCrawler'));
        $this->register(new Spider('Feed24.com', 'Feed24', 'http://www.feed24.com'));
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
        $this->register(new Spider('Google-Extended', 'Google-Extended'));
        $this->register(new Spider('UniversalFeedParser', 'FeedParser', 'http://www.feedparser.org'));
        $this->register(new Spider('Feedster Crawler', 'Feedster', 'http://www.feedstermedia.com/'));
        $this->register(new Spider('FEHLSTART Superspider', 'FEHLSTART'));
        $this->register(new Spider('FelixIDE', 'Felix IDE'));
        $this->register(new Spider('ESIRover', 'FetchRover'));
        $this->register(new Spider('fido', 'fido'));
        $this->register(new Spider('findlinks', 'FindLinks', 'http://wortschatz.uni-leipzig.de/findlinks/'));
        $this->register(new Spider('FindoryBot', 'Findroy', 'http://www.findory.com'));
        $this->register(new Spider('Fish-Search-Robot', 'Fish search'));
        $this->register(new Spider('Mozilla/4.0 (compatible: FDSE robot)', 'Fluid Dynamics'));
        $this->register(new Spider('fouineur.9bit.qc.ca', 'Fouineur'));
        $this->register(new Spider('Freecrawl', 'Freecrawl'));
        $this->register(
            new Spider(
                'FreeWebMonitoring SiteChecker',
                'FreeWebMonitoring SiteChecker',
                'https://www.freewebmonitoring.com/bot.html'
            )
        );
        $this->register(new Spider('FreshpingBot', 'Freshping', 'https://freshping.io/'));
        $this->register(new Spider('FreshRSS', 'FreshRSS', 'https://freshrss.org'));
        $this->register(new Spider('FunnelWeb', 'FunnelWeb'));
        $this->register(new Spider('GaisBot', 'Gais', 'http://gais.cs.ccu.edu.tw/robot.php'));
        $this->register(new Spider('gamekitbot', 'GAMEKIT', 'http://www.uchoose.de/crawler/gamekitbot/'));
        $this->register(new Spider('gammaSpider', 'gammaSpider'));
        $this->register(new Spider('gazz', 'gazz'));
        $this->register(new Spider('gcreep', 'GCreep'));
        $this->register(new Spider('genieBot', 'genieBot', 'http://64.5.245.11/faq/faq.html'));
        $this->register(new Spider('geourl', 'GeoURL', 'http://geourl.org/bot.html'));
        $this->register(new Spider('GetterroboPlus', 'GetterroboPlus Puu'));
        $this->register(new Spider('GetURL.rexx', 'GetURL'));
        $this->register(new Spider('Gigabot', 'Gigabot', 'http://www.gigablast.com/spider.html'));
        $this->register(new Spider('Girafabot', 'Girafabot', 'http://www.girafa.com/'));
        $this->register(new Spider('Goku', 'Goku', 'http://goku.ru/bot.htm; bot@goku.ru'));
        $this->register(new Spider('Golem', 'Golem'));
        $this->register(new Spider('gonzo', 'Gonzo'));
        $this->register(new Spider('Googlebot/', 'Google', 'http://www.google.com/bot.html'));
        $this->register(new Spider('Mediapartners-Google', 'Google AdSense', 'https://www.google.com/adsense/faq'));
        $this->register(new Spider('Googlebot-Image', 'Googlebot-Image', 'http://www.googlebot.com/bot.html'));
        $this->register(new Spider('Googlebot-Mobile', 'Googlebot-Mobile', 'http://www.google.com/bot.html'));
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
        $this->register(new Spider('Google-Ads-Creatives-Assistant', 'Google-Ads-Creatives-Assistant'));
        $this->register(new Spider('Google-AdWords-Express', 'Google-AdWords-Express'));
        $this->register(new Spider('AdsBot-Google', 'Google Ads-Bot', 'http://www.google.com/adsbot.html'));
        $this->register(
            new Spider('AdsBot-Google-Mobile', 'Google Ads-Bot Mobile', 'http://www.google.com/adsbot.html')
        );
        $this->register(new Spider('Gpostbot', 'Gpostbot', 'http://www.gpost.info/help.php?c=bot'));
        $this->register(new Spider('griffon', 'Griffon'));
        $this->register(new Spider('Gromit', 'Gromit'));
        $this->register(new Spider('http://grub.org', 'Grub Client'));
        $this->register(new Spider('Gulper Web Bot', 'Gulper Bot'));
        $this->register(new Spider('havIndex', 'havIndex'));
        $this->register(new Spider('HeinrichderMiragoRobot', 'HeinrichderMiragoRobot'));
        $this->register(new Spider('HenryTheMiragoRobot', 'HenryTheMiragoRobot'));
        $this->register(new Spider('HetrixTools Uptime Monitoring Bot', 'HetrixTools Uptime Monitoring Bot', 'https://hetrix.tools/uptime-monitoring-bot.html'));
        $this->register(new Spider('heritrix', 'Heritrix', 'https://github.com/internetarchive/heritrix3/wiki'));
        $this->register(new Spider('HKU WWW Robot', 'HKU WWW Octopus'));
        $this->register(new Spider('HolyCowDude', 'HolyCowDude', 'http://www.holycowdude.com/spider.htm'));
        $this->register(new Spider('HomeTags', 'HomeTags', 'http://www.hometags.nl/bot'));
        $this->register(new Spider('Hometown', 'Hometown'));
        $this->register(new Spider('htdig', 'ht://Dig'));
        $this->register(new Spider('AITCSRobot', 'HTML Index'));
        $this->register(new Spider('HTMLgobble', 'HTMLgobble'));
        $this->register(new Spider('I Robot', 'I, Robot'));
        $this->register(new Spider('iajaBot', 'iajaBot'));
        $this->register(new Spider('IBM_Planetwide', 'IBM_Planetwide'));
        $this->register(new Spider('+http://www.icerocket.com/', 'IceRocket', 'http://www.icerocket.com/'));
        $this->register(new Spider('ichiro', 'ichiro'));
        $this->register(
            new Spider('IlTrovatore-Setaccio', 'IlTrovatore-Setaccio', 'http://www.iltrovatore.it/aiuto/faq.html')
        );
        $this->register(new Spider('image.kapsi.net', 'image.kapsi.net'));
        $this->register(new Spider('Mozilla 3.01 PBWF (Win95)', 'Imagelock'));
        $this->register(new Spider('IncyWincy', 'IncyWincy'));
        $this->register(new Spider('infoobot', 'infoobot', 'https://www.infoo.nl/bot.html'));
        $this->register(new Spider('Informant', 'Informant'));
        $this->register(new Spider('InfoSeek Robot', 'InfoSeek Robot 1.0'));
        $this->register(new Spider('Infoseek Sidewinder', 'Infoseek Sidewinder'));
        $this->register(new Spider('InfoSpiders', 'InfoSpiders'));
        $this->register(new Spider('INGRID', 'Ingrid'));
        $this->register(new Spider('slurp@inktomi', 'Inktomi'));
        $this->register(new Spider('Insitor', 'Insitor', 'http://www.insitor.de/'));
        $this->register(new Spider('inspectorwww', 'Inspector Web'));
        $this->register(new Spider('IAGENT', 'IntelliAgent'));
        $this->register(new Spider('Intelliseek', 'Intelliseek', 'http://www.intelliseek.com/'));
        $this->register(new Spider('Internet Cruiser Robot', 'Internet Cruiser'));
        $this->register(new Spider('internetseer', 'Internet Seer'));
        $this->register(new Spider('sharp-info-agent', 'Internet Shinchakubin'));
        $this->register(new Spider('InternetLinkAgent', 'InternetLinkAgent'));
        $this->register(new Spider('IRLbot', 'IRL Crawler', 'http://irl.cs.tamu.edu/crawler'));
        $this->register(new Spider('IonCrawl', 'IonCrawl', 'https://www.ionos.de/terms-gtc/faq-crawler-en'));
        $this->register(new Spider('Iron33', 'Iron33'));
        $this->register(new Spider('IsraeliSearch', 'Israeli-search'));
        $this->register(new Spider('itchBot', 'itch'));
        $this->register(new Spider('JavaBee', 'JavaBee'));
        $this->register(new Spider('JBot', 'JBot'));
        $this->register(new Spider('JCrawler', 'JCrawler'));
        $this->register(new Spider('JetBot', 'JetEye', 'http://www.jeteye.com/jetbot.html'));
        $this->register(new Spider('JoBo', 'JoBo'));
        $this->register(new Spider('Jobot', 'Jobot'));
        $this->register(new Spider('jobs.de', 'Jobs.de', 'http://www.jobs.de/'));
        $this->register(new Spider('JoeBot', 'JoeBot'));
        $this->register(new Spider('jumpstation', 'JumpStation'));
        $this->register(new Spider('Katipo', 'Katipo'));
        $this->register(new Spider('KDD-Explorer', 'KDD-Explorer'));
        $this->register(new Spider('KIT-Fireball', 'KIT-Fireball'));
        $this->register(new Spider('KO_Yappo_Robot', 'KO_Yappo_Robot'));
        $this->register(new Spider('LabelGrab', 'LabelGrabber'));
        $this->register(new Spider('larbin', 'larbin'));
        $this->register(new Spider('legs', 'legs'));
        $this->register(new Spider('linkdexbot', 'Linkdex', 'http://www.linkdex.com/bots/'));
        $this->register(new Spider('LinkScan Server', 'LinkScan'));
        $this->register(new Spider('LinkWalker', 'LinkWalker'));
        $this->register(new Spider('Linguee Bot', 'Linguee', 'http://www.linguee.com/bot'));
        $this->register(new Spider('livedoorCheckers/', 'livedoorCheckers'));
        $this->register(new Spider('Lockon', 'Lockon'));
        $this->register(new Spider('logo.gif crawler', 'logo.gif'));
        $this->register(new Spider('Lycos', 'Lycos'));
        $this->register(new Spider('Magpie', 'Magpie'));
        $this->register(new Spider('MJ12bot', 'Majestics MJ12bot'));
        $this->register(new Spider('Mammoth', 'Mammoth', 'http://www.sli-systems.com'));
        $this->register(new Spider('Marvin', 'Marvin'));
        $this->register(new Spider('marvin/infoseek', 'marvin/infoseek'));
        $this->register(new Spider('M/3.8', 'Mattie'));
        $this->register(new Spider('MediaFox', 'MediaFox'));
        $this->register(
            new Spider('memorybot', 'Memorybot', 'http://archivethe.net/en/index.php/about/internet_memory1')
        );
        $this->register(new Spider('mercator', 'Mercator', 'http://research.compaq.com/SRC/mercator/'));
        $this->register(new Spider('MerzScope', 'MerzScope'));
        $this->register(new Spider('METASpider', 'META', 'http://www.meta.com.ua/'));
        $this->register(new Spider('MetaGer-LinkChecker', 'MetaGer'));
        $this->register(new Spider('MindCrawler', 'MindCrawler'));
        $this->register(new Spider('Miva', 'Miva'));
        $this->register(new Spider('UdmSearch', 'mnoGoSearch'));
        $this->register(new Spider('moget', 'moget'));
        $this->register(new Spider('MOMspider', 'MOMspider'));
        $this->register(new Spider('Monster', 'Monster'));
        $this->register(new Spider('Moreoverbot', 'Moreover', 'http://www.moreover.com'));
        $this->register(new Spider('msnbot', 'MSNBot', 'http://search.msn.com/msnbot.htm'));
        $this->register(new Spider('MSRBOT', 'MSRBOT', 'http://research.microsoft.com/research/sv/msrbot/'));
        $this->register(new Spider('MuscatFerret', 'Muscat Ferret'));
        $this->register(new Spider('MwdSearch', 'Mwd.Search'));
        $this->register(new Spider('NPBot', 'NameProtect'));
        $this->register(new Spider('NaverBot', 'NaverBot', 'http://www.spidermatic.com/en/robot-spider/20'));
        $this->register(new Spider('NDSpider', 'NDSpider'));
        $this->register(new Spider('NEC-MeshExplorer', 'NEC-MeshExplorer'));
        $this->register(new Spider('Nederland.zoek', 'Nederland.zoek'));
        $this->register(new Spider('Neevabot', 'Neeva', 'https://neeva.com/neevabot'));
        $this->register(new Spider('NerdyBot', 'NerdyBot', 'http://nerdybot.com/'));
        $this->register(new Spider('NetCarta CyberPilot Pro', 'NetCarta WebMap'));
        $this->register(new Spider('Netcraft', 'Netcraft Web Server Survey', 'http://news.netcraft.com/'));
        $this->register(new Spider('Neticle Crawler', 'Neticle Crawler', 'https://neticle.com/bot/en/'));
        $this->register(new Spider('NetMechanic', 'NetMechanic'));
        $this->register(new Spider('NetScoop', 'NetScoop'));
        $this->register(new Spider('newscan-online', 'newscan-online'));
        $this->register(
            new Spider('NextGenSearchBot 1', 'NextGenSearchBot', 'http://www.zoominfo.com/NextGenSearchBot')
        );
        $this->register(new Spider('NHSEWalker', 'NHSE Web Forager'));
        $this->register(new Spider('NIF', 'NIF', 'http://www.newsisfree.com/robot.php'));
        $this->register(new Spider('NimbleCrawler', 'NimbleCrawler', 'http://www.healthline.com/aboutus.jsp'));
        $this->register(new Spider('Nomad', 'Nomad'));
        $this->register(new Spider('Norbert the Spider', 'Norbert', 'http://www.Burf.com'));
        $this->register(new Spider('Gulliver', 'Northern Light'));
        $this->register(new Spider('explorersearch', 'nzexplorer'));
        $this->register(new Spider('Occam', 'Occam'));
        $this->register(new Spider('Ocelli', 'Ocelli', 'http://www.globalspec.com/Ocelli'));
        $this->register(new Spider('Online24-Bot', 'Online24-Bot'));
        $this->register(new Spider('Openbot', 'Openbot', 'http://www.openfind.com.tw/robot.html'));
        $this->register(new Spider('Openfind', 'Openfind data gatherer'));
        $this->register(new Spider('Orbsearch', 'Orb Search'));
        $this->register(new Spider('PackRat', 'Pack Rat'));
        $this->register(new Spider('PageBoy', 'PageBoy'));
        $this->register(new Spider('PagePeeker', 'PagePeeker', 'https://pagepeeker.com/robots/'));
        $this->register(new Spider('Pandalytics', 'Pandalytics', 'https://domainsbot.com/pandalytics/'));
        $this->register(new Spider('ParaSite', 'ParaSite'));
        $this->register(new Spider('Patric', 'Patric'));
        $this->register(new Spider('PEGASUS', 'pegasus'));
        $this->register(new Spider('PerlCrawler/1.0 Xavatoria/2.0', 'PerlCrawler 1.0'));
        $this->register(new Spider('PetalBot', 'PetalBot', 'https://aspiegel.com/petalbot'));
        $this->register(new Spider('PGP-KA', 'PGP Key Agent'));
        $this->register(new Spider('Duppies', 'Phantom'));
        $this->register(new Spider('phpdig', 'PhpDig'));
        $this->register(new Spider('PiltdownMan', 'PiltdownMan'));
        $this->register(new Spider('Pimptrain\'s robot', 'Pimptrain.com\'s'));
        $this->register(new Spider('pingalink', 'PingALink'));
        $this->register(new Spider('Pioneer', 'Pioneer'));
        $this->register(new Spider('PluckFeedCrawler', 'Pluck', 'http://www.pluck.com'));
        $this->register(new Spider('PlumtreeWebAccessor', 'PlumtreeWebAccessor'));
        $this->register(new Spider('PodNova', 'PodNova', 'http://www.podnova.com'));
        $this->register(new Spider('Pompos', 'Pompos', 'http://dir.com/pompos.html'));
        $this->register(new Spider('Poppi', 'Poppi'));
        $this->register(new Spider('publiclibraryarchive.org', 'publiclibraryarchive.org'));
        $this->register(new Spider('gestaltIconoclast', 'Popular Iconoclast'));
        $this->register(new Spider('PortalJuice.com', 'Portal Juice'));
        $this->register(new Spider('PortalBSpider', 'PortalB Spider'));
        $this->register(
            new Spider('Qualidator', 'Qualidator', 'www.qualidator.com/Web/de/Support/FAQ_OnlineTestStatistiken.htm')
        );
        $this->register(new Spider('www.kolinka.com', 'Project Kolinka Forum Search', 'http://www.kolinka.com/'));
        $this->register(new Spider('psbot', 'psbot'));
        $this->register(new Spider('Qango.com Web Directory', 'Qango', 'http://www.qango.com'));
        $this->register(new Spider('Qwant', 'Qwant', 'https://help.qwant.com/bot/'));
        $this->register(
            new Spider(
                'SBSearch',
                'SecretSearchEngineLabs.com',
                'http://www.secretsearchenginelabs.com/secret-web-crawler.php'
            )
        );
        $this->register(new Spider('SemrushBot', 'SemrushBot', 'http://semrush.com/bot/'));
        $this->register(new Spider('StackRambler', 'Rambler', 'http://www.rambler.ru/'));
        $this->register(new Spider('Raven', 'Raven Search'));
        $this->register(new Spider('Resume Robot', 'Resume Robot'));
        $this->register(new Spider('Road Runner: ImageScape Robot', 'Road Runner: The ImageScape Robot'));
        $this->register(new Spider('RHCS', 'RoadHouse Crawling System'));
        $this->register(new Spider('Robbie', 'Robbie the Robot'));
        $this->register(new Spider('RoboCrawl', 'RoboCrawl'));
        $this->register(new Spider('Robofox', 'RoboFox'));
        $this->register(new Spider('Robot du CRIM 1.0a', 'Robot Francoroute'));
        $this->register(new Spider('Robozilla', 'Robozilla'));
        $this->register(new Spider('Roverbot', 'Roverbot'));
        $this->register(new Spider('RSS-SPIDER', 'RSS Feed Seeker', 'http://www.rss-spider.com/fsb.php'));
        $this->register(new Spider('RuLeS', 'RuLeS'));
        $this->register(new Spider('RyzeCrawler', 'RyzeCrawler', 'http://www.domain2day.nl/crawler/'));
        $this->register(new Spider('SafetyNet Robot', 'SafetyNet'));
        $this->register(new Spider('SBIder', 'SBIder.', 'http://www.sitesell.com/sbider.html'));
        $this->register(new Spider('Scharia', 'Scharia'));
        $this->register(new Spider('Science-Index', 'Science-Index'));
        $this->register(new Spider('Scooter', 'Scooter'));
        $this->register(new Spider('SearchAtlas', 'SearchAtlas', 'https://searchatlas.com/'));
        $this->register(new Spider('SearchNZ', 'SearchNZ', 'http://www.searchnz.co.nz/'));
        $this->register(new Spider('searchprocess', 'SearchProcess'));
        $this->register(
            new Spider('SearchmetricsBot', 'SearchmetricsBot', 'http://www.searchmetrics.com/en/searchmetrics-bot/')
        );
        $this->register(new Spider('Seekbot', 'Seekbot', 'http://www.seekbot.net/bot.html'));
        $this->register(new Spider('SeekportBot', 'Seekport Bot', 'https://bot.seekport.com'));
        $this->register(new Spider('Senrigan', 'Senrigan'));
        $this->register(new Spider('Sensis Web Crawler', 'Sensis Web Crawler', 'http://www.sensis.com.au/help.do'));
        $this->register(new Spider('SentiBot', 'SentiBot', 'http://www.sentibot.eu'));
        $this->register(new Spider('SEO Scanner', 'SEO Scanner'));
        $this->register(new Spider('SeobilityBot', 'SeobilityBot', 'https://www.seobility.net/sites/bot.html'));
        $this->register(new Spider('SEOkicks', 'SEOkicks', 'https://www.seokicks.de/robot.html'));
        $this->register(new Spider('seostar.co', 'Seostar', 'https://seostar.co/robot/'));
        $this->register(
            new Spider('SerendeputyBot', 'SerendeputyBot', 'http://serendeputy.com/about/serendeputy-bot')
        );
        $this->register(new Spider('serpstatbot', 'serpstatbot', 'http://serpstatbot.com/'));
        $this->register(new Spider('SeznamBot/3.2', 'Seznam Bot', 'http://napoveda.seznam.cz/en/seznambot-intro/'));
        $this->register(new Spider('SG-Scout', 'SG-Scout'));
        $this->register(new Spider('Shagseeker', 'ShagSeeker'));
        $this->register(new Spider('Shai\'Hulud', 'Shai\'Hulud'));
        $this->register(new Spider('SimBot/1.0', 'Simmany Robot Ver1.0'));
        $this->register(new Spider('SimplePie', 'SimplePie', 'https://simplepie.org'));
        $this->register(new Spider('SkypeUriPreview', 'Skype Preview', 'https://www.skype.com/'));
        $this->register(new Spider('ssearcher100', 'Site Searcher'));
        $this->register(new Spider('Site Valet', 'Site Valet'));
        $this->register(new Spider('http://www.site-list.net', 'Site-List', 'http://www.site-list.net'));
        $this->register(new Spider('SiteTech-Rover', 'SiteTech-Rover'));
        $this->register(new Spider('+SitiDi.net/SitiDiBot/', 'SitiDi.net/SitiDiBot'));
        $this->register(new Spider('aWapClient', 'Skymob.com'));
        $this->register(new Spider('Slack', 'Slackbot', 'https://api.slack.com/robots'));
        $this->register(new Spider('SLCrawler', 'SLCrawler'));
        $this->register(new Spider('Sleek Spider', 'Sleek'));
        $this->register(new Spider('ESISmartSpider', 'Smart Spider'));
        $this->register(new Spider('Snapbot', 'Snapbot', 'http://www.snap.com/'));
        $this->register(new Spider('Snooper', 'Snooper'));
        $this->register(new Spider('sohu-search', 'sohu-search'));
        $this->register(new Spider('Solbot', 'Solbot'));
        $this->register(
            new Spider('Speedy Spider', 'Speedy Spider', 'http://www.entireweb.com/about/search_tech/speedyspider/')
        );
        $this->register(new Spider('Sphere Scout', 'Sphere'));
        $this->register(new Spider('Sphider2', 'Sphider'));
        $this->register(new Spider('SpiderBot', 'SpiderBot'));
        $this->register(new Spider('spiderline', 'Spiderline Crawler'));
        $this->register(new Spider('SpiderMan', 'SpiderMan'));
        $this->register(new Spider('SpiderView', 'SpiderView(tm)'));
        $this->register(new Spider('mouse.house', 'spider_monkey'));
        $this->register(new Spider('suke', 'Suke'));
        $this->register(new Spider('suntek', 'suntek search engine'));
        $this->register(new Spider('Superfeedr', 'Superfeedr', 'http://superfeedr.com'));
        $this->register(new Spider('SurdotlyBot', 'SurdotlyBot', 'http://sur.ly/bot.html'));
        $this->register(new Spider('Szukacz', 'Szukacz', 'http://www.szukacz.pl/html/RobotEnglishVersion.html'));
        $this->register(new Spider('T-H-U-N-D-E-R-S-T-O-N-E', 'T-H-U-N-D-E-R-S-T-O-N-E'));
        $this->register(new Spider('TinEye Crawler', 'TinEye', 'http://tineye.com/crawler.html'));
        $this->register(new Spider('Black Widow', 'TACH Black Widow'));
        $this->register(new Spider('Tapatalk CloudSearch', 'Tapatalk CloudSearch'));
        $this->register(new Spider('Tarantula', 'Tarantula'));
        $this->register(new Spider('tarspider', 'tarspider'));
        $this->register(new Spider('dlw3robot', 'Tcl W3 Robot'));
        $this->register(new Spider('TechBOT', 'TechBOT'));
        $this->register(new Spider('Technoratibot', 'Technorati', 'http://technorati.com/about/'));
        $this->register(new Spider('Templeton', 'Templeton'));
        $this->register(new Spider('teoma', 'Teoma/Ask Jeeves', 'http://sp.teoma.com/docs/teoma/about/'));
        $this->register(new Spider('trovitBot', 'trovitBot', 'http://www.trovit.com/bot.html'));
        $this->register(new Spider('JubiiRobot', 'The Jubii'));
        $this->register(new Spider('NorthStar', 'The NorthStar Robot'));
        $this->register(new Spider('w3index', 'The NWI Robot'));
        $this->register(new Spider('Peregrinator-Mathematics', 'The Peregrinator'));
        $this->register(new Spider('Pixray-Seeker', 'Pixray', 'http://www.pixray.com/pixraybot/'));
        $this->register(
            new Spider('TelegramBot (like TwitterBot)', 'TelegramBot (like TwitterBot)', 'https://telegram.org/')
        );
        $this->register(new Spider('Testomatobot', 'TestomatoBot', 'https://www.testomato.com/bot'));
        $this->register(new Spider('thumbshots-de-Bot', 'thumbshots-de-Bot'));
        $this->register(new Spider('TITAN', 'TITAN'));
        $this->register(new Spider('TitIn', 'TitIn'));
        $this->register(new Spider('TLSpider', 'TLSpider'));
        $this->register(new Spider('TMCrawler', 'TMCrawler'));
        $this->register(new Spider('trendictionbot', 'Trendiction-Bot', 'http://www.trendiction.com/bot'));
        $this->register(
            new Spider(
                'slysearch',
                'Turnitin.com',
                'http://www.turnitin.com/static/products_services/search_engines.html'
            )
        );
        $this->register(new Spider('TurnitinBot', 'TurnitinBot', 'http://www.turnitin.com/robot/crawlerinfo.html'));
        $this->register(new Spider('TurtleScanner', 'Turtle', 'http://www.turtle.ru/'));
        $this->register(new Spider('TwengaBot', 'Twenga', 'http://www.twenga.com/bot.html'));
        $this->register(new Spider('Twiceler', 'Twiceler', 'http://www.cuill.com/twiceler/robot.html'));
        $this->register(new Spider('Twitterbot', 'Twitterbot', 'https://twitter.com/'));
        $this->register(new Spider('UCSD-Crawler', 'UCSD Crawl'));
        $this->register(new Spider('UMBC-memeta-Bot', 'UMBC'));
        $this->register(new Spider('unisterbot', 'Unister'));
        $this->register(new Spider('Unpartisan', 'Unpartisan', 'http://www.unpartisan.com'));
        $this->register(new Spider('Uptime-Kuma', 'Uptime-Kuma', 'https://uptime.kuma.pet/'));
        $this->register(new Spider('UptimeRobot/2.0', 'Uptime Robot', 'http://uptimerobot.com/'));
        $this->register(new Spider('urlck', 'URL Check'));
        $this->register(new Spider('URL Spider Pro', 'URL Spider Pro'));
        $this->register(new Spider('Valkyrie', 'Valkyrie'));
        $this->register(new Spider('VelenPublicWebCrawler', 'Velen Crawler', 'https://velen.io'));
        $this->register(new Spider('Verticrawl', 'Verticrawl'));
        $this->register(new Spider('Victoria', 'Victoria'));
        $this->register(new Spider('vision-search', 'vision-search'));
        $this->register(new Spider('VoilaBot', 'VoilaBot', 'http://www.voila.com/'));
        $this->register(new Spider('VisBot', 'VisBot', 'http://www.visvo.com/webmasters.html'));
        $this->register(new Spider('Voyager', 'Voyager'));
        $this->register(new Spider('VWbot_K', 'VWbot'));
        $this->register(new Spider('W3M2', 'W3M2'));
        $this->register(new Spider('w3mir', 'w3mir'));
        $this->register(new Spider('w@pSpider', 'w@pSpider'));
        $this->register(new Spider('appie', 'Walhello appie', 'http://www.robotstxt.org/db/appie.html'));
        $this->register(new Spider('CrawlPaper', 'WallPaper'));
        $this->register(new Spider('root', 'Web Core / Roots'));
        $this->register(new Spider('WBSearchBot', 'Ware Bay', 'http://www.warebay.com/bot.html'));
        $this->register(new Spider('WebMoose', 'Web Moose'));
        $this->register(new Spider('WebBandit', 'WebBandit'));
        $this->register(new Spider('WebCatcher', 'WebCatcher'));
        $this->register(new Spider('Webclipping', 'Webclipping'));
        $this->register(new Spider('WebCopy', 'WebCopy'));
        $this->register(new Spider('WebFetcher', 'webfetcher'));
        $this->register(new Spider('weblayers', 'weblayers'));
        $this->register(new Spider('WebLinker', 'WebLinker'));
        $this->register(new Spider('wlm', 'Weblog Monitor'));
        $this->register(new Spider('WebQuest', 'WebQuest'));
        $this->register(new Spider('WebReaper', 'WebReaper'));
        $this->register(new Spider('webs@recruit.co.jp', 'webs'));
        $this->register(new Spider('websearchbench', 'WebSearchBench', 'http://websearchbench.cs.uni-dortmund.de/'));
        $this->register(new Spider('WOLP', 'WebStolperer'));
        $this->register(new Spider('webvac', 'WebVac'));
        $this->register(new Spider('webwalk', 'webwalk'));
        $this->register(new Spider('WebWalker', 'WebWalker'));
        $this->register(new Spider('WebWatch', 'WebWatch'));
        $this->register(new Spider('WebZinger', 'WebZinger'));
        $this->register(new Spider('whatUseek_winona', 'whatUseek Winona'));
        $this->register(new Spider('WhoWhere Robot', 'WebWatch', 'http://www.whowhere.com'));
        $this->register(new Spider('SurveyBot', 'Whois Source', 'http://www.whois.sc/info/webmasters/surveybot.html'));
        $this->register(new Spider('Hazel\'s Ferret Web hopper', 'Wild Ferret Web Hopper'));
        $this->register(new Spider('HRCrawler', 'HRCrawler'));
        $this->register(new Spider('WinHTTP', 'WinHTTP'));
        $this->register(new Spider('wired-digital-newsbot', 'Wired Digital'));
        $this->register(new Spider('zyborg', 'WiseNut'));
        $this->register(new Spider('WoltLabSuite', 'WoltLab Suite'));
        $this->register(new Spider('OmniExplorer_Bot', 'WorldIndexer', 'http://www.omni-explorer.com'));
        $this->register(new Spider('WWWC', 'WWWC'));
        $this->register(new Spider('WWWeasel Robot', 'WWWeasel Robot'));
        $this->register(new Spider('wwwster', 'wwwster'));
        $this->register(new Spider('WWWWanderer', 'WWWWanderer'));
        $this->register(new Spider('TECOMAC-Crawler', 'X-Crawler'));
        $this->register(new Spider('XGET', 'XGET'));
        $this->register(new Spider('cosmos', 'XYLEME Robot'));
        $this->register(new Spider('yacybot', 'YaCy-Bot', 'https://yacy.net/bot.html'));
        $this->register(new Spider('YahooYSMcm', 'Yahoo Publisher Network', 'http://publisher.yahoo.com/'));
        $this->register(
            new Spider('Yahoo-Blogs', 'Yahoo-Blogs', 'http://help.yahoo.com/help/us/ysearch/crawling/crawling-02.html')
        );
        $this->register(new Spider('Yahoo Pipes', 'Yahoo Pipes'));
        $this->register(new Spider('Yahoo! Slurp', 'Yahoo! Slurp', 'http://help.yahoo.com/help/us/ysearch/slurp'));
        $this->register(new Spider('Yahoo-VerticalCrawler', 'Yahoo-VerticalCrawler'));
        $this->register(new Spider('YahooFeedSeeker', 'YahooFeedSeeker', 'http://my.yahoo.com/s/publishers.html'));
        $this->register(new Spider('Yandex', 'Yandex', 'https://yandex.com/bots'));
        $this->register(new Spider('zeus', 'Zeus Internet Marketing', 'http://www.cyber-robotics.com/'));
        $this->register(new Spider('http://www.zorkk.com', 'Zork', 'http://www.zorkk.com'));
        $this->register(new Spider('Zookabot', 'Zookabotk', 'http://zookabot.com/'));
        $this->register(
            new Spider('ZoominfoBot', 'ZoominfoBot', 'https://www.zoominfo.com/about-zoominfo/zoominfobot')
        );
        $this->register(new Spider('360Spider', '360Spider'));
        $this->register(new Spider('GPTBot', 'GPTBot', 'https://openai.com/gptbot'));
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
