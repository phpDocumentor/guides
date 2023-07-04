<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Doctrine\Common\Lexer\AbstractLexer;
use ReflectionClass;

use function array_column;
use function array_flip;
use function parse_url;
use function preg_match;

use const PHP_URL_SCHEME;

/** @extends AbstractLexer<int, string> */
final class InlineLexer extends AbstractLexer
{
    public const WORD = 1;
    public const UNDERSCORE = 2;
    public const ANONYMOUS_END = 3;
    public const PHRASE_ANONYMOUS_END = 4;
    public const LITERAL = 5;
    public const BACKTICK = 6;
    public const NAMED_REFERENCE_END = 7;
    public const INTERNAL_REFERENCE_START = 8;
    public const EMBEDED_URL_START = 9;
    public const EMBEDED_URL_END = 10;
    public const NAMED_REFERENCE = 11;
    public const ANONYMOUSE_REFERENCE = 12;
    public const COLON = 13;
    public const OCTOTHORPE = 14;
    public const WHITESPACE = 15;
    public const ANNOTATION_START = 16;
    public const ANNOTATION_END = 17;
    public const DOUBLE_BACKTICK = 18;
    public const HYPERLINK = 19;
    public const EMAIL = 20;
    public const EMPHASIS_DELIMITER = 21;
    public const STRONG_DELIMITER = 22;
    public const NBSP = 23;
    public const VARIABLE_DELIMITER = 24;
    public const ESCAPED_SIGN = 25;

    public const SUPPORTED_TLDS = '(?:aaa|aaas|about|acap|acct|acd|acr|adiumxtra|adt|afp|afs|aim|amss|android|appdata|apt|ar|ark|at|attachment|aw|barion|bb|beshare|bitcoin|bitcoincash|blob|bolo|browserext|cabal|calculator|callto|cap|cast|casts|chrome|chrome-extension|cid|coap|coap+tcp|coap+ws|coaps|coaps+tcp|coaps+ws|com-eventbrite-attendee|content|content-type|crid|cstr|cvs|dab|dat|data|dav|dhttp|diaspora|dict|did|dis|dlna-playcontainer|dlna-playsingle|dns|dntp|doi|dpp|drm|drop|dtmi|dtn|dvb|dvx|dweb|ed2k|eid|elsi|embedded|ens|ethereum|example|facetime|fax|feed|feedready|fido|file|filesystem|finger|first-run-pen-experience|fish|fm|ftp|fuchsia-pkg|geo|gg|git|gitoid|gizmoproject|go|gopher|graph|grd|gtalk|h323|ham|hcap|hcp|http|https|hxxp|hxxps|hydrazone|hyper|iax|icap|icon|im|imap|info|iotdisco|ipfs|ipn|ipns|ipp|ipps|irc|irc6|ircs|iris|iris\.beep|iris\.lwz|iris\.xpc|iris\.xpcs|isostore|itms|jabber|jar|jms|keyparc|lastfm|lbry|ldap|ldaps|leaptofrogans|lorawan|lpa|lvlt|magnet|mailserver|mailto|maps|market|matrix|message|microsoft\.windows\.camera|microsoft\.windows\.camera\.multipicker|microsoft\.windows\.camera\.picker|mid|mms|modem|mongodb|moz|ms-access|ms-appinstaller|ms-browser-extension|ms-calculator|ms-drive-to|ms-enrollment|ms-excel|ms-eyecontrolspeech|ms-gamebarservices|ms-gamingoverlay|ms-getoffice|ms-help|ms-infopath|ms-inputapp|ms-launchremotedesktop|ms-lockscreencomponent-config|ms-media-stream-id|ms-meetnow|ms-mixedrealitycapture|ms-mobileplans|ms-newsandinterests|ms-officeapp|ms-people|ms-project|ms-powerpoint|ms-publisher|ms-remotedesktop|ms-remotedesktop-launch|ms-restoretabcompanion|ms-screenclip|ms-screensketch|ms-search|ms-search-repair|ms-secondary-screen-controller|ms-secondary-screen-setup|ms-settings|ms-settings-airplanemode|ms-settings-bluetooth|ms-settings-camera|ms-settings-cellular|ms-settings-cloudstorage|ms-settings-connectabledevices|ms-settings-displays-topology|ms-settings-emailandaccounts|ms-settings-language|ms-settings-location|ms-settings-lock|ms-settings-nfctransactions|ms-settings-notifications|ms-settings-power|ms-settings-privacy|ms-settings-proximity|ms-settings-screenrotation|ms-settings-wifi|ms-settings-workplace|ms-spd|ms-stickers|ms-sttoverlay|ms-transit-to|ms-useractivityset|ms-virtualtouchpad|ms-visio|ms-walk-to|ms-whiteboard|ms-whiteboard-cmd|ms-word|msnim|msrp|msrps|mss|mt|mtqp|mumble|mupdate|mvn|news|nfs|ni|nih|nntp|notes|num|ocf|oid|onenote|onenote-cmd|opaquelocktoken|openpgp4fpr|otpauth|p1|pack|palm|paparazzi|payment|payto|pkcs11|platform|pop|pres|prospero|proxy|pwid|psyc|pttp|qb|query|quic-transport|redis|rediss|reload|res|resource|rmi|rsync|rtmfp|rtmp|rtsp|rtsps|rtspu|sarif|secondlife|secret-token|service|session|sftp|sgn|shc|shttp (OBSOLETE)|sieve|simpleledger|simplex|sip|sips|skype|smb|smp|sms|smtp|snews|snmp|soap\.beep|soap\.beeps|soldat|spiffe|spotify|ssb|ssh|starknet|steam|stun|stuns|submit|svn|swh|swid|swidpath|tag|taler|teamspeak|tel|teliaeid|telnet|tftp|things|thismessage|tip|tn3270|tool|turn|turns|tv|udp|unreal|upt|urn|ut2004|uuid-in-package|v-event|vemmi|ventrilo|ves|videotex|vnc|view-source|vscode|vscode-insiders|vsls|w3|wais|web3|wcr|webcal|web+ap|wifi|wpid|ws|wss|wtai|wyciwyg|xcon|xcon-userid|xfire|xmlrpc\.beep|xmlrpc\.beeps|xmpp|xri|ymsgr|z39\.50|z39\.50r|z39\.50s)';

