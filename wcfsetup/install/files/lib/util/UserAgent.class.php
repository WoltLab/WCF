<?php

namespace wcf\util;

/**
 * Represents the user agent.
 *
 * @author  Tim Duesterhus, Joshua Ruesweg
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Util
 * @since       5.4
 */
final class UserAgent
{
    private readonly string $userAgent;

    private readonly ?string $browser;

    private readonly ?string $browserVersion;

    private readonly ?string $os;

    public function __construct(string $userAgent)
    {
        $this->userAgent = $userAgent;

        $this->os = $this->detectOs($this->userAgent);
        [$this->browser, $this->browserVersion] = $this->detectBrowser($this->userAgent);
    }

    /**
     * Detects the browser on the basis of the user agent.
     */
    private function detectBrowser(string $userAgent): array
    {
        // lunascape
        if (\preg_match('~lunascape[ /]([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Lunascape',
                $match[1],
            ];
        }

        // sleipnir
        if (\preg_match('~sleipnir/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Sleipnir',
                $match[1],
            ];
        }

        // uc browser
        if (\preg_match('~(?:ucbrowser|uc browser|ucweb)[ /]?([\d\.]+)~i', $userAgent, $match)) {
            return [
                'UC Browser',
                $match[1],
            ];
        }

        // baidu browser
        if (\preg_match('~(?:baidubrowser|flyflow)[ /]?([\d\.x]+)~i', $userAgent, $match)) {
            return [
                'Baidubrowser',
                $match[1],
            ];
        }

        // blackberry
        if (\preg_match('~blackberry.*version/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Blackberry',
                $match[1],
            ];
        }

        // opera mobile
        if (\preg_match('~opera/([\d\.]+).*(mobi|mini)~i', $userAgent, $match)) {
            return [
                'Opera Mobile',
                $match[1],
            ];
        }

        // opera
        if (\preg_match('~opera.*version/([\d\.]+)|opr/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Opera',
                ($match[2] ?? $match[1]),
            ];
        }

        // thunderbird
        if (\preg_match('~thunderbird/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Thunderbird',
                $match[1],
            ];
        }

        // icedragon
        if (\preg_match('~icedragon/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'IceDragon',
                $match[1],
            ];
        }

        // palemoon
        if (\preg_match('~palemoon/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'PaleMoon',
                $match[1],
            ];
        }

        // flock
        if (\preg_match('~flock/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Flock',
                $match[1],
            ];
        }

        // iceweasel
        if (\preg_match('~iceweasel/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Iceweasel',
                $match[1],
            ];
        }

        // firefox mobile
        if (\preg_match('~(?:mobile.*firefox|fxios)/([\d\.]+)|fennec/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Firefox Mobile',
                ($match[2] ?? $match[1]),
            ];
        }

        // tapatalk 4
        if (\preg_match('~tapatalk/([\d\.]+)?~i', $userAgent, $match)) {
            return [
                'Tapatalk',
                ($match[1] ?? 4),
            ];
        }

        // firefox
        if (\preg_match('~firefox/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Firefox',
                $match[1],
            ];
        }

        // maxthon
        if (\preg_match('~maxthon[ /]([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Maxthon',
                $match[1],
            ];
        }

        // iemobile
        if (\preg_match('~iemobile[ /]([\d\.]+)|MSIE ([\d\.]+).*XBLWP7~i', $userAgent, $match)) {
            return [
                'Internet Explorer Mobile',
                ($match[2] ?? $match[1]),
            ];
        }

        // ie
        if (\preg_match('~msie ([\d\.]+)|Trident\/\d{1,2}.\d{1,2}; .*rv:([0-9]*)~i', $userAgent, $match)) {
            return [
                'Internet Explorer',
                ($match[2] ?? $match[1]),
            ];
        }

        // edge
        if (\preg_match('~edge?/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Microsoft Edge',
                $match[1],
            ];
        }

        // edge mobile
        if (\preg_match('~edga/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Microsoft Edge Mobile',
                $match[1],
            ];
        }

        // vivaldi
        if (\preg_match('~vivaldi/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Vivaldi',
                $match[1],
            ];
        }

        // iron
        if (\preg_match('~iron/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Iron',
                $match[1],
            ];
        }

        // coowon
        if (\preg_match('~coowon/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Coowon',
                $match[1],
            ];
        }

        // coolnovo
        if (\preg_match('~(?:coolnovo|chromeplus)/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'CoolNovo',
                $match[1],
            ];
        }

        // yandex
        if (\preg_match('~yabrowser/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Yandex',
                $match[1],
            ];
        }

        // midori
        if (\preg_match('~midori/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Midori',
                $match[1],
            ];
        }

        // chrome mobile
        if (\preg_match('~(?:crios|crmo)/([\d\.]+)|chrome/([\d\.]+).*mobile~i', $userAgent, $match)) {
            return [
                'Chrome Mobile',
                ($match[2] ?? $match[1]),
            ];
        }

        // kindle
        if (\preg_match('~kindle/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Kindle',
                $match[1],
            ];
        }

        // silk
        if (\preg_match('~silk/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Silk',
                $match[1],
            ];
        }

        // android browser
        if (\preg_match('~Android ([\d\.]+).*AppleWebKit~i', $userAgent, $match)) {
            return [
                'Android Browser',
                $match[1],
            ];
        }

        // safari mobile
        if (\preg_match('~([\d\.]+) Mobile/\w+ safari~i', $userAgent, $match)) {
            return [
                'Safari Mobile',
                $match[1],
            ];
        }

        // chrome
        if (\preg_match('~(?:chromium|chrome)/([\d\.]+)~i', $userAgent, $match)) {
            return [
                'Chrome',
                $match[1],
            ];
        }

        // safari
        if (\preg_match('~([\d\.]+) safari~i', $userAgent, $match)) {
            return [
                'Safari',
                $match[1],
            ];
        }

        return [
            null,
            null,
        ];
    }