    /**
     * Map between string position and position in token list.
     *
     * @link https://github.com/doctrine/lexer/issues/53
     *
     * @var array<int, int>
     */
    private array $tokenPositions = [];

    /** @return string[] */
    protected function getCatchablePatterns(): array
    {
        return [
            '\\\\``', // must be a separate case, as the next pattern would split in "\`" + "`", causing it to become a intepreted text
            '\\\\[\s\S]', // Escaping hell... needs escaped slash in regex, but also in php.
            self::SUPPORTED_TLDS . ':[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*[-a-zA-Z0-9()@%_\\+~#&\\/=]', // standalone hyperlinks
            '\\S+@\\S+\\.\\S+',
            '[a-z0-9-]+_{2}', //Inline href.
            '[a-z0-9-]+_{1}(?=[\s\.+]|$)', //Inline href.
            '``.+?``(?!`)',
            '`__',
            '`_',
            '`~',
            '<',
            '>',
            '_`',
            '`',
            '_{2}',
            ':',
            '|',
            '\\*\\*',
            '\\*',
        ];
    }

    /** @param int $position */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function resetPosition($position = 0): void
    {
        parent::resetPosition($this->tokenPositions[$position]);
    }

    /** @param string $input */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    protected function scan($input): void
    {
        parent::scan($input);

        $class = new ReflectionClass(AbstractLexer::class);
        $property = $class->getProperty('tokens');
        $property->setAccessible(true);
        /** @var array<int, string> $tokens */
        $tokens = $property->getValue($this);

        $this->tokenPositions = array_flip(array_column($tokens, 'position'));
    }

    /** @return string[] */
    protected function getNonCatchablePatterns(): array
    {
        return [];
    }

    /** @inheritDoc */
    protected function getType(string &$value)
    {
        if (preg_match('/^\\\\[\s\S]/i', $value)) {
            return self::ESCAPED_SIGN;
        }

        if (preg_match('/``.+``(?!`)/i', $value)) {
            return self::LITERAL;
        }

        if (preg_match('/' . self::SUPPORTED_TLDS . ':[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*[-a-zA-Z0-9()@%_\\+~#&\\/=]/', $value) && parse_url($value, PHP_URL_SCHEME) !== null) {
            return self::HYPERLINK;
        }

        if (preg_match('/\\S+@\\S+\\.\\S+/i', $value)) {
            return self::EMAIL;
        }

        if (preg_match('/[a-z0-9-]+_{2}/i', $value)) {
            return self::ANONYMOUSE_REFERENCE;
        }

        if (preg_match('/[a-z0-9-]+_{1}/i', $value)) {
            return self::NAMED_REFERENCE;
        }

        if (preg_match('/\s/i', $value)) {
            return self::WHITESPACE;
        }

        switch ($value) {
            case '`':
                return self::BACKTICK;

            case '**':
                return self::STRONG_DELIMITER;

            case '*':
                return self::EMPHASIS_DELIMITER;

            case '|':
                return self::VARIABLE_DELIMITER;

            case '<':
                return self::EMBEDED_URL_START;

            case '>':
                return self::EMBEDED_URL_END;

            case '_':
                return self::UNDERSCORE;

            case '`_':
                return self::NAMED_REFERENCE_END;

            case '_`':
                return self::INTERNAL_REFERENCE_START;

            case '__':
                return self::ANONYMOUS_END;

            case '`__':
                return self::PHRASE_ANONYMOUS_END;

            case ':':
                return self::COLON;

            case '#':
                return self::OCTOTHORPE;

            case '[':
                return self::ANNOTATION_START;

            case ']':
                return self::ANNOTATION_END;

            case '~':
                return self::NBSP;

            default:
                return self::WORD;
        }
    }
}