    /**
     * Detects the OS on the basis of the user agent.
     */
    private function detectOs(string $userAgent): ?string
    {
        // iOS
        if (\preg_match('/iphone/i', $userAgent)) {
            return "iOS";
        }

        // iOS
        if (\preg_match('/cfnetwork/i', $userAgent)) {
            return "iOS";
        }

        // Windows
        if (\preg_match('/windows/i', $userAgent)) {
            return "Windows";
        }

        // FreeBSD
        if (\preg_match('/freebsd/i', $userAgent)) {
            return "FreeBSD";
        }

        // netBSD
        if (\preg_match('/netbsd/i', $userAgent)) {
            return "NetBSD";
        }

        // openBSD
        if (\preg_match('/openbsd/i', $userAgent)) {
            return "OpenBSD";
        }

        // Android
        if (\preg_match('/android/i', $userAgent)) {
            return "Android";
        }

        // Linux
        if (\preg_match('/linux/i', $userAgent)) {
            return "Linux";
        }

        // iPad
        if (\preg_match('/ipad/i', $userAgent)) {
            return "iPad";
        }

        // webOS
        if (\preg_match('/web[0o]s/i', $userAgent)) {
            return "webOS";
        }

        // CrOS
        if (\preg_match('/cros/i', $userAgent)) {
            return "Chrome OS";
        }

        // macOS
        if (\preg_match('/mac/i', $userAgent)) {
            return "macOS";
        }

        return null;
    }

    /**
     * Returns the raw user agent string.
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @see UserAgent::getUserAgent()
     */
    public function __toString(): string
    {
        return $this->getUserAgent();
    }

    /**
     * Returns the browser based on the user agent or null, if no browser can be determined.
     */
    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    /**
     * Returns the browser version based on the user agent or null, if no browser version can be determined.
     */
    public function getBrowserVersion(): ?string
    {
        return $this->browserVersion;
    }

    /**
     * Returns the OS based on the user agent or null, if no OS can be determined.
     */
    public function getOs(): ?string
    {
        return $this->os;
    }

    /**
     * Checks if the User Agent gives an indicator about a tablet device.
     * <strong>Attention</strong>: This is only a basic test and can easily be falsified by the user.
     */
    public function isTablet(): bool
    {
        if (\preg_match('/tab(let)|ipad/i', $this->userAgent)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the User Agent gives an indicator about a mobile device.
     * <strong>Attention</strong>: This is only a basic test and can easily be falsified by the user.
     */
    public function isMobileBrowser(): bool
    {
        if (!$this->userAgent) {
            return false;
        }

        if (
            \preg_match(
                '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',
                $this->userAgent
            )
        ) {
            return true;
        }

        if (
            \preg_match(
                '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',
                \substr($this->userAgent, 0, 4)
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns a font awesome device icon.
     */
    public function getDeviceIcon(): string
    {
        if ($this->isTablet()) {
            return 'tablet-screen-button';
        }

        if ($this->isMobileBrowser()) {
            return 'mobile-screen-button';
        }

        return 'computer';
    }
}
