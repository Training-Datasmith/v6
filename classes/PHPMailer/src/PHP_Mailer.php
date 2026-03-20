<?php

declare (strict_types=1);
/**
 * PHPMailer - PHP email creation and transport class.
 * PHP Version 5.5.
 *
 * @see https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 *
 * @author    Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author    Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author    Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author    Brent R. Matzelle (original founder)
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note      This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */
namespace Php_Mailer\Php_Mailer;

/**
 * PHPMailer - PHP email creation and transport class.
 *
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 */
class Php_Mailer
{
    public const CHARSET_ASCII = 'us-ascii';
    public const CHARSET_ISO88591 = 'iso-8859-1';
    public const CHARSET_UTF8 = 'utf-8';
    public const CONTENT_TYPE_PLAINTEXT = 'text/plain';
    public const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
    public const CONTENT_TYPE_TEXT_HTML = 'text/html';
    public const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
    public const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
    public const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';
    public const ENCODING_7BIT = '7bit';
    public const ENCODING_8BIT = '8bit';
    public const ENCODING_BASE64 = 'base64';
    public const ENCODING_BINARY = 'binary';
    public const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';
    public const ENCRYPTION_STARTTLS = 'tls';
    public const ENCRYPTION_SMTPS = 'ssl';
    public const ICAL_METHOD_REQUEST = 'REQUEST';
    public const ICAL_METHOD_PUBLISH = 'PUBLISH';
    public const ICAL_METHOD_REPLY = 'REPLY';
    public const ICAL_METHOD_ADD = 'ADD';
    public const ICAL_METHOD_CANCEL = 'CANCEL';
    public const ICAL_METHOD_REFRESH = 'REFRESH';
    public const ICAL_METHOD_COUNTER = 'COUNTER';
    public const ICAL_METHOD_DECLINECOUNTER = 'DECLINECOUNTER';
    /**
     * Email priority.
     * Options: null (default), 1 = High, 3 = Normal, 5 = low.
     * When null, the header is not set at all.
     *
     * @var int|null
     */
    public $Priority;
    /**
     * The character set of the message.
     *
     * @var string
     */
    public $char_set = self::CHARSET_ISO88591;
    /**
     * The MIME Content-type of the message.
     *
     * @var string
     */
    public $content_type = self::CONTENT_TYPE_PLAINTEXT;
    /**
     * The message encoding.
     * Options: "8bit", "7bit", "binary", "base64", and "quoted-printable".
     *
     * @var string
     */
    public $Encoding = self::ENCODING_8BIT;
    /**
     * Holds the most recent mailer error message.
     *
     * @var string
     */
    public $error_info = '';
    /**
     * The From email address for the message.
     *
     * @var string
     */
    public $From = 'root@localhost';
    /**
     * The From name of the message.
     *
     * @var string
     */
    public $from_name = 'Root User';
    /**
     * The envelope sender of the message.
     * This will usually be turned into a Return-Path header by the receiver,
     * and is the address that bounces will be sent to.
     * If not empty, will be passed via `-f` to sendmail or as the 'MAIL FROM' value over SMTP.
     *
     * @var string
     */
    public $Sender = '';
    /**
     * The Subject of the message.
     *
     * @var string
     */
    public $Subject = '';
    /**
     * An HTML or plain text message body.
     * If HTML then call isHTML(true).
     *
     * @var string
     */
    public $Body = '';
    /**
     * The plain-text message body.
     * This body can be read by mail clients that do not have HTML email
     * capability such as mutt & Eudora.
     * Clients that can read HTML will view the normal Body.
     *
     * @var string
     */
    public $alt_body = '';
    /**
     * An iCal message part body.
     * Only supported in simple alt or alt_inline message types
     * To generate iCal event structures, use classes like EasyPeasyICS or iCalcreator.
     *
     * @see http://sprain.ch/blog/downloads/php-class-easypeasyics-create-ical-files-with-php/
     * @see http://kigkonsult.se/iCalcreator/
     *
     * @var string
     */
    public $Ical = '';
    /**
     * Value-array of "method" in Contenttype header "text/calendar"
     *
     * @var string[]
     */
    protected static $ical_methods = [self::ICAL_METHOD_REQUEST, self::ICAL_METHOD_PUBLISH, self::ICAL_METHOD_REPLY, self::ICAL_METHOD_ADD, self::ICAL_METHOD_CANCEL, self::ICAL_METHOD_REFRESH, self::ICAL_METHOD_COUNTER, self::ICAL_METHOD_DECLINECOUNTER];
    /**
     * The complete compiled MIME message body.
     *
     * @var string
     */
    protected $mime_body = '';
    /**
     * The complete compiled MIME message headers.
     *
     * @var string
     */
    protected $mime_header = '';
    /**
     * Extra headers that createHeader() doesn't fold in.
     *
     * @var string
     */
    protected $mail_header = '';
    /**
     * Word-wrap the message body to this number of chars.
     * Set to 0 to not wrap. A useful value here is 78, for RFC2822 section 2.1.1 compliance.
     *
     * @see static::STD_LINE_LENGTH
     *
     * @var int
     */
    public $word_wrap = 0;
    /**
     * Which method to use to send mail.
     * Options: "mail", "sendmail", or "smtp".
     *
     * @var string
     */
    public $Mailer = 'mail';
    /**
     * The path to the sendmail program.
     *
     * @var string
     */
    public $Sendmail = '/usr/sbin/sendmail';
    /**
     * Whether mail() uses a fully sendmail-compatible MTA.
     * One which supports sendmail's "-oi -f" options.
     *
     * @var bool
     */
    public $use_sendmail_options = true;
    /**
     * The email address that a reading confirmation should be sent to, also known as read receipt.
     *
     * @var string
     */
    public $confirm_reading_to = '';
    /**
     * The hostname to use in the Message-ID header and as default HELO string.
     * If empty, PHPMailer attempts to find one with, in order,
     * $_SERVER['SERVER_NAME'], gethostname(), php_uname('n'), or the value
     * 'localhost.localdomain'.
     *
     * @see PHPMailer::$Helo
     *
     * @var string
     */
    public $Hostname = '';
    /**
     * An ID to be used in the Message-ID header.
     * If empty, a unique id will be generated.
     * You can set your own, but it must be in the format "<id@domain>",
     * as defined in RFC5322 section 3.6.4 or it will be ignored.
     *
     * @see https://tools.ietf.org/html/rfc5322#section-3.6.4
     *
     * @var string
     */
    public $message_id = '';
    /**
     * The message Date to be used in the Date header.
     * If empty, the current date will be added.
     *
     * @var string
     */
    public $message_date = '';
    /**
     * SMTP hosts.
     * Either a single hostname or multiple semicolon-delimited hostnames.
     * You can also specify a different port
     * for each host by using this format: [hostname:port]
     * (e.g. "smtp1.example.com:25;smtp2.example.com").
     * You can also specify encryption type, for example:
     * (e.g. "tls://smtp1.example.com:587;ssl://smtp2.example.com:465").
     * Hosts will be tried in order.
     *
     * @var string
     */
    public $Host = 'localhost';
    /**
     * The default SMTP server port.
     *
     * @var int
     */
    public $Port = 25;
    /**
     * The SMTP HELO/EHLO name used for the SMTP connection.
     * Default is $Hostname. If $Hostname is empty, PHPMailer attempts to find
     * one with the same method described above for $Hostname.
     *
     * @see PHPMailer::$Hostname
     *
     * @var string
     */
    public $Helo = '';
    /**
     * What kind of encryption to use on the SMTP connection.
     * Options: '', static::ENCRYPTION_STARTTLS, or static::ENCRYPTION_SMTPS.
     *
     * @var string
     */
    public $smtp_secure = '';
    /**
     * Whether to enable TLS encryption automatically if a server supports it,
     * even if `SMTPSecure` is not set to 'tls'.
     * Be aware that in PHP >= 5.6 this requires that the server's certificates are valid.
     *
     * @var bool
     */
    public $smtp_auto_tls = true;
    /**
     * Whether to use SMTP authentication.
     * Uses the Username and Password properties.
     *
     * @see PHPMailer::$Username
     * @see PHPMailer::$Password
     *
     * @var bool
     */
    public $smtp_auth = false;
    /**
     * Options array passed to stream_context_create when connecting via SMTP.
     *
     * @var array
     */
    public $smtp_options = [];
    /**
     * SMTP username.
     *
     * @var string
     */
    public $Username = '';
    /**
     * SMTP password.
     *
     * @var string
     */
    public $Password = '';
    /**
     * SMTP auth type.
     * Options are CRAM-MD5, LOGIN, PLAIN, XOAUTH2, attempted in that order if not specified.
     *
     * @var string
     */
    public $auth_type = '';
    /**
     * An instance of the PHPMailer OAuth class.
     *
     * @var OAuth
     */
    protected $oauth;
    /**
     * The SMTP server timeout in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
     *
     * @var int
     */
    public $Timeout = 300;
    /**
     * Comma separated list of DSN notifications
     * 'NEVER' under no circumstances a DSN must be returned to the sender.
     *         If you use NEVER all other notifications will be ignored.
     * 'SUCCESS' will notify you when your mail has arrived at its destination.
     * 'FAILURE' will arrive if an error occurred during delivery.
     * 'DELAY'   will notify you if there is an unusual delay in delivery, but the actual
     *           delivery's outcome (success or failure) is not yet decided.
     *
     * @see https://tools.ietf.org/html/rfc3461 See section 4.1 for more information about NOTIFY
     */
    public $dsn = '';
    /**
     * SMTP class debug output mode.
     * Debug output level.
     * Options:
     * @see SMTP::DEBUG_OFF: No output
     * @see SMTP::DEBUG_CLIENT: Client messages
     * @see SMTP::DEBUG_SERVER: Client and server messages
     * @see SMTP::DEBUG_CONNECTION: As SERVER plus connection status
     * @see SMTP::DEBUG_LOWLEVEL: Noisy, low-level data output, rarely needed
     *
     * @see SMTP::$do_debug
     *
     * @var int
     */
    public $smtp_debug = 0;
    /**
     * How to handle debug output.
     * Options:
     * * `echo` Output plain-text as-is, appropriate for CLI
     * * `html` Output escaped, line breaks converted to `<br>`, appropriate for browser output
     * * `error_log` Output to error log as configured in php.ini
     * By default PHPMailer will use `echo` if run from a `cli` or `cli-server` SAPI, `html` otherwise.
     * Alternatively, you can provide a callable expecting two params: a message string and the debug level:
     *
     * ```php
     * $mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str";};
     * ```
     *
     * Alternatively, you can pass in an instance of a PSR-3 compatible logger, though only `debug`
     * level output is used:
     *
     * ```php
     * $mail->Debugoutput = new myPsr3Logger;
     * ```
     *
     * @see SMTP::$Debugoutput
     *
     * @var string|callable|\Psr\Log\LoggerInterface
     */
    public $Debugoutput = 'echo';
    /**
     * Whether to keep SMTP connection open after each message.
     * If this is set to true then to close the connection
     * requires an explicit call to smtpClose().
     *
     * @var bool
     */
    public $smtp_keep_alive = false;
    /**
     * Whether to split multiple to addresses into multiple messages
     * or send them all in one message.
     * Only supported in `mail` and `sendmail` transports, not in SMTP.
     *
     * @var bool
     *
     * @deprecated 6.0.0 PHPMailer isn't a mailing list manager!
     */
    public $single_to = false;
    /**
     * Storage for addresses when SingleTo is enabled.
     *
     * @var array
     */
    protected $single_to_array = [];
    /**
     * Whether to generate VERP addresses on send.
     * Only applicable when sending via SMTP.
     *
     * @see https://en.wikipedia.org/wiki/Variable_envelope_return_path
     * @see http://www.postfix.org/VERP_README.html Postfix VERP info
     *
     * @var bool
     */
    public $do_verp = false;
    /**
     * Whether to allow sending messages with an empty body.
     *
     * @var bool
     */
    public $allow_empty = false;
    /**
     * DKIM selector.
     *
     * @var string
     */
    public $DKIM_selector = '';
    /**
     * DKIM Identity.
     * Usually the email address used as the source of the email.
     *
     * @var string
     */
    public $DKIM_identity = '';
    /**
     * DKIM passphrase.
     * Used if your key is encrypted.
     *
     * @var string
     */
    public $DKIM_passphrase = '';
    /**
     * DKIM signing domain name.
     *
     * @example 'example.com'
     *
     * @var string
     */
    public $DKIM_domain = '';
    /**
     * DKIM Copy header field values for diagnostic use.
     *
     * @var bool
     */
    public $dkim_copy_header_fields = true;
    /**
     * DKIM Extra signing headers.
     *
     * @example ['List-Unsubscribe', 'List-Help']
     *
     * @var array
     */
    public $dkim_extra_headers = [];
    /**
     * DKIM private key file path.
     *
     * @var string
     */
    public $DKIM_private = '';
    /**
     * DKIM private key string.
     *
     * If set, takes precedence over `$DKIM_private`.
     *
     * @var string
     */
    public $DKIM_private_string = '';
    /**
     * Callback Action function name.
     *
     * The function that handles the result of the send email action.
     * It is called out by send() for each email sent.
     *
     * Value can be any php callable: http://www.php.net/is_callable
     *
     * Parameters:
     *   bool $result        result of the send action
     *   array   $to            email addresses of the recipients
     *   array   $cc            cc email addresses
     *   array   $bcc           bcc email addresses
     *   string  $subject       the subject
     *   string  $body          the email body
     *   string  $from          email address of sender
     *   string  $extra         extra information of possible use
     *                          "smtp_transaction_id' => last smtp transaction id
     *
     * @var string
     */
    public $action_function = '';
    /**
     * What to put in the X-Mailer header.
     * Options: An empty string for PHPMailer default, whitespace/null for none, or a string to use.
     *
     * @var string|null
     */
    public $x_mailer = '';
    /**
     * Which validator to use by default when validating email addresses.
     * May be a callable to inject your own validator, but there are several built-in validators.
     * The default validator uses PHP's FILTER_VALIDATE_EMAIL filter_var option.
     *
     * @see PHPMailer::validateAddress()
     *
     * @var string|callable
     */
    public static $validator = 'php';
    /**
     * An instance of the SMTP sender class.
     *
     * @var SMTP
     */
    protected $smtp;
    /**
     * The array of 'to' names and addresses.
     *
     * @var array
     */
    protected $to = [];
    /**
     * The array of 'cc' names and addresses.
     *
     * @var array
     */
    protected $cc = [];
    /**
     * The array of 'bcc' names and addresses.
     *
     * @var array
     */
    protected $bcc = [];
    /**
     * The array of reply-to names and addresses.
     *
     * @var array
     */
    protected $reply_to = [];
    /**
     * An array of all kinds of addresses.
     * Includes all of $to, $cc, $bcc.
     *
     * @see PHPMailer::$to
     * @see PHPMailer::$cc
     * @see PHPMailer::$bcc
     *
     * @var array
     */
    protected $all_recipients = [];
    /**
     * An array of names and addresses queued for validation.
     * In send(), valid and non duplicate entries are moved to $all_recipients
     * and one of $to, $cc, or $bcc.
     * This array is used only for addresses with IDN.
     *
     * @see PHPMailer::$to
     * @see PHPMailer::$cc
     * @see PHPMailer::$bcc
     * @see PHPMailer::$all_recipients
     *
     * @var array
     */
    protected $recipients_queue = [];
    /**
     * An array of reply-to names and addresses queued for validation.
     * In send(), valid and non duplicate entries are moved to $ReplyTo.
     * This array is used only for addresses with IDN.
     *
     * @see PHPMailer::$ReplyTo
     *
     * @var array
     */
    protected $reply_to_queue = [];
    /**
     * The array of attachments.
     *
     * @var array
     */
    protected $attachment = [];
    /**
     * The array of custom headers.
     *
     * @var array
     */
    protected $custom_header = [];
    /**
     * The most recent Message-ID (including angular brackets).
     *
     * @var string
     */
    protected $last_message_id = '';
    /**
     * The message's MIME type.
     *
     * @var string
     */
    protected $message_type = '';
    /**
     * The array of MIME boundary strings.
     *
     * @var array
     */
    protected $boundary = [];
    /**
     * The array of available languages.
     *
     * @var array
     */
    protected $language = [];
    /**
     * The number of errors encountered.
     *
     * @var int
     */
    protected $error_count = 0;
    /**
     * The S/MIME certificate file path.
     *
     * @var string
     */
    protected $sign_cert_file = '';
    /**
     * The S/MIME key file path.
     *
     * @var string
     */
    protected $sign_key_file = '';
    /**
     * The optional S/MIME extra certificates ("CA Chain") file path.
     *
     * @var string
     */
    protected $sign_extracerts_file = '';
    /**
     * The S/MIME password for the key.
     * Used only if the key is encrypted.
     *
     * @var string
     */
    protected $sign_key_pass = '';
    /**
     * Whether to throw exceptions for errors.
     *
     * @var bool
     */
    protected $exceptions = false;
    /**
     * Unique ID used for message ID and boundaries.
     *
     * @var string
     */
    protected $uniqueid = '';
    /**
     * The PHPMailer Version number.
     *
     * @var string
     */
    public const VERSION = '6.4.1';
    /**
     * Error severity: message only, continue processing.
     *
     * @var int
     */
    public const STOP_MESSAGE = 0;
    /**
     * Error severity: message, likely ok to continue processing.
     *
     * @var int
     */
    public const STOP_CONTINUE = 1;
    /**
     * Error severity: message, plus full stop, critical error reached.
     *
     * @var int
     */
    public const STOP_CRITICAL = 2;
    /**
     * The SMTP standard CRLF line break.
     * If you want to change line break format, change static::$LE, not this.
     */
    public const CRLF = "\r\n";
    /**
     * "Folding White Space" a white space string used for line folding.
     */
    public const FWS = ' ';
    /**
     * SMTP RFC standard line ending; Carriage Return, Line Feed.
     *
     * @var string
     */
    protected static $LE = self::CRLF;
    /**
     * The maximum line length supported by mail().
     *
     * Background: mail() will sometimes corrupt messages
     * with headers headers longer than 65 chars, see #818.
     *
     * @var int
     */
    public const MAIL_MAX_LINE_LENGTH = 63;
    /**
     * The maximum line length allowed by RFC 2822 section 2.1.1.
     *
     * @var int
     */
    public const MAX_LINE_LENGTH = 998;
    /**
     * The lower maximum line length allowed by RFC 2822 section 2.1.1.
     * This length does NOT include the line break
     * 76 means that lines will be 77 or 78 chars depending on whether
     * the line break format is LF or CRLF; both are valid.
     *
     * @var int
     */
    public const STD_LINE_LENGTH = 76;
    /**
     * Constructor.
     *
     * @param bool $exceptions Should we throw external exceptions?
     */
    public function __construct($exceptions = null)
    {
        if (null !== $exceptions) {
            $this->exceptions = (bool) $exceptions;
        }
        //Pick an appropriate debug output format automatically
        $this->Debugoutput = str_contains(PHP_SAPI, 'cli') ? 'echo' : 'html';
    }
    /**
     * Destructor.
     */
    public function __destruct()
    {
        //Close any open SMTP connection nicely
        $this->smtp_close();
    }
    /**
     * Call mail() in a safe_mode-aware fashion.
     * Also, unless sendmail_path points to sendmail (or something that
     * claims to be sendmail), don't pass params (not a perfect fix,
     * but it will do).
     *
     * @param string      $to      To
     * @param string      $subject Subject
     * @param string      $body    Message Body
     * @param string      $header  Additional Header(s)
     * @param string|null $params  Params
     *
     * @return bool
     */
    private function mail_passthru(string $to, $subject, $body, string $header, ?string $params)
    {
        //Check overloading of mail function to avoid double-encoding
        if (ini_get('mbstring.func_overload') & 1) {
            $subject = $this->secure_header($subject);
        } else {
            $subject = $this->encode_header($this->secure_header($subject));
        }
        //Calling mail() with null params breaks
        $this->edebug('Sending with mail()');
        $this->edebug('Sendmail path: ' . ini_get('sendmail_path'));
        $this->edebug("Envelope sender: {$this->Sender}");
        $this->edebug("To: {$to}");
        $this->edebug("Subject: {$subject}");
        $this->edebug("Headers: {$header}");
        if (!$this->use_sendmail_options || null === $params) {
            $result = @mail($to, $subject, $body, $header);
        } else {
            $this->edebug("Additional params: {$params}");
            $result = @mail($to, $subject, $body, $header, $params);
        }
        $this->edebug('Result: ' . ($result ? 'true' : 'false'));
        return $result;
    }
    /**
     * Output debugging info via a user-defined method.
     * Only generates output if debug output is enabled.
     *
     * @see PHPMailer::$Debugoutput
     * @see PHPMailer::$SMTPDebug
     *
     * @param string $str
     */
    protected function edebug($str)
    {
        if ($this->smtp_debug <= 0) {
            return;
        }
        //Is this a PSR-3 logger?
        if ($this->Debugoutput instanceof \Psr\Log\Logger_Interface) {
            $this->Debugoutput->debug($str);
            return;
        }
        //Avoid clash with built-in function names
        if (is_callable($this->Debugoutput) && !in_array($this->Debugoutput, ['error_log', 'html', 'echo'])) {
            call_user_func($this->Debugoutput, $str, $this->smtp_debug);
            return;
        }
        switch ($this->Debugoutput) {
            case 'error_log':
                //Don't output, just log
                /** @noinspection ForgottenDebugOutputInspection */
                error_log($str);
                break;
            case 'html':
                //Cleans up output a bit for a better looking, HTML-safe output
                echo htmlentities((string) preg_replace('/[\r\n]+/', '', $str), ENT_QUOTES, 'UTF-8'), "<br>\n";
                break;
            case 'echo':
            default:
                //Normalize line breaks
                $str = preg_replace('/\r\n|\r/m', "\n", $str);
                echo gmdate('Y-m-d H:i:s'), "\t", trim(
                    //Indent for readability, except for trailing break
                    str_replace("\n", "\n                   \t                  ", trim((string) $str))
                ), "\n";
        }
    }
    /**
     * Sets message type to HTML or plain.
     *
     * @param bool $isHtml True for HTML mode
     */
    public function is_html($is_html = true): void
    {
        if ($is_html) {
            $this->content_type = static::CONTENT_TYPE_TEXT_HTML;
        } else {
            $this->content_type = static::CONTENT_TYPE_PLAINTEXT;
        }
    }
    /**
     * Send messages using SMTP.
     */
    public function is_smtp(): void
    {
        $this->Mailer = 'smtp';
    }
    /**
     * Send messages using PHP's mail() function.
     */
    public function is_mail(): void
    {
        $this->Mailer = 'mail';
    }
    /**
     * Send messages using $Sendmail.
     */
    public function is_sendmail(): void
    {
        $ini_sendmail_path = ini_get('sendmail_path');
        if (false === stripos($ini_sendmail_path, 'sendmail')) {
            $this->Sendmail = '/usr/sbin/sendmail';
        } else {
            $this->Sendmail = $ini_sendmail_path;
        }
        $this->Mailer = 'sendmail';
    }
    /**
     * Send messages using qmail.
     */
    public function is_qmail(): void
    {
        $ini_sendmail_path = ini_get('sendmail_path');
        if (false === stripos($ini_sendmail_path, 'qmail')) {
            $this->Sendmail = '/var/qmail/bin/qmail-inject';
        } else {
            $this->Sendmail = $ini_sendmail_path;
        }
        $this->Mailer = 'qmail';
    }
    /**
     * Add a "To" address.
     *
     * @param string $address The email address to send to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    public function add_address($address, $name = '')
    {
        return $this->add_or_enqueue_an_address('to', $address, $name);
    }
    /**
     * Add a "CC" address.
     *
     * @param string $address The email address to send to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    public function add_cc($address, $name = '')
    {
        return $this->add_or_enqueue_an_address('cc', $address, $name);
    }
    /**
     * Add a "BCC" address.
     *
     * @param string $address The email address to send to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    public function add_bcc($address, $name = '')
    {
        return $this->add_or_enqueue_an_address('bcc', $address, $name);
    }
    /**
     * Add a "Reply-To" address.
     *
     * @param string $address The email address to reply to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    public function add_reply_to($address, $name = '')
    {
        return $this->add_or_enqueue_an_address('Reply-To', $address, $name);
    }
    /**
     * Add an address to one of the recipient arrays or to the ReplyTo array. Because PHPMailer
     * can't validate addresses with an IDN without knowing the PHPMailer::$CharSet (that can still
     * be modified after calling this function), addition of such addresses is delayed until send().
     * Addresses that have been added already return false, but do not throw exceptions.
     *
     * @param string $kind    One of 'to', 'cc', 'bcc', or 'ReplyTo'
     * @param string $address The email address to send, resp. to reply to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    protected function add_or_enqueue_an_address($kind, $address, $name)
    {
        $address = trim($address);
        $name = trim((string) preg_replace('/[\r\n]+/', '', $name));
        //Strip breaks and trim
        $pos = strrpos($address, '@');
        if (false === $pos) {
            //At-sign is missing.
            $error_message = sprintf('%s (%s): %s', $this->lang('invalid_address'), $kind, $address);
            $this->set_error($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        $params = [$kind, $address, $name];
        //Enqueue addresses with IDN until we know the PHPMailer::$CharSet.
        if (static::idn_supported() && $this->has8bit_chars(substr($address, ++$pos))) {
            if ('Reply-To' !== $kind) {
                if (!array_key_exists($address, $this->recipients_queue)) {
                    $this->recipients_queue[$address] = $params;
                    return true;
                }
            } elseif (!array_key_exists($address, $this->reply_to_queue)) {
                $this->reply_to_queue[$address] = $params;
                return true;
            }
            return false;
        }
        //Immediately add standard addresses without IDN.
        return call_user_func_array($this->add_an_address(...), $params);
    }
    /**
     * Add an address to one of the recipient arrays or to the ReplyTo array.
     * Addresses that have been added already return false, but do not throw exceptions.
     *
     * @param string $kind    One of 'to', 'cc', 'bcc', or 'ReplyTo'
     * @param string $address The email address to send, resp. to reply to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    protected function add_an_address($kind, $address, $name = ''): bool
    {
        if (!in_array($kind, ['to', 'cc', 'bcc', 'Reply-To'])) {
            $error_message = sprintf('%s: %s', $this->lang('Invalid recipient kind'), $kind);
            $this->set_error($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        if (!static::validate_address($address)) {
            $error_message = sprintf('%s (%s): %s', $this->lang('invalid_address'), $kind, $address);
            $this->set_error($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        if ('Reply-To' !== $kind) {
            if (!array_key_exists(strtolower($address), $this->all_recipients)) {
                $this->{$kind}[] = [$address, $name];
                $this->all_recipients[strtolower($address)] = true;
                return true;
            }
        } elseif (!array_key_exists(strtolower($address), $this->reply_to)) {
            $this->reply_to[strtolower($address)] = [$address, $name];
            return true;
        }
        return false;
    }
    /**
     * Parse and validate a string containing one or more RFC822-style comma-separated email addresses
     * of the form "display name <address>" into an array of name/address pairs.
     * Uses the imap_rfc822_parse_adrlist function if the IMAP extension is available.
     * Note that quotes in the name part are removed.
     *
     * @see http://www.andrew.cmu.edu/user/agreen1/testing/mrbs/web/Mail/RFC822.php A more careful implementation
     *
     * @param string $addrstr The address list string
     * @param bool   $useimap Whether to use the IMAP extension to parse the list
     */
    public static function parse_addresses($addrstr, $useimap = true): array
    {
        $addresses = [];
        if ($useimap && function_exists('imap_rfc822_parse_adrlist')) {
            //Use this built-in parser if it's available
            $list = imap_rfc822_parse_adrlist($addrstr, '');
            foreach ($list as $address) {
                if ('.SYNTAX-ERROR.' !== $address->host && static::validate_address($address->mailbox . '@' . $address->host)) {
                    //Decode the name part if it's present and encoded
                    if (property_exists($address, 'personal') && extension_loaded('mbstring') && preg_match('/^=\?.*\?=$/', (string) $address->personal)) {
                        $address->personal = mb_decode_mimeheader((string) $address->personal);
                    }
                    $addresses[] = ['name' => property_exists($address, 'personal') ? $address->personal : '', 'address' => $address->mailbox . '@' . $address->host];
                }
            }
        } else {
            //Use this simpler parser
            $list = explode(',', $addrstr);
            foreach ($list as $address) {
                $address = trim($address);
                //Is there a separate name part?
                if (!str_contains($address, '<')) {
                    //No separate name, just use the whole thing
                    if (static::validate_address($address)) {
                        $addresses[] = ['name' => '', 'address' => $address];
                    }
                } else {
                    [$name, $email] = explode('<', $address);
                    $email = trim(str_replace('>', '', $email));
                    $name = trim($name);
                    if (static::validate_address($email)) {
                        //If this name is encoded, decode it
                        if (preg_match('/^=\?.*\?=$/', $name)) {
                            $name = mb_decode_mimeheader($name);
                        }
                        $addresses[] = [
                            //Remove any surrounding quotes and spaces from the name
                            'name' => trim($name, '\'" '),
                            'address' => $email,
                        ];
                    }
                }
            }
        }
        return $addresses;
    }
    /**
     * Set the From and FromName properties.
     *
     * @param string $address
     * @param string $name
     * @param bool   $auto    Whether to also set the Sender address, defaults to true
     *
     * @throws Exception
     */
    public function set_from($address, $name = '', $auto = true): bool
    {
        $address = trim($address);
        $name = trim((string) preg_replace('/[\r\n]+/', '', $name));
        //Strip breaks and trim
        //Don't validate now addresses with IDN. Will be done in send().
        $pos = strrpos($address, '@');
        if (false === $pos || (!$this->has8bit_chars(substr($address, ++$pos)) || !static::idn_supported()) && !static::validate_address($address)) {
            $error_message = sprintf('%s (From): %s', $this->lang('invalid_address'), $address);
            $this->set_error($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        $this->From = $address;
        $this->from_name = $name;
        if ($auto && empty($this->Sender)) {
            $this->Sender = $address;
        }
        return true;
    }
    /**
     * Return the Message-ID header of the last email.
     * Technically this is the value from the last time the headers were created,
     * but it's also the message ID of the last sent message except in
     * pathological cases.
     *
     * @return string
     */
    public function get_last_message_id()
    {
        return $this->last_message_id;
    }
    /**
     * Check that a string looks like an email address.
     * Validation patterns supported:
     * * `auto` Pick best pattern automatically;
     * * `pcre8` Use the squiloople.com pattern, requires PCRE > 8.0;
     * * `pcre` Use old PCRE implementation;
     * * `php` Use PHP built-in FILTER_VALIDATE_EMAIL;
     * * `html5` Use the pattern given by the HTML5 spec for 'email' type form input elements.
     * * `noregex` Don't use a regex: super fast, really dumb.
     * Alternatively you may pass in a callable to inject your own validator, for example:
     *
     * ```php
     * PHPMailer::validateAddress('user@example.com', function($address) {
     *     return (strpos($address, '@') !== false);
     * });
     * ```
     *
     * You can also set the PHPMailer::$validator static to a callable, allowing built-in methods to use your validator.
     *
     * @param string          $address       The email address to check
     * @param string|callable $patternselect Which pattern to use
     *
     * @return bool
     */
    public static function validate_address($address, $patternselect = null)
    {
        if (null === $patternselect) {
            $patternselect = static::$validator;
        }
        if (is_callable($patternselect)) {
            return call_user_func($patternselect, $address);
        }
        //Reject line breaks in addresses; it's valid RFC5322, but not RFC5321
        if (str_contains($address, "\n") || str_contains($address, "\r")) {
            return false;
        }
        return match ($patternselect) {
            /*
             * A more complex and more permissive version of the RFC5322 regex on which FILTER_VALIDATE_EMAIL
             * is based.
             * In addition to the addresses allowed by filter_var, also permits:
             *  * dotless domains: `a@b`
             *  * comments: `1234 @ local(blah) .machine .example`
             *  * quoted elements: `'"test blah"@example.org'`
             *  * numeric TLDs: `a@b.123`
             *  * unbracketed IPv4 literals: `a@192.168.0.1`
             *  * IPv6 literals: 'first.last@[IPv6:a1::]'
             * Not all of these will necessarily work for sending!
             *
             * @see       http://squiloople.com/2009/12/20/email-address-validation/
             * @copyright 2009-2010 Michael Rushton
             * Feel free to use and redistribute this code. But please keep this copyright notice.
             */
            'pcre', 'pcre8' => (bool) preg_match('/^(?!(?>(?1)"?(?>\\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\\[ -~]|[^"])"?(?1)){65,}@)' . '((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)?[\t ]+)?)(\((?>(?2)' . '(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)' . '([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\\[\x00-\x7F]))*' . '(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' . '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' . '|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:' . '|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}' . '|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD', $address),
            /*
             * This is the pattern used in the HTML5 spec for validation of 'email' type form input elements.
             *
             * @see https://html.spec.whatwg.org/#e-mail-state-(type=email)
             */
            'html5' => (bool) preg_match('/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' . '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD', $address),
            default => filter_var($address, FILTER_VALIDATE_EMAIL) !== false,
        };
    }
    /**
     * Tells whether IDNs (Internationalized Domain Names) are supported or not. This requires the
     * `intl` and `mbstring` PHP extensions.
     *
     * @return bool `true` if required functions for IDN support are present
     */
    public static function idn_supported(): bool
    {
        return function_exists('idn_to_ascii') && function_exists('mb_convert_encoding');
    }
    /**
     * Converts IDN in given email address to its ASCII form, also known as punycode, if possible.
     * Important: Address must be passed in same encoding as currently set in PHPMailer::$CharSet.
     * This function silently returns unmodified address if:
     * - No conversion is necessary (i.e. domain name is not an IDN, or is already in ASCII form)
     * - Conversion to punycode is impossible (e.g. required PHP functions are not available)
     *   or fails for any reason (e.g. domain contains characters not allowed in an IDN).
     *
     * @see PHPMailer::$CharSet
     *
     * @param string $address The email address to convert
     *
     * @return string The encoded address in ASCII form
     */
    public function punyencode_address($address)
    {
        //Verify we have required functions, CharSet, and at-sign.
        $pos = strrpos($address, '@');
        if (!empty($this->char_set) && false !== $pos && static::idn_supported()) {
            $domain = substr($address, ++$pos);
            //Verify CharSet string is a valid one, and domain properly encoded in this CharSet.
            if ($this->has8bit_chars($domain) && @mb_check_encoding($domain, $this->char_set)) {
                //Convert the domain from whatever charset it's in to UTF-8
                $domain = mb_convert_encoding($domain, self::CHARSET_UTF8, $this->char_set);
                //Ignore IDE complaints about this line - method signature changed in PHP 5.4
                $errorcode = 0;
                if (defined('INTL_IDNA_VARIANT_UTS46')) {
                    //Use the current punycode standard (appeared in PHP 7.2)
                    $punycode = idn_to_ascii($domain, $errorcode, \INTL_IDNA_VARIANT_UTS46);
                } elseif (defined('INTL_IDNA_VARIANT_2003')) {
                    //Fall back to this old, deprecated/removed encoding
                    $punycode = idn_to_ascii($domain, $errorcode, \INTL_IDNA_VARIANT_2003);
                } else {
                    //Fall back to a default we don't know about
                    $punycode = idn_to_ascii($domain, $errorcode);
                }
                if (false !== $punycode) {
                    return substr($address, 0, $pos) . $punycode;
                }
            }
        }
        return $address;
    }
    /**
     * Create a message and send it.
     * Uses the sending method specified by $Mailer.
     *
     * @throws Exception
     *
     * @return bool false on error - See the ErrorInfo property for details of the error
     */
    public function send()
    {
        try {
            if (!$this->pre_send()) {
                return false;
            }
            return $this->post_send();
        } catch (Exception $exc) {
            $this->mail_header = '';
            $this->set_error($exc->get_message());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
    }
    /**
     * Prepare a message for sending.
     *
     * @throws Exception
     */
    public function pre_send(): bool
    {
        if ('smtp' === $this->Mailer || 'mail' === $this->Mailer && (\PHP_VERSION_ID >= 80000 || stripos(PHP_OS, 'WIN') === 0)) {
            //SMTP mandates RFC-compliant line endings
            //and it's also used with mail() on Windows
            static::set_le(self::CRLF);
        } else {
            //Maintain backward compatibility with legacy Linux command line mailers
            static::set_le(PHP_EOL);
        }
        //Check for buggy PHP versions that add a header with an incorrect line break
        if ('mail' === $this->Mailer && (\PHP_VERSION_ID >= 70000 && \PHP_VERSION_ID < 70017 || \PHP_VERSION_ID >= 70100 && \PHP_VERSION_ID < 70103) && ini_get('mail.add_x_header') === '1' && stripos(PHP_OS, 'WIN') === 0) {
            trigger_error('Your version of PHP is affected by a bug that may result in corrupted messages.' . ' To fix it, switch to sending using SMTP, disable the mail.add_x_header option in' . ' your php.ini, switch to MacOS or Linux, or upgrade your PHP to version 7.0.17+ or 7.1.3+.', E_USER_WARNING);
        }
        try {
            $this->error_count = 0;
            //Reset errors
            $this->mail_header = '';
            //Dequeue recipient and Reply-To addresses with IDN
            foreach (array_merge($this->recipients_queue, $this->reply_to_queue) as $params) {
                $params[1] = $this->punyencode_address($params[1]);
                call_user_func_array($this->add_an_address(...), $params);
            }
            if (count($this->to) + count($this->cc) + count($this->bcc) < 1) {
                throw new Exception($this->lang('provide_address'), self::STOP_CRITICAL);
            }
            //Validate From, Sender, and ConfirmReadingTo addresses
            foreach (['From', 'Sender', 'ConfirmReadingTo'] as $address_kind) {
                $this->{$address_kind} = trim($this->{$address_kind});
                if (empty($this->{$address_kind})) {
                    continue;
                }
                $this->{$address_kind} = $this->punyencode_address($this->{$address_kind});
                if (!static::validate_address($this->{$address_kind})) {
                    $error_message = sprintf('%s (%s): %s', $this->lang('invalid_address'), $address_kind, $this->{$address_kind});
                    $this->set_error($error_message);
                    $this->edebug($error_message);
                    if ($this->exceptions) {
                        throw new Exception($error_message);
                    }
                    return false;
                }
            }
            //Set whether the message is multipart/alternative
            if ($this->alternative_exists()) {
                $this->content_type = static::CONTENT_TYPE_MULTIPART_ALTERNATIVE;
            }
            $this->set_message_type();
            //Refuse to send an empty message unless we are specifically allowing it
            if (!$this->allow_empty && empty($this->Body)) {
                throw new Exception($this->lang('empty_message'), self::STOP_CRITICAL);
            }
            //Trim subject consistently
            $this->Subject = trim($this->Subject);
            //Create body before headers in case body makes changes to headers (e.g. altering transfer encoding)
            $this->mime_header = '';
            $this->mime_body = $this->create_body();
            //createBody may have added some headers, so retain them
            $tempheaders = $this->mime_header;
            $this->mime_header = $this->create_header();
            $this->mime_header .= $tempheaders;
            //To capture the complete message when using mail(), create
            //an extra header list which createHeader() doesn't fold in
            if ('mail' === $this->Mailer) {
                if (count($this->to) > 0) {
                    $this->mail_header .= $this->addr_append('To', $this->to);
                } else {
                    $this->mail_header .= $this->header_line('To', 'undisclosed-recipients:;');
                }
                $this->mail_header .= $this->header_line('Subject', $this->encode_header($this->secure_header($this->Subject)));
            }
            //Sign with DKIM if enabled
            if (!empty($this->DKIM_domain) && !empty($this->DKIM_selector) && (!empty($this->DKIM_private_string) || !empty($this->DKIM_private) && static::is_permitted_path($this->DKIM_private) && file_exists($this->DKIM_private))) {
                $header_dkim = $this->DKIM_Add($this->mime_header . $this->mail_header, $this->encode_header($this->secure_header($this->Subject)), $this->mime_body);
                $this->mime_header = static::strip_trailing_wsp($this->mime_header) . static::$LE . static::normalize_breaks($header_dkim) . static::$LE;
            }
            return true;
        } catch (Exception $exc) {
            $this->set_error($exc->get_message());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
    }
    /**
     * Actually send a message via the selected mechanism.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function post_send()
    {
        try {
            //Choose the mailer and send through it
            switch ($this->Mailer) {
                case 'sendmail':
                case 'qmail':
                    return $this->sendmail_send($this->mime_header, $this->mime_body);
                case 'smtp':
                    return $this->smtp_send($this->mime_header, $this->mime_body);
                case 'mail':
                    return $this->mail_send($this->mime_header, $this->mime_body);
                default:
                    $send_method = $this->Mailer . 'Send';
                    if (method_exists($this, $send_method)) {
                        return $this->{$send_method}($this->mime_header, $this->mime_body);
                    }
                    return $this->mail_send($this->mime_header, $this->mime_body);
            }
        } catch (Exception $exc) {
            if ($this->Mailer === 'smtp' && $this->smtp_keep_alive == true) {
                $this->smtp->reset();
            }
            $this->set_error($exc->get_message());
            $this->edebug($exc->get_message());
            if ($this->exceptions) {
                throw $exc;
            }
        }
        return false;
    }
    /**
     * Send mail using the $Sendmail program.
     *
     * @see PHPMailer::$Sendmail
     *
     * @param string $header The message headers
     * @param string $body   The message body
     *
     * @throws Exception
     */
    protected function sendmail_send($header, $body): bool
    {
        if ($this->Mailer === 'qmail') {
            $this->edebug('Sending with qmail');
        } else {
            $this->edebug('Sending with sendmail');
        }
        $header = static::strip_trailing_wsp($header) . static::$LE . static::$LE;
        //This sets the SMTP envelope sender which gets turned into a return-path header by the receiver
        //A space after `-f` is optional, but there is a long history of its presence
        //causing problems, so we don't use one
        //Exim docs: http://www.exim.org/exim-html-current/doc/html/spec_html/ch-the_exim_command_line.html
        //Sendmail docs: http://www.sendmail.org/~ca/email/man/sendmail.html
        //Qmail docs: http://www.qmail.org/man/man8/qmail-inject.html
        //Example problem: https://www.drupal.org/node/1057954
        if (empty($this->Sender) && !empty(ini_get('sendmail_from'))) {
            //PHP config has a sender address we can use
            $this->Sender = ini_get('sendmail_from');
        }
        //CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
        if (!empty($this->Sender) && static::validate_address($this->Sender) && self::is_shell_safe($this->Sender)) {
            if ($this->Mailer === 'qmail') {
                $sendmail_fmt = '%s -f%s';
            } else {
                $sendmail_fmt = '%s -oi -f%s -t';
            }
        } else {
            //allow sendmail to choose a default envelope sender. It may
            //seem preferable to force it to use the From header as with
            //SMTP, but that introduces new problems (see
            //<https://github.com/PHPMailer/PHPMailer/issues/2298>), and
            //it has historically worked this way.
            $sendmail_fmt = '%s -oi -t';
        }
        $sendmail = sprintf($sendmail_fmt, escapeshellcmd($this->Sendmail), $this->Sender);
        $this->edebug('Sendmail path: ' . $this->Sendmail);
        $this->edebug('Sendmail command: ' . $sendmail);
        $this->edebug('Envelope sender: ' . $this->Sender);
        $this->edebug("Headers: {$header}");
        if ($this->single_to) {
            foreach ($this->single_to_array as $to_addr) {
                $mail = @popen($sendmail, 'w');
                if (!$mail) {
                    throw new Exception($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
                }
                $this->edebug("To: {$to_addr}");
                fwrite($mail, 'To: ' . $to_addr . "\n");
                fwrite($mail, $header);
                fwrite($mail, $body);
                $result = pclose($mail);
                $addrinfo = static::parse_addresses($to_addr);
                $this->do_callback($result === 0, [[$addrinfo['address'], $addrinfo['name']]], $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
                $this->edebug('Result: ' . ($result === 0 ? 'true' : 'false'));
                if (0 !== $result) {
                    throw new Exception($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
                }
            }
        } else {
            $mail = @popen($sendmail, 'w');
            if (!$mail) {
                throw new Exception($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
            }
            fwrite($mail, $header);
            fwrite($mail, $body);
            $result = pclose($mail);
            $this->do_callback($result === 0, $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
            $this->edebug('Result: ' . ($result === 0 ? 'true' : 'false'));
            if (0 !== $result) {
                throw new Exception($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
            }
        }
        return true;
    }
    /**
     * Fix CVE-2016-10033 and CVE-2016-10045 by disallowing potentially unsafe shell characters.
     * Note that escapeshellarg and escapeshellcmd are inadequate for our purposes, especially on Windows.
     *
     * @see https://github.com/PHPMailer/PHPMailer/issues/924 CVE-2016-10045 bug report
     *
     * @param string $string The string to be validated
     */
    protected static function is_shell_safe($string): bool
    {
        //Future-proof
        if (escapeshellcmd($string) !== $string || !in_array(escapeshellarg($string), ["'{$string}'", "\"{$string}\""])) {
            return false;
        }
        $length = strlen($string);
        for ($i = 0; $i < $length; ++$i) {
            $c = $string[$i];
            //All other characters have a special meaning in at least one common shell, including = and +.
            //Full stop (.) has a special meaning in cmd.exe, but its impact should be negligible here.
            //Note that this does permit non-Latin alphanumeric characters based on the current locale.
            if (!ctype_alnum($c) && !str_contains('@_-.', $c)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Check whether a file path is of a permitted type.
     * Used to reject URLs and phar files from functions that access local file paths,
     * such as addAttachment.
     *
     * @param string $path A relative or absolute path to a file
     */
    protected static function is_permitted_path($path): bool
    {
        //Matches scheme definition from https://tools.ietf.org/html/rfc3986#section-3.1
        return !preg_match('#^[a-z][a-z\d+.-]*://#i', $path);
    }
    /**
     * Check whether a file path is safe, accessible, and readable.
     *
     * @param string $path A relative or absolute path to a file
     *
     * @return bool
     */
    protected static function file_is_accessible($path)
    {
        if (!static::is_permitted_path($path)) {
            return false;
        }
        $readable = file_exists($path);
        //If not a UNC path (expected to start with \\), check read permission, see #2069
        if (!str_starts_with($path, '\\\\')) {
            return $readable && is_readable($path);
        }
        return $readable;
    }
    /**
     * Send mail using the PHP mail() function.
     *
     * @see http://www.php.net/manual/en/book.mail.php
     *
     * @param string $header The message headers
     * @param string $body   The message body
     *
     * @throws Exception
     */
    protected function mail_send($header, $body): bool
    {
        $header = static::strip_trailing_wsp($header) . static::$LE . static::$LE;
        $to_arr = [];
        foreach ($this->to as $toaddr) {
            $to_arr[] = $this->addr_format($toaddr);
        }
        $to = implode(', ', $to_arr);
        $params = null;
        //This sets the SMTP envelope sender which gets turned into a return-path header by the receiver
        //A space after `-f` is optional, but there is a long history of its presence
        //causing problems, so we don't use one
        //Exim docs: http://www.exim.org/exim-html-current/doc/html/spec_html/ch-the_exim_command_line.html
        //Sendmail docs: http://www.sendmail.org/~ca/email/man/sendmail.html
        //Qmail docs: http://www.qmail.org/man/man8/qmail-inject.html
        //Example problem: https://www.drupal.org/node/1057954
        //CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
        if (empty($this->Sender) && !empty(ini_get('sendmail_from'))) {
            //PHP config has a sender address we can use
            $this->Sender = ini_get('sendmail_from');
        }
        if (!empty($this->Sender) && static::validate_address($this->Sender)) {
            if (self::is_shell_safe($this->Sender)) {
                $params = sprintf('-f%s', $this->Sender);
            }
            $old_from = ini_get('sendmail_from');
            ini_set('sendmail_from', $this->Sender);
        }
        $result = false;
        if ($this->single_to && count($to_arr) > 1) {
            foreach ($to_arr as $to_addr) {
                $result = $this->mail_passthru($to_addr, $this->Subject, $body, $header, $params);
                $addrinfo = static::parse_addresses($to_addr);
                $this->do_callback($result, [[$addrinfo['address'], $addrinfo['name']]], $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
            }
        } else {
            $result = $this->mail_passthru($to, $this->Subject, $body, $header, $params);
            $this->do_callback($result, $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
        }
        if (isset($old_from)) {
            ini_set('sendmail_from', $old_from);
        }
        if (!$result) {
            throw new Exception($this->lang('instantiate'), self::STOP_CRITICAL);
        }
        return true;
    }
    /**
     * Get an instance to use for SMTP operations.
     * Override this function to load your own SMTP implementation,
     * or set one with setSMTPInstance.
     *
     * @return SMTP
     */
    public function get_smtp_instance()
    {
        if (!is_object($this->smtp)) {
            $this->smtp = new SMTP();
        }
        return $this->smtp;
    }
    /**
     * Provide an instance to use for SMTP operations.
     *
     * @return SMTP
     */
    public function set_smtp_instance(SMTP $smtp)
    {
        $this->smtp = $smtp;
        return $this->smtp;
    }
    /**
     * Send mail via SMTP.
     * Returns false if there is a bad MAIL FROM, RCPT, or DATA input.
     *
     * @see PHPMailer::setSMTPInstance() to use a different class.
     *
     * @uses \PHPMailer\PHPMailer\SMTP
     *
     * @param string $header The message headers
     * @param string $body   The message body
     *
     * @throws Exception
     */
    protected function smtp_send($header, string $body): bool
    {
        $header = static::strip_trailing_wsp($header) . static::$LE . static::$LE;
        $bad_rcpt = [];
        if (!$this->smtp_connect($this->smtp_options)) {
            throw new Exception($this->lang('smtp_connect_failed'), self::STOP_CRITICAL);
        }
        //Sender already validated in preSend()
        if ('' === $this->Sender) {
            $smtp_from = $this->From;
        } else {
            $smtp_from = $this->Sender;
        }
        if (!$this->smtp->mail($smtp_from)) {
            $this->set_error($this->lang('from_failed') . $smtp_from . ' : ' . implode(',', $this->smtp->get_error()));
            throw new Exception($this->error_info, self::STOP_CRITICAL);
        }
        $callbacks = [];
        //Attempt to send to all recipients
        foreach ([$this->to, $this->cc, $this->bcc] as $togroup) {
            foreach ($togroup as $to) {
                if (!$this->smtp->recipient($to[0], $this->dsn)) {
                    $error = $this->smtp->get_error();
                    $bad_rcpt[] = ['to' => $to[0], 'error' => $error['detail']];
                    $is_sent = false;
                } else {
                    $is_sent = true;
                }
                $callbacks[] = ['issent' => $is_sent, 'to' => $to[0], 'name' => $to[1]];
            }
        }
        //Only send the DATA command if we have viable recipients
        if (count($this->all_recipients) > count($bad_rcpt) && !$this->smtp->data($header . $body)) {
            throw new Exception($this->lang('data_not_accepted'), self::STOP_CRITICAL);
        }
        $smtp_transaction_id = $this->smtp->get_last_transaction_id();
        if ($this->smtp_keep_alive) {
            $this->smtp->reset();
        } else {
            $this->smtp->quit();
            $this->smtp->close();
        }
        foreach ($callbacks as $cb) {
            $this->do_callback($cb['issent'], [[$cb['to'], $cb['name']]], [], [], $this->Subject, $body, $this->From, ['smtp_transaction_id' => $smtp_transaction_id]);
        }
        //Create error message for any bad addresses
        if (count($bad_rcpt) > 0) {
            $errstr = '';
            foreach ($bad_rcpt as $bad) {
                $errstr .= $bad['to'] . ': ' . $bad['error'];
            }
            throw new Exception($this->lang('recipients_failed') . $errstr, self::STOP_CONTINUE);
        }
        return true;
    }
    /**
     * Initiate a connection to an SMTP server.
     * Returns false if the operation failed.
     *
     * @param array $options An array of options compatible with stream_context_create()
     *
     * @throws Exception
     *
     * @uses \PHPMailer\PHPMailer\SMTP
     */
    public function smtp_connect($options = null): bool
    {
        if (null === $this->smtp) {
            $this->smtp = $this->get_smtp_instance();
        }
        //If no options are provided, use whatever is set in the instance
        if (null === $options) {
            $options = $this->smtp_options;
        }
        //Already connected?
        if ($this->smtp->connected()) {
            return true;
        }
        $this->smtp->set_timeout($this->Timeout);
        $this->smtp->set_debug_level($this->smtp_debug);
        $this->smtp->set_debug_output($this->Debugoutput);
        $this->smtp->set_verp($this->do_verp);
        $hosts = explode(';', $this->Host);
        $lastexception = null;
        foreach ($hosts as $hostentry) {
            $hostinfo = [];
            if (!preg_match('/^(?:(ssl|tls):\/\/)?(.+?)(?::(\d+))?$/', trim($hostentry), $hostinfo)) {
                $this->edebug($this->lang('invalid_hostentry') . ' ' . trim($hostentry));
                //Not a valid host entry
                continue;
            }
            //$hostinfo[1]: optional ssl or tls prefix
            //$hostinfo[2]: the hostname
            //$hostinfo[3]: optional port number
            //The host string prefix can temporarily override the current setting for SMTPSecure
            //If it's not specified, the default value is used
            //Check the host name is a valid name or IP address before trying to use it
            if (!static::is_valid_host($hostinfo[2])) {
                $this->edebug($this->lang('invalid_host') . ' ' . $hostinfo[2]);
                continue;
            }
            $prefix = '';
            $secure = $this->smtp_secure;
            $tls = static::ENCRYPTION_STARTTLS === $this->smtp_secure;
            if ('ssl' === $hostinfo[1] || '' === $hostinfo[1] && static::ENCRYPTION_SMTPS === $this->smtp_secure) {
                $prefix = 'ssl://';
                $tls = false;
                //Can't have SSL and TLS at the same time
                $secure = static::ENCRYPTION_SMTPS;
            } elseif ('tls' === $hostinfo[1]) {
                $tls = true;
                //TLS doesn't use a prefix
                $secure = static::ENCRYPTION_STARTTLS;
            }
            //Do we need the OpenSSL extension?
            $sslext = defined('OPENSSL_ALGO_SHA256');
            if (static::ENCRYPTION_STARTTLS === $secure || static::ENCRYPTION_SMTPS === $secure) {
                //Check for an OpenSSL constant rather than using extension_loaded, which is sometimes disabled
                if (!$sslext) {
                    throw new Exception($this->lang('extension_missing') . 'openssl', self::STOP_CRITICAL);
                }
            }
            $host = $hostinfo[2];
            $port = $this->Port;
            if (array_key_exists(3, $hostinfo) && is_numeric($hostinfo[3]) && $hostinfo[3] > 0 && $hostinfo[3] < 65536) {
                $port = (int) $hostinfo[3];
            }
            if ($this->smtp->connect($prefix . $host, $port, $this->Timeout, $options)) {
                try {
                    if ($this->Helo) {
                        $hello = $this->Helo;
                    } else {
                        $hello = $this->server_hostname();
                    }
                    $this->smtp->hello($hello);
                    //Automatically enable TLS encryption if:
                    //* it's not disabled
                    //* we have openssl extension
                    //* we are not already using SSL
                    //* the server offers STARTTLS
                    if ($this->smtp_auto_tls && $sslext && 'ssl' !== $secure && $this->smtp->get_server_ext('STARTTLS')) {
                        $tls = true;
                    }
                    if ($tls) {
                        if (!$this->smtp->start_tls()) {
                            throw new Exception($this->lang('connect_host'));
                        }
                        //We must resend EHLO after TLS negotiation
                        $this->smtp->hello($hello);
                    }
                    if ($this->smtp_auth && !$this->smtp->authenticate($this->Username, $this->Password, $this->auth_type, $this->oauth)) {
                        throw new Exception($this->lang('authenticate'));
                    }
                    return true;
                } catch (Exception $exc) {
                    $lastexception = $exc;
                    $this->edebug($exc->get_message());
                    //We must have connected, but then failed TLS or Auth, so close connection nicely
                    $this->smtp->quit();
                }
            }
        }
        //If we get here, all connection attempts have failed, so close connection hard
        $this->smtp->close();
        //As we've caught all exceptions, just report whatever the last one was
        if ($this->exceptions && null !== $lastexception) {
            throw $lastexception;
        }
        return false;
    }
    /**
     * Close the active SMTP session if one exists.
     */
    public function smtp_close(): void
    {
        if (null !== $this->smtp && $this->smtp->connected()) {
            $this->smtp->quit();
            $this->smtp->close();
        }
    }
    /**
     * Set the language for error messages.
     * Returns false if it cannot load the language file.
     * The default language is English.
     *
     * @param string $langcode  ISO 639-1 2-character language code (e.g. French is "fr")
     * @param string $lang_path Path to the language file directory, with trailing separator (slash)
     */
    public function set_language($langcode = 'en', $lang_path = ''): bool
    {
        //Backwards compatibility for renamed language codes
        $renamed_langcodes = ['br' => 'pt_br', 'cz' => 'cs', 'dk' => 'da', 'no' => 'nb', 'se' => 'sv', 'rs' => 'sr', 'tg' => 'tl', 'am' => 'hy'];
        if (array_key_exists($langcode, $renamed_langcodes)) {
            $langcode = $renamed_langcodes[$langcode];
        }
        //Define full set of translatable strings in English
        $PHPMAILER_LANG = ['authenticate' => 'SMTP Error: Could not authenticate.', 'connect_host' => 'SMTP Error: Could not connect to SMTP host.', 'data_not_accepted' => 'SMTP Error: data not accepted.', 'empty_message' => 'Message body empty', 'encoding' => 'Unknown encoding: ', 'execute' => 'Could not execute: ', 'file_access' => 'Could not access file: ', 'file_open' => 'File Error: Could not open file: ', 'from_failed' => 'The following From address failed: ', 'instantiate' => 'Could not instantiate mail function.', 'invalid_address' => 'Invalid address: ', 'invalid_hostentry' => 'Invalid hostentry: ', 'invalid_host' => 'Invalid host: ', 'mailer_not_supported' => ' mailer is not supported.', 'provide_address' => 'You must provide at least one recipient email address.', 'recipients_failed' => 'SMTP Error: The following recipients failed: ', 'signing' => 'Signing Error: ', 'smtp_connect_failed' => 'SMTP connect() failed.', 'smtp_error' => 'SMTP server error: ', 'variable_set' => 'Cannot set or reset variable: ', 'extension_missing' => 'Extension missing: '];
        if (empty($lang_path)) {
            //Calculate an absolute path so it can work if CWD is not here
            $lang_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
        }
        //Validate $langcode
        if (!preg_match('/^[a-z]{2}(?:_[a-zA-Z]{2})?$/', $langcode)) {
            $langcode = 'en';
        }
        $foundlang = true;
        $lang_file = $lang_path . 'phpmailer.lang-' . $langcode . '.php';
        //There is no English translation file
        if ('en' !== $langcode) {
            //Make sure language file path is readable
            if (!static::file_is_accessible($lang_file)) {
                $foundlang = false;
            } else {
                //Overwrite language-specific strings.
                //This way we'll never have missing translation keys.
                $foundlang = include $lang_file;
            }
        }
        $this->language = $PHPMAILER_LANG;
        return (bool) $foundlang;
        //Returns false if language not found
    }
    /**
     * Get the array of strings for the current language.
     *
     * @return array
     */
    public function get_translations()
    {
        return $this->language;
    }
    /**
     * Create recipient headers.
     *
     * @param array  $addr An array of recipients,
     *                     where each recipient is a 2-element indexed array with element 0 containing an address
     *                     and element 1 containing a name, like:
     *                     [['joe@example.com', 'Joe User'], ['zoe@example.com', 'Zoe User']]
     *
     */
    public function addr_append(string $type, $addr): string
    {
        $addresses = [];
        foreach ($addr as $address) {
            $addresses[] = $this->addr_format($address);
        }
        return $type . ': ' . implode(', ', $addresses) . static::$LE;
    }
    /**
     * Format an address for use in a message header.
     *
     * @param array $addr A 2-element indexed array, element 0 containing an address, element 1 containing a name like
     *                    ['joe@example.com', 'Joe User']
     *
     * @return string
     */
    public function addr_format(array $addr)
    {
        if (empty($addr[1])) {
            //No name provided
            return $this->secure_header($addr[0]);
        }
        return $this->encode_header($this->secure_header($addr[1]), 'phrase') . ' <' . $this->secure_header($addr[0]) . '>';
    }
    /**
     * Word-wrap message.
     * For use with mailers that do not automatically perform wrapping
     * and for quoted-printable encoded messages.
     * Original written by philippe.
     *
     * @param string $message The message to wrap
     * @param int    $length  The line length to wrap to
     * @param bool   $qp_mode Whether to run in Quoted-Printable mode
     */
    public function wrap_text($message, $length, $qp_mode = false): string
    {
        if ($qp_mode) {
            $soft_break = sprintf(' =%s', static::$LE);
        } else {
            $soft_break = static::$LE;
        }
        //If utf-8 encoding is used, we will need to make sure we don't
        //split multibyte characters when we wrap
        $is_utf8 = static::CHARSET_UTF8 === strtolower($this->char_set);
        $lelen = strlen(static::$LE);
        $crlflen = strlen(static::$LE);
        $message = static::normalize_breaks($message);
        //Remove a trailing line break
        if (substr($message, -$lelen) === static::$LE) {
            $message = substr($message, 0, -$lelen);
        }
        //Split message into lines
        $lines = explode(static::$LE, $message);
        //Message will be rebuilt in here
        $message = '';
        foreach ($lines as $line) {
            $words = explode(' ', $line);
            $buf = '';
            $firstword = true;
            foreach ($words as $word) {
                if ($qp_mode && strlen($word) > $length) {
                    $space_left = $length - strlen($buf) - $crlflen;
                    if (!$firstword) {
                        if ($space_left > 20) {
                            $len = $space_left;
                            if ($is_utf8) {
                                $len = $this->utf8char_boundary($word, $len);
                            } elseif ('=' === substr($word, $len - 1, 1)) {
                                --$len;
                            } elseif ('=' === substr($word, $len - 2, 1)) {
                                $len -= 2;
                            }
                            $part = substr($word, 0, $len);
                            $word = substr($word, $len);
                            $buf .= ' ' . $part;
                            $message .= $buf . sprintf('=%s', static::$LE);
                        } else {
                            $message .= $buf . $soft_break;
                        }
                        $buf = '';
                    }
                    while ($word !== '') {
                        if ($length <= 0) {
                            break;
                        }
                        $len = $length;
                        if ($is_utf8) {
                            $len = $this->utf8char_boundary($word, $len);
                        } elseif ('=' === substr($word, $len - 1, 1)) {
                            --$len;
                        } elseif ('=' === substr($word, $len - 2, 1)) {
                            $len -= 2;
                        }
                        $part = substr($word, 0, $len);
                        $word = substr($word, $len);
                        if ($word !== '') {
                            $message .= $part . sprintf('=%s', static::$LE);
                        } else {
                            $buf = $part;
                        }
                    }
                } else {
                    $buf_o = $buf;
                    if (!$firstword) {
                        $buf .= ' ';
                    }
                    $buf .= $word;
                    if ('' !== $buf_o && strlen($buf) > $length) {
                        $message .= $buf_o . $soft_break;
                        $buf = $word;
                    }
                }
                $firstword = false;
            }
            $message .= $buf . static::$LE;
        }
        return $message;
    }
    /**
     * Find the last character boundary prior to $maxLength in a utf-8
     * quoted-printable encoded string.
     * Original written by Colin Brown.
     *
     * @param string $encodedText utf-8 QP text
     * @param int    $maxLength   Find the last character boundary prior to this length
     *
     * @return int
     */
    public function utf8char_boundary($encoded_text, $max_length)
    {
        $found_split_pos = false;
        $look_back = 3;
        while (!$found_split_pos) {
            $last_chunk = substr($encoded_text, $max_length - $look_back, $look_back);
            $encoded_char_pos = strpos($last_chunk, '=');
            if (false !== $encoded_char_pos) {
                //Found start of encoded character byte within $lookBack block.
                //Check the encoded byte value (the 2 chars after the '=')
                $hex = substr($encoded_text, $max_length - $look_back + $encoded_char_pos + 1, 2);
                $dec = hexdec($hex);
                if ($dec < 128) {
                    //Single byte character.
                    //If the encoded char was found at pos 0, it will fit
                    //otherwise reduce maxLength to start of the encoded char
                    if ($encoded_char_pos > 0) {
                        $max_length -= $look_back - $encoded_char_pos;
                    }
                    $found_split_pos = true;
                } elseif ($dec >= 192) {
                    //First byte of a multi byte character
                    //Reduce maxLength to split at start of character
                    $max_length -= $look_back - $encoded_char_pos;
                    $found_split_pos = true;
                } elseif ($dec < 192) {
                    //Middle byte of a multi byte character, look further back
                    $look_back += 3;
                }
            } else {
                //No encoded character found
                $found_split_pos = true;
            }
        }
        return $max_length;
    }
    /**
     * Apply word wrapping to the message body.
     * Wraps the message body to the number of chars set in the WordWrap property.
     * You should only do this to plain-text bodies as wrapping HTML tags may break them.
     * This is called automatically by createBody(), so you don't need to call it yourself.
     */
    public function set_word_wrap(): void
    {
        if ($this->word_wrap < 1) {
            return;
        }
        switch ($this->message_type) {
            case 'alt':
            case 'alt_inline':
            case 'alt_attach':
            case 'alt_inline_attach':
                $this->alt_body = $this->wrap_text($this->alt_body, $this->word_wrap);
                break;
            default:
                $this->Body = $this->wrap_text($this->Body, $this->word_wrap);
                break;
        }
    }
    /**
     * Assemble message headers.
     *
     * @return string The assembled headers
     */
    public function create_header(): string
    {
        $result = '';
        $result .= $this->header_line('Date', '' === $this->message_date ? self::rfc_date() : $this->message_date);
        //The To header is created automatically by mail(), so needs to be omitted here
        if ('mail' !== $this->Mailer) {
            if ($this->single_to) {
                foreach ($this->to as $toaddr) {
                    $this->single_to_array[] = $this->addr_format($toaddr);
                }
            } elseif (count($this->to) > 0) {
                $result .= $this->addr_append('To', $this->to);
            } elseif (count($this->cc) === 0) {
                $result .= $this->header_line('To', 'undisclosed-recipients:;');
            }
        }
        $result .= $this->addr_append('From', [[trim($this->From), $this->from_name]]);
        //sendmail and mail() extract Cc from the header before sending
        if (count($this->cc) > 0) {
            $result .= $this->addr_append('Cc', $this->cc);
        }
        //sendmail and mail() extract Bcc from the header before sending
        if (('sendmail' === $this->Mailer || 'qmail' === $this->Mailer || 'mail' === $this->Mailer) && count($this->bcc) > 0) {
            $result .= $this->addr_append('Bcc', $this->bcc);
        }
        if (count($this->reply_to) > 0) {
            $result .= $this->addr_append('Reply-To', $this->reply_to);
        }
        //mail() sets the subject itself
        if ('mail' !== $this->Mailer) {
            $result .= $this->header_line('Subject', $this->encode_header($this->secure_header($this->Subject)));
        }
        //Only allow a custom message ID if it conforms to RFC 5322 section 3.6.4
        //https://tools.ietf.org/html/rfc5322#section-3.6.4
        if ('' !== $this->message_id && preg_match('/^<.*@.*>$/', $this->message_id)) {
            $this->last_message_id = $this->message_id;
        } else {
            $this->last_message_id = sprintf('<%s@%s>', $this->uniqueid, $this->server_hostname());
        }
        $result .= $this->header_line('Message-ID', $this->last_message_id);
        if (null !== $this->Priority) {
            $result .= $this->header_line('X-Priority', $this->Priority);
        }
        if ('' === $this->x_mailer) {
            $result .= $this->header_line('X-Mailer', 'PHPMailer ' . self::VERSION . ' (https://github.com/PHPMailer/PHPMailer)');
        } else {
            $my_xmailer = trim((string) $this->x_mailer);
            if ($my_xmailer) {
                $result .= $this->header_line('X-Mailer', $my_xmailer);
            }
        }
        if ('' !== $this->confirm_reading_to) {
            $result .= $this->header_line('Disposition-Notification-To', '<' . $this->confirm_reading_to . '>');
        }
        //Add custom headers
        foreach ($this->custom_header as $header) {
            $result .= $this->header_line(trim((string) $header[0]), $this->encode_header(trim((string) $header[1])));
        }
        if (!$this->sign_key_file) {
            $result .= $this->header_line('MIME-Version', '1.0');
            $result .= $this->get_mail_mime();
        }
        return $result;
    }
    /**
     * Get the message MIME type headers.
     */
    public function get_mail_mime(): string
    {
        $result = '';
        $ismultipart = true;
        switch ($this->message_type) {
            case 'inline':
                $result .= $this->header_line('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                $result .= $this->text_line(' boundary="' . $this->boundary[1] . '"');
                break;
            case 'attach':
            case 'inline_attach':
            case 'alt_attach':
            case 'alt_inline_attach':
                $result .= $this->header_line('Content-Type', static::CONTENT_TYPE_MULTIPART_MIXED . ';');
                $result .= $this->text_line(' boundary="' . $this->boundary[1] . '"');
                break;
            case 'alt':
            case 'alt_inline':
                $result .= $this->header_line('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                $result .= $this->text_line(' boundary="' . $this->boundary[1] . '"');
                break;
            default:
                //Catches case 'plain': and case '':
                $result .= $this->text_line('Content-Type: ' . $this->content_type . '; charset=' . $this->char_set);
                $ismultipart = false;
                break;
        }
        //RFC1341 part 5 says 7bit is assumed if not specified
        if (static::ENCODING_7BIT !== $this->Encoding) {
            //RFC 2045 section 6.4 says multipart MIME parts may only use 7bit, 8bit or binary CTE
            if ($ismultipart) {
                if (static::ENCODING_8BIT === $this->Encoding) {
                    $result .= $this->header_line('Content-Transfer-Encoding', static::ENCODING_8BIT);
                }
                //The only remaining alternatives are quoted-printable and base64, which are both 7bit compatible
            } else {
                $result .= $this->header_line('Content-Transfer-Encoding', $this->Encoding);
            }
        }
        return $result;
    }
    /**
     * Returns the whole MIME message.
     * Includes complete headers and body.
     * Only valid post preSend().
     *
     * @see PHPMailer::preSend()
     */
    public function get_sent_mime_message(): string
    {
        return static::strip_trailing_wsp($this->mime_header . $this->mail_header) . static::$LE . static::$LE . $this->mime_body;
    }
    /**
     * Create a unique ID to use for boundaries.
     */
    protected function generate_id(): string
    {
        $len = 32;
        //32 bytes = 256 bits
        $bytes = '';
        if (function_exists('random_bytes')) {
            try {
                $bytes = random_bytes($len);
            } catch (\Exception) {
                //Do nothing
            }
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            /** @noinspection CryptographicallySecureRandomnessInspection */
            $bytes = openssl_random_pseudo_bytes($len);
        }
        if ($bytes === '') {
            //We failed to produce a proper random string, so make do.
            //Use a hash to force the length to the same as the other methods
            $bytes = hash('sha256', uniqid((string) mt_rand(), true), true);
        }
        //We don't care about messing up base64 format here, just want a random string
        return str_replace(['=', '+', '/'], '', base64_encode(hash('sha256', $bytes, true)));
    }
    /**
     * Assemble the message body.
     * Returns an empty string on failure.
     *
     * @throws Exception
     *
     * @return string The assembled message body
     */
    public function create_body(): string
    {
        $body = '';
        //Create unique IDs and preset boundaries
        $this->uniqueid = $this->generate_id();
        $this->boundary[1] = 'b1_' . $this->uniqueid;
        $this->boundary[2] = 'b2_' . $this->uniqueid;
        $this->boundary[3] = 'b3_' . $this->uniqueid;
        if ($this->sign_key_file) {
            $body .= $this->get_mail_mime() . static::$LE;
        }
        $this->set_word_wrap();
        $body_encoding = $this->Encoding;
        $body_char_set = $this->char_set;
        //Can we do a 7-bit downgrade?
        if (static::ENCODING_8BIT === $body_encoding && !$this->has8bit_chars($this->Body)) {
            $body_encoding = static::ENCODING_7BIT;
            //All ISO 8859, Windows codepage and UTF-8 charsets are ascii compatible up to 7-bit
            $body_char_set = static::CHARSET_ASCII;
        }
        //If lines are too long, and we're not already using an encoding that will shorten them,
        //change to quoted-printable transfer encoding for the body part only
        if (static::ENCODING_BASE64 !== $this->Encoding && static::has_line_longer_than_max($this->Body)) {
            $body_encoding = static::ENCODING_QUOTED_PRINTABLE;
        }
        $alt_body_encoding = $this->Encoding;
        $alt_body_char_set = $this->char_set;
        //Can we do a 7-bit downgrade?
        if (static::ENCODING_8BIT === $alt_body_encoding && !$this->has8bit_chars($this->alt_body)) {
            $alt_body_encoding = static::ENCODING_7BIT;
            //All ISO 8859, Windows codepage and UTF-8 charsets are ascii compatible up to 7-bit
            $alt_body_char_set = static::CHARSET_ASCII;
        }
        //If lines are too long, and we're not already using an encoding that will shorten them,
        //change to quoted-printable transfer encoding for the alt body part only
        if (static::ENCODING_BASE64 !== $alt_body_encoding && static::has_line_longer_than_max($this->alt_body)) {
            $alt_body_encoding = static::ENCODING_QUOTED_PRINTABLE;
        }
        //Use this as a preamble in all multipart message types
        $mimepre = 'This is a multi-part message in MIME format.' . static::$LE . static::$LE;
        switch ($this->message_type) {
            case 'inline':
                $body .= $mimepre;
                $body .= $this->get_boundary($this->boundary[1], $body_char_set, '', $body_encoding);
                $body .= $this->encode_string($this->Body, $body_encoding);
                $body .= static::$LE;
                $body .= $this->attach_all('inline', $this->boundary[1]);
                break;
            case 'attach':
                $body .= $mimepre;
                $body .= $this->get_boundary($this->boundary[1], $body_char_set, '', $body_encoding);
                $body .= $this->encode_string($this->Body, $body_encoding);
                $body .= static::$LE;
                $body .= $this->attach_all('attachment', $this->boundary[1]);
                break;
            case 'inline_attach':
                $body .= $mimepre;
                $body .= $this->text_line('--' . $this->boundary[1]);
                $body .= $this->header_line('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                $body .= $this->text_line(' boundary="' . $this->boundary[2] . '";');
                $body .= $this->text_line(' type="' . static::CONTENT_TYPE_TEXT_HTML . '"');
                $body .= static::$LE;
                $body .= $this->get_boundary($this->boundary[2], $body_char_set, '', $body_encoding);
                $body .= $this->encode_string($this->Body, $body_encoding);
                $body .= static::$LE;
                $body .= $this->attach_all('inline', $this->boundary[2]);
                $body .= static::$LE;
                $body .= $this->attach_all('attachment', $this->boundary[1]);
                break;
            case 'alt':
                $body .= $mimepre;
                $body .= $this->get_boundary($this->boundary[1], $alt_body_char_set, static::CONTENT_TYPE_PLAINTEXT, $alt_body_encoding);
                $body .= $this->encode_string($this->alt_body, $alt_body_encoding);
                $body .= static::$LE;
                $body .= $this->get_boundary($this->boundary[1], $body_char_set, static::CONTENT_TYPE_TEXT_HTML, $body_encoding);
                $body .= $this->encode_string($this->Body, $body_encoding);
                $body .= static::$LE;
                if (!empty($this->Ical)) {
                    $method = static::ICAL_METHOD_REQUEST;
                    foreach (static::$ical_methods as $imethod) {
                        if (stripos($this->Ical, 'METHOD:' . $imethod) !== false) {
                            $method = $imethod;
                            break;
                        }
                    }
                    $body .= $this->get_boundary($this->boundary[1], '', static::CONTENT_TYPE_TEXT_CALENDAR . '; method=' . $method, '');
                    $body .= $this->encode_string($this->Ical, $this->Encoding);
                    $body .= static::$LE;
                }
                $body .= $this->end_boundary($this->boundary[1]);
                break;
            case 'alt_inline':
                $body .= $mimepre;
                $body .= $this->get_boundary($this->boundary[1], $alt_body_char_set, static::CONTENT_TYPE_PLAINTEXT, $alt_body_encoding);
                $body .= $this->encode_string($this->alt_body, $alt_body_encoding);
                $body .= static::$LE;
                $body .= $this->text_line('--' . $this->boundary[1]);
                $body .= $this->header_line('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                $body .= $this->text_line(' boundary="' . $this->boundary[2] . '";');
                $body .= $this->text_line(' type="' . static::CONTENT_TYPE_TEXT_HTML . '"');
                $body .= static::$LE;
                $body .= $this->get_boundary($this->boundary[2], $body_char_set, static::CONTENT_TYPE_TEXT_HTML, $body_encoding);
                $body .= $this->encode_string($this->Body, $body_encoding);
                $body .= static::$LE;
                $body .= $this->attach_all('inline', $this->boundary[2]);
                $body .= static::$LE;
                $body .= $this->end_boundary($this->boundary[1]);
                break;
            case 'alt_attach':
                $body .= $mimepre;
                $body .= $this->text_line('--' . $this->boundary[1]);
                $body .= $this->header_line('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                $body .= $this->text_line(' boundary="' . $this->boundary[2] . '"');
                $body .= static::$LE;
                $body .= $this->get_boundary($this->boundary[2], $alt_body_char_set, static::CONTENT_TYPE_PLAINTEXT, $alt_body_encoding);
                $body .= $this->encode_string($this->alt_body, $alt_body_encoding);
                $body .= static::$LE;
                $body .= $this->get_boundary($this->boundary[2], $body_char_set, static::CONTENT_TYPE_TEXT_HTML, $body_encoding);
                $body .= $this->encode_string($this->Body, $body_encoding);
                $body .= static::$LE;
                if (!empty($this->Ical)) {
                    $method = static::ICAL_METHOD_REQUEST;
                    foreach (static::$ical_methods as $imethod) {
                        if (stripos($this->Ical, 'METHOD:' . $imethod) !== false) {
                            $method = $imethod;
                            break;
                        }
                    }
                    $body .= $this->get_boundary($this->boundary[2], '', static::CONTENT_TYPE_TEXT_CALENDAR . '; method=' . $method, '');
                    $body .= $this->encode_string($this->Ical, $this->Encoding);
                }
                $body .= $this->end_boundary($this->boundary[2]);
                $body .= static::$LE;
                $body .= $this->attach_all('attachment', $this->boundary[1]);
                break;
            case 'alt_inline_attach':
                $body .= $mimepre;
                $body .= $this->text_line('--' . $this->boundary[1]);
                $body .= $this->header_line('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                $body .= $this->text_line(' boundary="' . $this->boundary[2] . '"');
                $body .= static::$LE;
                $body .= $this->get_boundary($this->boundary[2], $alt_body_char_set, static::CONTENT_TYPE_PLAINTEXT, $alt_body_encoding);
                $body .= $this->encode_string($this->alt_body, $alt_body_encoding);
                $body .= static::$LE;
                $body .= $this->text_line('--' . $this->boundary[2]);
                $body .= $this->header_line('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                $body .= $this->text_line(' boundary="' . $this->boundary[3] . '";');
                $body .= $this->text_line(' type="' . static::CONTENT_TYPE_TEXT_HTML . '"');
                $body .= static::$LE;
                $body .= $this->get_boundary($this->boundary[3], $body_char_set, static::CONTENT_TYPE_TEXT_HTML, $body_encoding);
                $body .= $this->encode_string($this->Body, $body_encoding);
                $body .= static::$LE;
                $body .= $this->attach_all('inline', $this->boundary[3]);
                $body .= static::$LE;
                $body .= $this->end_boundary($this->boundary[2]);
                $body .= static::$LE;
                $body .= $this->attach_all('attachment', $this->boundary[1]);
                break;
            default:
                //Catch case 'plain' and case '', applies to simple `text/plain` and `text/html` body content types
                //Reset the `Encoding` property in case we changed it for line length reasons
                $this->Encoding = $body_encoding;
                $body .= $this->encode_string($this->Body, $this->Encoding);
                break;
        }
        if ($this->is_error()) {
            $body = '';
            if ($this->exceptions) {
                throw new Exception($this->lang('empty_message'), self::STOP_CRITICAL);
            }
        } elseif ($this->sign_key_file) {
            try {
                if (!defined('PKCS7_TEXT')) {
                    throw new Exception($this->lang('extension_missing') . 'openssl');
                }
                $file = tempnam(sys_get_temp_dir(), 'srcsign');
                $signed = tempnam(sys_get_temp_dir(), 'mailsign');
                file_put_contents($file, $body);
                //Workaround for PHP bug https://bugs.php.net/bug.php?id=69197
                if (empty($this->sign_extracerts_file)) {
                    $sign = @openssl_pkcs7_sign($file, $signed, 'file://' . realpath($this->sign_cert_file), ['file://' . realpath($this->sign_key_file), $this->sign_key_pass], []);
                } else {
                    $sign = @openssl_pkcs7_sign($file, $signed, 'file://' . realpath($this->sign_cert_file), ['file://' . realpath($this->sign_key_file), $this->sign_key_pass], [], PKCS7_DETACHED, $this->sign_extracerts_file);
                }
                @unlink($file);
                if ($sign) {
                    $body = file_get_contents($signed);
                    @unlink($signed);
                    //The message returned by openssl contains both headers and body, so need to split them up
                    $parts = explode("\n\n", $body, 2);
                    $this->mime_header .= $parts[0] . static::$LE . static::$LE;
                    $body = $parts[1];
                } else {
                    @unlink($signed);
                    throw new Exception($this->lang('signing') . openssl_error_string());
                }
            } catch (Exception $exc) {
                $body = '';
                if ($this->exceptions) {
                    throw $exc;
                }
            }
        }
        return $body;
    }
    /**
     * Return the start of a message boundary.
     *
     * @param string $charSet
     * @param string $contentType
     * @param string $encoding
     *
     */
    protected function get_boundary(string $boundary, $char_set, $content_type, $encoding): string
    {
        $result = '';
        if ('' === $char_set) {
            $char_set = $this->char_set;
        }
        if ('' === $content_type) {
            $content_type = $this->content_type;
        }
        if ('' === $encoding) {
            $encoding = $this->Encoding;
        }
        $result .= $this->text_line('--' . $boundary);
        $result .= sprintf('Content-Type: %s; charset=%s', $content_type, $char_set);
        $result .= static::$LE;
        //RFC1341 part 5 says 7bit is assumed if not specified
        if (static::ENCODING_7BIT !== $encoding) {
            $result .= $this->header_line('Content-Transfer-Encoding', $encoding);
        }
        return $result . static::$LE;
    }
    /**
     * Return the end of a message boundary.
     *
     *
     */
    protected function end_boundary(string $boundary): string
    {
        return static::$LE . '--' . $boundary . '--' . static::$LE;
    }
    /**
     * Set the message type.
     * PHPMailer only supports some preset message types, not arbitrary MIME structures.
     */
    protected function set_message_type()
    {
        $type = [];
        if ($this->alternative_exists()) {
            $type[] = 'alt';
        }
        if ($this->inline_image_exists()) {
            $type[] = 'inline';
        }
        if ($this->attachment_exists()) {
            $type[] = 'attach';
        }
        $this->message_type = implode('_', $type);
        if ('' === $this->message_type) {
            //The 'plain' message_type refers to the message having a single body element, not that it is plain-text
            $this->message_type = 'plain';
        }
    }
    /**
     * Format a header line.
     *
     * @param string|int $value
     *
     */
    public function header_line(string $name, $value): string
    {
        return $name . ': ' . $value . static::$LE;
    }
    /**
     * Return a formatted mail line.
     *
     *
     */
    public function text_line(string $value): string
    {
        return $value . static::$LE;
    }
    /**
     * Add an attachment from a path on the filesystem.
     * Never use a user-supplied path to a file!
     * Returns false if the file could not be found or read.
     * Explicitly *does not* support passing URLs; PHPMailer is not an HTTP client.
     * If you need to do that, fetch the resource yourself and pass it in via a local file or string.
     *
     * @param string $path        Path to the attachment
     * @param string $name        Overrides the attachment name
     * @param string $encoding    File encoding (see $Encoding)
     * @param string $type        MIME type, e.g. `image/jpeg`; determined automatically from $path if not specified
     * @param string $disposition Disposition to use
     *
     * @throws Exception
     */
    public function add_attachment(string $path, $name = '', string $encoding = self::ENCODING_BASE64, $type = '', $disposition = 'attachment'): bool
    {
        try {
            if (!static::file_is_accessible($path)) {
                throw new Exception($this->lang('file_access') . $path, self::STOP_CONTINUE);
            }
            //If a MIME type is not specified, try to work it out from the file name
            if ('' === $type) {
                $type = static::filename_to_type($path);
            }
            $filename = (string) static::mb_pathinfo($path, PATHINFO_BASENAME);
            if ('' === $name) {
                $name = $filename;
            }
            if (!$this->validate_encoding($encoding)) {
                throw new Exception($this->lang('encoding') . $encoding);
            }
            $this->attachment[] = [
                0 => $path,
                1 => $filename,
                2 => $name,
                3 => $encoding,
                4 => $type,
                5 => false,
                //isStringAttachment
                6 => $disposition,
                7 => $name,
            ];
        } catch (Exception $exc) {
            $this->set_error($exc->get_message());
            $this->edebug($exc->get_message());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
        return true;
    }
    /**
     * Return the array of attachments.
     *
     * @return array
     */
    public function get_attachments()
    {
        return $this->attachment;
    }
    /**
     * Attach all file, string, and binary attachments to the message.
     * Returns an empty string on failure.
     *
     * @param string $disposition_type
     * @param string $boundary
     *
     * @throws Exception
     */
    protected function attach_all($disposition_type, $boundary): string
    {
        //Return text of body
        $mime = [];
        $cid_uniq = [];
        $incl = [];
        //Add all attachments
        foreach ($this->attachment as $attachment) {
            //Check if it is a valid disposition_filter
            if ($attachment[6] === $disposition_type) {
                //Check for string attachment
                $string = '';
                $path = '';
                $b_string = $attachment[5];
                if ($b_string) {
                    $string = $attachment[0];
                } else {
                    $path = $attachment[0];
                }
                $inclhash = hash('sha256', serialize($attachment));
                if (in_array($inclhash, $incl, true)) {
                    continue;
                }
                $incl[] = $inclhash;
                $name = $attachment[2];
                $encoding = $attachment[3];
                $type = $attachment[4];
                $disposition = $attachment[6];
                $cid = $attachment[7];
                if ('inline' === $disposition && array_key_exists($cid, $cid_uniq)) {
                    continue;
                }
                $cid_uniq[$cid] = true;
                $mime[] = sprintf('--%s%s', $boundary, static::$LE);
                //Only include a filename property if we have one
                if (!empty($name)) {
                    $mime[] = sprintf('Content-Type: %s; name=%s%s', $type, static::quoted_string($this->encode_header($this->secure_header($name))), static::$LE);
                } else {
                    $mime[] = sprintf('Content-Type: %s%s', $type, static::$LE);
                }
                //RFC1341 part 5 says 7bit is assumed if not specified
                if (static::ENCODING_7BIT !== $encoding) {
                    $mime[] = sprintf('Content-Transfer-Encoding: %s%s', $encoding, static::$LE);
                }
                //Only set Content-IDs on inline attachments
                if ((string) $cid !== '' && $disposition === 'inline') {
                    $mime[] = 'Content-ID: <' . $this->encode_header($this->secure_header($cid)) . '>' . static::$LE;
                }
                //Allow for bypassing the Content-Disposition header
                if (!empty($disposition)) {
                    $encoded_name = $this->encode_header($this->secure_header($name));
                    if (!empty($encoded_name)) {
                        $mime[] = sprintf('Content-Disposition: %s; filename=%s%s', $disposition, static::quoted_string($encoded_name), static::$LE . static::$LE);
                    } else {
                        $mime[] = sprintf('Content-Disposition: %s%s', $disposition, static::$LE . static::$LE);
                    }
                } else {
                    $mime[] = static::$LE;
                }
                //Encode as string attachment
                if ($b_string) {
                    $mime[] = $this->encode_string($string, $encoding);
                } else {
                    $mime[] = $this->encode_file($path, $encoding);
                }
                if ($this->is_error()) {
                    return '';
                }
                $mime[] = static::$LE;
            }
        }
        $mime[] = sprintf('--%s--%s', $boundary, static::$LE);
        return implode('', $mime);
    }
    /**
     * Encode a file attachment in requested format.
     * Returns an empty string on failure.
     *
     * @param string $path     The full path to the file
     * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
     *
     * @return string
     */
    protected function encode_file(string $path, $encoding = self::ENCODING_BASE64)
    {
        try {
            if (!static::file_is_accessible($path)) {
                throw new Exception($this->lang('file_open') . $path, self::STOP_CONTINUE);
            }
            $file_buffer = file_get_contents($path);
            if (false === $file_buffer) {
                throw new Exception($this->lang('file_open') . $path, self::STOP_CONTINUE);
            }
            return $this->encode_string($file_buffer, $encoding);
        } catch (Exception $exc) {
            $this->set_error($exc->get_message());
            $this->edebug($exc->get_message());
            if ($this->exceptions) {
                throw $exc;
            }
            return '';
        }
    }
    /**
     * Encode a string in requested format.
     * Returns an empty string on failure.
     *
     * @param string $str      The text to encode
     * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
     *
     * @throws Exception
     *
     * @return string
     */
    public function encode_string($str, string $encoding = self::ENCODING_BASE64)
    {
        $encoded = '';
        switch (strtolower($encoding)) {
            case static::ENCODING_BASE64:
                $encoded = chunk_split(base64_encode($str), static::STD_LINE_LENGTH, static::$LE);
                break;
            case static::ENCODING_7BIT:
            case static::ENCODING_8BIT:
                $encoded = static::normalize_breaks($str);
                //Make sure it ends with a line break
                if (!str_ends_with($encoded, static::$LE)) {
                    $encoded .= static::$LE;
                }
                break;
            case static::ENCODING_BINARY:
                $encoded = $str;
                break;
            case static::ENCODING_QUOTED_PRINTABLE:
                $encoded = $this->encode_qp($str);
                break;
            default:
                $this->set_error($this->lang('encoding') . $encoding);
                if ($this->exceptions) {
                    throw new Exception($this->lang('encoding') . $encoding);
                }
                break;
        }
        return $encoded;
    }
    /**
     * Encode a header value (not including its label) optimally.
     * Picks shortest of Q, B, or none. Result includes folding if needed.
     * See RFC822 definitions for phrase, comment and text positions.
     *
     * @param string $str      The header value to encode
     * @param string $position What context the string will be used in
     *
     * @return string
     */
    public function encode_header($str, $position = 'text')
    {
        $matchcount = 0;
        switch (strtolower($position)) {
            case 'phrase':
                if (!preg_match('/[\200-\377]/', $str)) {
                    //Can't use addslashes as we don't know the value of magic_quotes_sybase
                    $encoded = addcslashes($str, "\x00..\x1f\\\"");
                    if ($str === $encoded && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)) {
                        return $encoded;
                    }
                    return "\"{$encoded}\"";
                }
                $matchcount = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
                break;
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'comment':
                $matchcount = preg_match_all('/[()"]/', $str, $matches);
            //fallthrough
            // no break
            case 'text':
            default:
                $matchcount += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
                break;
        }
        if ($this->has8bit_chars($str)) {
            $charset = $this->char_set;
        } else {
            $charset = static::CHARSET_ASCII;
        }
        //Q/B encoding adds 8 chars and the charset ("` =?<charset>?[QB]?<content>?=`").
        $overhead = 8 + strlen((string) $charset);
        if ('mail' === $this->Mailer) {
            $maxlen = static::MAIL_MAX_LINE_LENGTH - $overhead;
        } else {
            $maxlen = static::MAX_LINE_LENGTH - $overhead;
        }
        //Select the encoding that produces the shortest output and/or prevents corruption.
        if ($matchcount > strlen($str) / 3) {
            //More than 1/3 of the content needs encoding, use B-encode.
            $encoding = 'B';
        } elseif ($matchcount > 0) {
            //Less than 1/3 of the content needs encoding, use Q-encode.
            $encoding = 'Q';
        } elseif (strlen($str) > $maxlen) {
            //No encoding needed, but value exceeds max line length, use Q-encode to prevent corruption.
            $encoding = 'Q';
        } else {
            //No reformatting needed
            $encoding = false;
        }
        switch ($encoding) {
            case 'B':
                if ($this->has_multi_bytes($str)) {
                    //Use a custom function which correctly encodes and wraps long
                    //multibyte strings without breaking lines within a character
                    $encoded = $this->base64encode_wrap_mb($str, "\n");
                } else {
                    $encoded = base64_encode($str);
                    $maxlen -= $maxlen % 4;
                    $encoded = trim(chunk_split($encoded, $maxlen, "\n"));
                }
                $encoded = preg_replace('/^(.*)$/m', ' =?' . $charset . "?{$encoding}?\\1?=", $encoded);
                break;
            case 'Q':
                $encoded = $this->encode_q($str, $position);
                $encoded = $this->wrap_text($encoded, $maxlen, true);
                $encoded = str_replace('=' . static::$LE, "\n", trim($encoded));
                $encoded = preg_replace('/^(.*)$/m', ' =?' . $charset . "?{$encoding}?\\1?=", $encoded);
                break;
            default:
                return $str;
        }
        return trim(static::normalize_breaks($encoded));
    }
    /**
     * Check if a string contains multi-byte characters.
     *
     * @param string $str multi-byte text to wrap encode
     *
     * @return bool
     */
    public function has_multi_bytes($str)
    {
        if (function_exists('mb_strlen')) {
            return strlen($str) > mb_strlen($str, $this->char_set);
        }
        //Assume no multibytes (we can't handle without mbstring functions anyway)
        return false;
    }
    /**
     * Does a string contain any 8-bit chars (in any charset)?
     *
     * @param string $text
     */
    public function has8bit_chars($text): bool
    {
        return (bool) preg_match('/[\x80-\xFF]/', $text);
    }
    /**
     * Encode and wrap long multibyte strings for mail headers
     * without breaking lines within a character.
     * Adapted from a function by paravoid.
     *
     * @see http://www.php.net/manual/en/function.mb-encode-mimeheader.php#60283
     *
     * @param string $str       multi-byte text to wrap encode
     * @param string $linebreak string to use as linefeed/end-of-line
     */
    public function base64encode_wrap_mb($str, $linebreak = null): string
    {
        $start = '=?' . $this->char_set . '?B?';
        $end = '?=';
        $encoded = '';
        if (null === $linebreak) {
            $linebreak = static::$LE;
        }
        $mb_length = mb_strlen($str, $this->char_set);
        //Each line must have length <= 75, including $start and $end
        $length = 75 - strlen($start) - strlen($end);
        //Average multi-byte ratio
        $ratio = $mb_length / strlen($str);
        //Base64 has a 4:3 ratio
        $avg_length = floor($length * $ratio * 0.75);
        $offset = 0;
        for ($i = 0; $i < $mb_length; $i += $offset) {
            $look_back = 0;
            do {
                $offset = $avg_length - $look_back;
                $chunk = mb_substr($str, $i, $offset, $this->char_set);
                $chunk = base64_encode($chunk);
                ++$look_back;
            } while (strlen($chunk) > $length);
            $encoded .= $chunk . $linebreak;
        }
        //Chomp the last linefeed
        return substr($encoded, 0, -strlen($linebreak));
    }
    /**
     * Encode a string in quoted-printable format.
     * According to RFC2045 section 6.7.
     *
     * @param string $string The text to encode
     *
     * @return string
     */
    public function encode_qp($string)
    {
        return static::normalize_breaks(quoted_printable_encode($string));
    }
    /**
     * Encode a string using Q encoding.
     *
     * @see http://tools.ietf.org/html/rfc2047#section-4.2
     *
     * @param string $str      the text to encode
     * @param string $position Where the text is going to be used, see the RFC for what that means
     */
    public function encode_q($str, $position = 'text'): string
    {
        //There should not be any EOL in the string
        $pattern = '';
        $encoded = str_replace(["\r", "\n"], '', $str);
        switch (strtolower($position)) {
            case 'phrase':
                //RFC 2047 section 5.3
                $pattern = '^A-Za-z0-9!*+\/ -';
                break;
            /*
             * RFC 2047 section 5.2.
             * Build $pattern without including delimiters and []
             */
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'comment':
                $pattern = '\(\)"';
            /* Intentional fall through */
            // no break
            case 'text':
            default:
                //RFC 2047 section 5.1
                //Replace every high ascii, control, =, ? and _ characters
                $pattern = '\000-\011\013\014\016-\037\075\077\137\177-\377' . $pattern;
                break;
        }
        $matches = [];
        if (preg_match_all("/[{$pattern}]/", $encoded, $matches)) {
            //If the string contains an '=', make sure it's the first thing we replace
            //so as to avoid double-encoding
            $eqkey = array_search('=', $matches[0], true);
            if (false !== $eqkey) {
                unset($matches[0][$eqkey]);
                array_unshift($matches[0], '=');
            }
            foreach (array_unique($matches[0]) as $char) {
                $encoded = str_replace($char, '=' . sprintf('%02X', ord($char)), $encoded);
            }
        }
        //Replace spaces with _ (more readable than =20)
        //RFC 2047 section 4.2(2)
        return str_replace(' ', '_', $encoded);
    }
    /**
     * Add a string or binary attachment (non-filesystem).
     * This method can be used to attach ascii or binary data,
     * such as a BLOB record from a database.
     *
     * @param string $string      String attachment data
     * @param string $filename    Name of the attachment
     * @param string $encoding    File encoding (see $Encoding)
     * @param string $type        File extension (MIME) type
     * @param string $disposition Disposition to use
     *
     * @throws Exception
     *
     * @return bool True on successfully adding an attachment
     */
    public function add_string_attachment($string, $filename, string $encoding = self::ENCODING_BASE64, $type = '', $disposition = 'attachment'): bool
    {
        try {
            //If a MIME type is not specified, try to work it out from the file name
            if ('' === $type) {
                $type = static::filename_to_type($filename);
            }
            if (!$this->validate_encoding($encoding)) {
                throw new Exception($this->lang('encoding') . $encoding);
            }
            //Append to $attachment array
            $this->attachment[] = [
                0 => $string,
                1 => $filename,
                2 => static::mb_pathinfo($filename, PATHINFO_BASENAME),
                3 => $encoding,
                4 => $type,
                5 => true,
                //isStringAttachment
                6 => $disposition,
                7 => 0,
            ];
        } catch (Exception $exc) {
            $this->set_error($exc->get_message());
            $this->edebug($exc->get_message());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
        return true;
    }
    /**
     * Add an embedded (inline) attachment from a file.
     * This can include images, sounds, and just about any other document type.
     * These differ from 'regular' attachments in that they are intended to be
     * displayed inline with the message, not just attached for download.
     * This is used in HTML messages that embed the images
     * the HTML refers to using the $cid value.
     * Never use a user-supplied path to a file!
     *
     * @param string $path        Path to the attachment
     * @param string $cid         Content ID of the attachment; Use this to reference
     *                            the content when using an embedded image in HTML
     * @param string $name        Overrides the attachment name
     * @param string $encoding    File encoding (see $Encoding)
     * @param string $type        File MIME type
     * @param string $disposition Disposition to use
     *
     * @throws Exception
     *
     * @return bool True on successfully adding an attachment
     */
    public function add_embedded_image(string $path, $cid, $name = '', string $encoding = self::ENCODING_BASE64, $type = '', $disposition = 'inline'): bool
    {
        try {
            if (!static::file_is_accessible($path)) {
                throw new Exception($this->lang('file_access') . $path, self::STOP_CONTINUE);
            }
            //If a MIME type is not specified, try to work it out from the file name
            if ('' === $type) {
                $type = static::filename_to_type($path);
            }
            if (!$this->validate_encoding($encoding)) {
                throw new Exception($this->lang('encoding') . $encoding);
            }
            $filename = (string) static::mb_pathinfo($path, PATHINFO_BASENAME);
            if ('' === $name) {
                $name = $filename;
            }
            //Append to $attachment array
            $this->attachment[] = [
                0 => $path,
                1 => $filename,
                2 => $name,
                3 => $encoding,
                4 => $type,
                5 => false,
                //isStringAttachment
                6 => $disposition,
                7 => $cid,
            ];
        } catch (Exception $exc) {
            $this->set_error($exc->get_message());
            $this->edebug($exc->get_message());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
        return true;
    }
    /**
     * Add an embedded stringified attachment.
     * This can include images, sounds, and just about any other document type.
     * If your filename doesn't contain an extension, be sure to set the $type to an appropriate MIME type.
     *
     * @param string $string      The attachment binary data
     * @param string $cid         Content ID of the attachment; Use this to reference
     *                            the content when using an embedded image in HTML
     * @param string $name        A filename for the attachment. If this contains an extension,
     *                            PHPMailer will attempt to set a MIME type for the attachment.
     *                            For example 'file.jpg' would get an 'image/jpeg' MIME type.
     * @param string $encoding    File encoding (see $Encoding), defaults to 'base64'
     * @param string $type        MIME type - will be used in preference to any automatically derived type
     * @param string $disposition Disposition to use
     *
     * @throws Exception
     *
     * @return bool True on successfully adding an attachment
     */
    public function add_string_embedded_image($string, $cid, $name = '', string $encoding = self::ENCODING_BASE64, $type = '', $disposition = 'inline'): bool
    {
        try {
            //If a MIME type is not specified, try to work it out from the name
            if ('' === $type && !empty($name)) {
                $type = static::filename_to_type($name);
            }
            if (!$this->validate_encoding($encoding)) {
                throw new Exception($this->lang('encoding') . $encoding);
            }
            //Append to $attachment array
            $this->attachment[] = [
                0 => $string,
                1 => $name,
                2 => $name,
                3 => $encoding,
                4 => $type,
                5 => true,
                //isStringAttachment
                6 => $disposition,
                7 => $cid,
            ];
        } catch (Exception $exc) {
            $this->set_error($exc->get_message());
            $this->edebug($exc->get_message());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
        return true;
    }
    /**
     * Validate encodings.
     *
     * @param string $encoding
     */
    protected function validate_encoding($encoding): bool
    {
        return in_array($encoding, [self::ENCODING_7BIT, self::ENCODING_QUOTED_PRINTABLE, self::ENCODING_BASE64, self::ENCODING_8BIT, self::ENCODING_BINARY], true);
    }
    /**
     * Check if an embedded attachment is present with this cid.
     *
     * @param string $cid
     */
    protected function cid_exists($cid): bool
    {
        foreach ($this->attachment as $attachment) {
            if ('inline' === $attachment[6] && $cid === $attachment[7]) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if an inline attachment is present.
     */
    public function inline_image_exists(): bool
    {
        foreach ($this->attachment as $attachment) {
            if ('inline' === $attachment[6]) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if an attachment (non-inline) is present.
     */
    public function attachment_exists(): bool
    {
        foreach ($this->attachment as $attachment) {
            if ('attachment' === $attachment[6]) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if this message has an alternative body set.
     */
    public function alternative_exists(): bool
    {
        return !empty($this->alt_body);
    }
    /**
     * Clear queued addresses of given kind.
     *
     * @param string $kind 'to', 'cc', or 'bcc'
     */
    public function clear_queued_addresses($kind): void
    {
        $this->recipients_queue = array_filter($this->recipients_queue, static fn(array $params) => $params[0] !== $kind);
    }
    /**
     * Clear all To recipients.
     */
    public function clear_addresses(): void
    {
        foreach ($this->to as $to) {
            unset($this->all_recipients[strtolower((string) $to[0])]);
        }
        $this->to = [];
        $this->clear_queued_addresses('to');
    }
    /**
     * Clear all CC recipients.
     */
    public function clear_c_cs(): void
    {
        foreach ($this->cc as $cc) {
            unset($this->all_recipients[strtolower((string) $cc[0])]);
        }
        $this->cc = [];
        $this->clear_queued_addresses('cc');
    }
    /**
     * Clear all BCC recipients.
     */
    public function clear_bc_cs(): void
    {
        foreach ($this->bcc as $bcc) {
            unset($this->all_recipients[strtolower((string) $bcc[0])]);
        }
        $this->bcc = [];
        $this->clear_queued_addresses('bcc');
    }
    /**
     * Clear all ReplyTo recipients.
     */
    public function clear_reply_tos(): void
    {
        $this->reply_to = [];
        $this->reply_to_queue = [];
    }
    /**
     * Clear all recipient types.
     */
    public function clear_all_recipients(): void
    {
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        $this->all_recipients = [];
        $this->recipients_queue = [];
    }
    /**
     * Clear all filesystem, string, and binary attachments.
     */
    public function clear_attachments(): void
    {
        $this->attachment = [];
    }
    /**
     * Clear all custom headers.
     */
    public function clear_custom_headers(): void
    {
        $this->custom_header = [];
    }
    /**
     * Add an error message to the error container.
     *
     * @param string $msg
     */
    protected function set_error($msg)
    {
        ++$this->error_count;
        if ('smtp' === $this->Mailer && null !== $this->smtp) {
            $lasterror = $this->smtp->get_error();
            if (!empty($lasterror['error'])) {
                $msg .= $this->lang('smtp_error') . $lasterror['error'];
                if (!empty($lasterror['detail'])) {
                    $msg .= ' Detail: ' . $lasterror['detail'];
                }
                if (!empty($lasterror['smtp_code'])) {
                    $msg .= ' SMTP code: ' . $lasterror['smtp_code'];
                }
                if (!empty($lasterror['smtp_code_ex'])) {
                    $msg .= ' Additional SMTP info: ' . $lasterror['smtp_code_ex'];
                }
            }
        }
        $this->error_info = $msg;
    }
    /**
     * Return an RFC 822 formatted date.
     */
    public static function rfc_date(): string
    {
        //Set the time zone to whatever the default is to avoid 500 errors
        //Will default to UTC if it's not set properly in php.ini
        date_default_timezone_set(@date_default_timezone_get());
        return date('D, j M Y H:i:s O');
    }
    /**
     * Get the server hostname.
     * Returns 'localhost.localdomain' if unknown.
     *
     * @return string
     */
    protected function server_hostname()
    {
        $result = '';
        if (!empty($this->Hostname)) {
            $result = $this->Hostname;
        } elseif (isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER)) {
            $result = $_SERVER['SERVER_NAME'];
        } elseif (function_exists('gethostname') && gethostname() !== false) {
            $result = gethostname();
        } elseif (php_uname('n') !== false) {
            $result = php_uname('n');
        }
        if (!static::is_valid_host($result)) {
            return 'localhost.localdomain';
        }
        return $result;
    }
    /**
     * Validate whether a string contains a valid value to use as a hostname or IP address.
     * IPv6 addresses must include [], e.g. `[::1]`, not just `::1`.
     *
     * @param string $host The host name or IP address to check
     *
     * @return bool
     */
    public static function is_valid_host($host)
    {
        //Simple syntax limits
        if (empty($host) || !is_string($host) || strlen($host) > 256 || !preg_match('/^([a-zA-Z\d.-]*|\[[a-fA-F\d:]+])$/', $host)) {
            return false;
        }
        //Looks like a bracketed IPv6 address
        if (strlen($host) > 2 && str_starts_with($host, '[') && str_ends_with($host, ']')) {
            return filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        }
        //If removing all the dots results in a numeric string, it must be an IPv4 address.
        //Need to check this first because otherwise things like `999.0.0.0` are considered valid host names
        if (is_numeric(str_replace('.', '', $host))) {
            //Is it a valid IPv4 address?
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
        }
        if (filter_var('http://' . $host, FILTER_VALIDATE_URL) !== false) {
            //Is it a syntactically valid hostname?
            return true;
        }
        return false;
    }
    /**
     * Get an error message in the current language.
     *
     * @param string $key
     *
     * @return string
     */
    protected function lang($key)
    {
        if (count($this->language) < 1) {
            $this->set_language();
            //Set the default language
        }
        if (array_key_exists($key, $this->language)) {
            if ('smtp_connect_failed' === $key) {
                //Include a link to troubleshooting docs on SMTP connection failure.
                //This is by far the biggest cause of support questions
                //but it's usually not PHPMailer's fault.
                return $this->language[$key] . ' https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting';
            }
            return $this->language[$key];
        }
        //Return the key as a fallback
        return $key;
    }
    /**
     * Check if an error occurred.
     *
     * @return bool True if an error did occur
     */
    public function is_error(): bool
    {
        return $this->error_count > 0;
    }
    /**
     * Add a custom header.
     * $name value can be overloaded to contain
     * both header name and value (name:value).
     *
     * @param string      $name  Custom header name
     * @param string|null $value Header value
     *
     * @throws Exception
     */
    public function add_custom_header($name, $value = null): bool
    {
        if (null === $value && str_contains($name, ':')) {
            //Value passed in as name:value
            [$name, $value] = explode(':', $name, 2);
        }
        $name = trim($name);
        $value = trim((string) $value);
        //Ensure name is not empty, and that neither name nor value contain line breaks
        if (empty($name) || strpbrk($name . $value, "\r\n") !== false) {
            if ($this->exceptions) {
                throw new Exception('Invalid header name or value');
            }
            return false;
        }
        $this->custom_header[] = [$name, $value];
        return true;
    }
    /**
     * Returns all custom headers.
     *
     * @return array
     */
    public function get_custom_headers()
    {
        return $this->custom_header;
    }
    /**
     * Create a message body from an HTML string.
     * Automatically inlines images and creates a plain-text version by converting the HTML,
     * overwriting any existing values in Body and AltBody.
     * Do not source $message content from user input!
     * $basedir is prepended when handling relative URLs, e.g. <img src="/images/a.png"> and must not be empty
     * will look for an image file in $basedir/images/a.png and convert it to inline.
     * If you don't provide a $basedir, relative paths will be left untouched (and thus probably break in email)
     * Converts data-uri images into embedded attachments.
     * If you don't want to apply these transformations to your HTML, just set Body and AltBody directly.
     *
     * @param string        $message  HTML message string
     * @param string        $basedir  Absolute path to a base directory to prepend to relative paths to images
     * @param bool|callable $advanced Whether to use the internal HTML to text converter
     *                                or your own custom converter
     * @return string The transformed message body
     *
     * @throws Exception
     *
     * @see PHPMailer::html2text()
     */
    public function msg_html($message, string $basedir = '', $advanced = false)
    {
        preg_match_all('/(?<!-)(src|background)=["\'](.*)["\']/Ui', $message, $images);
        if (array_key_exists(2, $images)) {
            if (strlen($basedir) > 1 && !str_ends_with($basedir, '/')) {
                //Ensure $basedir has a trailing /
                $basedir .= '/';
            }
            foreach ($images[2] as $imgindex => $url) {
                //Convert data URIs into embedded images
                //e.g. "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                $match = [];
                if (preg_match('#^data:(image/(?:jpe?g|gif|png));?(base64)?,(.+)#', $url, $match)) {
                    if (static::ENCODING_BASE64 === $match[2]) {
                        $data = base64_decode($match[3]);
                    } elseif ('' === $match[2]) {
                        $data = rawurldecode($match[3]);
                    } else {
                        //Not recognised so leave it alone
                        continue;
                    }
                    //Hash the decoded data, not the URL, so that the same data-URI image used in multiple places
                    //will only be embedded once, even if it used a different encoding
                    $cid = substr(hash('sha256', $data), 0, 32) . '@phpmailer.0';
                    //RFC2392 S 2
                    if (!$this->cid_exists($cid)) {
                        $this->add_string_embedded_image($data, $cid, 'embed' . $imgindex, static::ENCODING_BASE64, $match[1]);
                    }
                    $message = str_replace($images[0][$imgindex], $images[1][$imgindex] . '="cid:' . $cid . '"', $message);
                    continue;
                }
                if (!empty($basedir) && !str_contains($url, '..') && !str_starts_with($url, 'cid:') && !preg_match('#^[a-z][a-z0-9+.-]*:?//#i', $url)) {
                    $filename = static::mb_pathinfo($url, PATHINFO_BASENAME);
                    $directory = dirname($url);
                    if ('.' === $directory) {
                        $directory = '';
                    }
                    //RFC2392 S 2
                    $cid = substr(hash('sha256', $url), 0, 32) . '@phpmailer.0';
                    if (strlen($basedir) > 1 && !str_ends_with($basedir, '/')) {
                        $basedir .= '/';
                    }
                    if (strlen($directory) > 1 && !str_ends_with($directory, '/')) {
                        $directory .= '/';
                    }
                    if ($this->add_embedded_image($basedir . $directory . $filename, $cid, $filename, static::ENCODING_BASE64, static::_mime_types((string) static::mb_pathinfo($filename, PATHINFO_EXTENSION)))) {
                        $message = preg_replace('/' . $images[1][$imgindex] . '=["\']' . preg_quote($url, '/') . '["\']/Ui', $images[1][$imgindex] . '="cid:' . $cid . '"', (string) $message);
                    }
                }
            }
        }
        $this->is_html();
        //Convert all message body line breaks to LE, makes quoted-printable encoding work much better
        $this->Body = static::normalize_breaks($message);
        $this->alt_body = static::normalize_breaks($this->html2text($message, $advanced));
        if (!$this->alternative_exists()) {
            $this->alt_body = 'This is an HTML-only message. To view it, activate HTML in your email application.' . static::$LE;
        }
        return $this->Body;
    }
    /**
     * Convert an HTML string into plain text.
     * This is used by msgHTML().
     * Note - older versions of this function used a bundled advanced converter
     * which was removed for license reasons in #232.
     * Example usage:
     *
     * ```php
     * //Use default conversion
     * $plain = $mail->html2text($html);
     * //Use your own custom converter
     * $plain = $mail->html2text($html, function($html) {
     *     $converter = new MyHtml2text($html);
     *     return $converter->get_text();
     * });
     * ```
     *
     * @param string        $html     The HTML text to convert
     * @param bool|callable $advanced Any boolean value to use the internal converter,
     *                                or provide your own callable for custom conversion
     *
     * @return string
     */
    public function html2text($html, $advanced = false)
    {
        if (is_callable($advanced)) {
            return call_user_func($advanced, $html);
        }
        return html_entity_decode(trim(strip_tags((string) preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\1>/si', '', $html))), ENT_QUOTES, $this->char_set);
    }
    /**
     * Get the MIME type for a file extension.
     *
     * @param string $ext File extension
     *
     * @return string MIME type of file
     */
    public static function _mime_types($ext = ''): string
    {
        $mimes = ['xl' => 'application/excel', 'js' => 'application/javascript', 'hqx' => 'application/mac-binhex40', 'cpt' => 'application/mac-compactpro', 'bin' => 'application/macbinary', 'doc' => 'application/msword', 'word' => 'application/msword', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template', 'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template', 'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12', 'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12', 'class' => 'application/octet-stream', 'dll' => 'application/octet-stream', 'dms' => 'application/octet-stream', 'exe' => 'application/octet-stream', 'lha' => 'application/octet-stream', 'lzh' => 'application/octet-stream', 'psd' => 'application/octet-stream', 'sea' => 'application/octet-stream', 'so' => 'application/octet-stream', 'oda' => 'application/oda', 'pdf' => 'application/pdf', 'ai' => 'application/postscript', 'eps' => 'application/postscript', 'ps' => 'application/postscript', 'smi' => 'application/smil', 'smil' => 'application/smil', 'mif' => 'application/vnd.mif', 'xls' => 'application/vnd.ms-excel', 'ppt' => 'application/vnd.ms-powerpoint', 'wbxml' => 'application/vnd.wap.wbxml', 'wmlc' => 'application/vnd.wap.wmlc', 'dcr' => 'application/x-director', 'dir' => 'application/x-director', 'dxr' => 'application/x-director', 'dvi' => 'application/x-dvi', 'gtar' => 'application/x-gtar', 'php3' => 'application/x-httpd-php', 'php4' => 'application/x-httpd-php', 'php' => 'application/x-httpd-php', 'phtml' => 'application/x-httpd-php', 'phps' => 'application/x-httpd-php-source', 'swf' => 'application/x-shockwave-flash', 'sit' => 'application/x-stuffit', 'tar' => 'application/x-tar', 'tgz' => 'application/x-tar', 'xht' => 'application/xhtml+xml', 'xhtml' => 'application/xhtml+xml', 'zip' => 'application/zip', 'mid' => 'audio/midi', 'midi' => 'audio/midi', 'mp2' => 'audio/mpeg', 'mp3' => 'audio/mpeg', 'm4a' => 'audio/mp4', 'mpga' => 'audio/mpeg', 'aif' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff', 'aiff' => 'audio/x-aiff', 'ram' => 'audio/x-pn-realaudio', 'rm' => 'audio/x-pn-realaudio', 'rpm' => 'audio/x-pn-realaudio-plugin', 'ra' => 'audio/x-realaudio', 'wav' => 'audio/x-wav', 'mka' => 'audio/x-matroska', 'bmp' => 'image/bmp', 'gif' => 'image/gif', 'jpeg' => 'image/jpeg', 'jpe' => 'image/jpeg', 'jpg' => 'image/jpeg', 'png' => 'image/png', 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'webp' => 'image/webp', 'avif' => 'image/avif', 'heif' => 'image/heif', 'heifs' => 'image/heif-sequence', 'heic' => 'image/heic', 'heics' => 'image/heic-sequence', 'eml' => 'message/rfc822', 'css' => 'text/css', 'html' => 'text/html', 'htm' => 'text/html', 'shtml' => 'text/html', 'log' => 'text/plain', 'text' => 'text/plain', 'txt' => 'text/plain', 'rtx' => 'text/richtext', 'rtf' => 'text/rtf', 'vcf' => 'text/vcard', 'vcard' => 'text/vcard', 'ics' => 'text/calendar', 'xml' => 'text/xml', 'xsl' => 'text/xml', 'wmv' => 'video/x-ms-wmv', 'mpeg' => 'video/mpeg', 'mpe' => 'video/mpeg', 'mpg' => 'video/mpeg', 'mp4' => 'video/mp4', 'm4v' => 'video/mp4', 'mov' => 'video/quicktime', 'qt' => 'video/quicktime', 'rv' => 'video/vnd.rn-realvideo', 'avi' => 'video/x-msvideo', 'movie' => 'video/x-sgi-movie', 'webm' => 'video/webm', 'mkv' => 'video/x-matroska'];
        $ext = strtolower($ext);
        if (array_key_exists($ext, $mimes)) {
            return $mimes[$ext];
        }
        return 'application/octet-stream';
    }
    /**
     * Map a file name to a MIME type.
     * Defaults to 'application/octet-stream', i.e.. arbitrary binary data.
     *
     * @param string $filename A file name or full path, does not need to exist as a file
     *
     * @return string
     */
    public static function filename_to_type($filename)
    {
        //In case the path is a URL, strip any query string before getting extension
        $qpos = strpos($filename, '?');
        if (false !== $qpos) {
            $filename = substr($filename, 0, $qpos);
        }
        $ext = static::mb_pathinfo($filename, PATHINFO_EXTENSION);
        return static::_mime_types($ext);
    }
    /**
     * Multi-byte-safe pathinfo replacement.
     * Drop-in replacement for pathinfo(), but multibyte- and cross-platform-safe.
     *
     * @see http://www.php.net/manual/en/function.pathinfo.php#107461
     *
     * @param string     $path    A filename or path, does not need to exist as a file
     * @param int|string $options Either a PATHINFO_* constant,
     *                            or a string name to return only the specified piece
     */
    public static function mb_pathinfo($path, $options = null): string|array
    {
        $ret = ['dirname' => '', 'basename' => '', 'extension' => '', 'filename' => ''];
        $pathinfo = [];
        if (preg_match('#^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^.\\\\/]+?)|))[\\\\/.]*$#m', $path, $pathinfo)) {
            if (array_key_exists(1, $pathinfo)) {
                $ret['dirname'] = $pathinfo[1];
            }
            if (array_key_exists(2, $pathinfo)) {
                $ret['basename'] = $pathinfo[2];
            }
            if (array_key_exists(5, $pathinfo)) {
                $ret['extension'] = $pathinfo[5];
            }
            if (array_key_exists(3, $pathinfo)) {
                $ret['filename'] = $pathinfo[3];
            }
        }
        switch ($options) {
            case PATHINFO_DIRNAME:
            case 'dirname':
                return $ret['dirname'];
            case PATHINFO_BASENAME:
            case 'basename':
                return $ret['basename'];
            case PATHINFO_EXTENSION:
            case 'extension':
                return $ret['extension'];
            case PATHINFO_FILENAME:
            case 'filename':
                return $ret['filename'];
            default:
                return $ret;
        }
    }
    /**
     * Set or reset instance properties.
     * You should avoid this function - it's more verbose, less efficient, more error-prone and
     * harder to debug than setting properties directly.
     * Usage Example:
     * `$mail->set('SMTPSecure', static::ENCRYPTION_STARTTLS);`
     *   is the same as:
     * `$mail->SMTPSecure = static::ENCRYPTION_STARTTLS;`.
     *
     * @param string $name  The property name to set
     * @param mixed  $value The value to set the property to
     */
    public function set(string $name, $value = ''): bool
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
            return true;
        }
        $this->set_error($this->lang('variable_set') . $name);
        return false;
    }
    /**
     * Strip newlines to prevent header injection.
     *
     * @param string $str
     */
    public function secure_header($str): string
    {
        return trim(str_replace(["\r", "\n"], '', $str));
    }
    /**
     * Normalize line breaks in a string.
     * Converts UNIX LF, Mac CR and Windows CRLF line breaks into a single line break format.
     * Defaults to CRLF (for message bodies) and preserves consecutive breaks.
     *
     * @param string $text
     * @param string $breaktype What kind of line break to use; defaults to static::$LE
     *
     * @return string
     */
    public static function normalize_breaks($text, $breaktype = null): string|array
    {
        if (null === $breaktype) {
            $breaktype = static::$LE;
        }
        //Normalise to \n
        $text = str_replace([self::CRLF, "\r"], "\n", $text);
        //Now convert LE as needed
        if ("\n" !== $breaktype) {
            return str_replace("\n", $breaktype, $text);
        }
        return $text;
    }
    /**
     * Remove trailing breaks from a string.
     *
     * @param string $text
     *
     * @return string The text to remove breaks from
     */
    public static function strip_trailing_wsp($text): string
    {
        return rtrim($text, " \r\n\t");
    }
    /**
     * Return the current line break format string.
     *
     * @return string
     */
    public static function get_le()
    {
        return static::$LE;
    }
    /**
     * Set the line break format string, e.g. "\r\n".
     *
     * @param string $le
     */
    protected static function set_le($le)
    {
        static::$LE = $le;
    }
    /**
     * Set the public and private key files and password for S/MIME signing.
     *
     * @param string $cert_filename
     * @param string $key_filename
     * @param string $key_pass            Password for private key
     * @param string $extracerts_filename Optional path to chain certificate
     */
    public function sign($cert_filename, $key_filename, $key_pass, $extracerts_filename = ''): void
    {
        $this->sign_cert_file = $cert_filename;
        $this->sign_key_file = $key_filename;
        $this->sign_key_pass = $key_pass;
        $this->sign_extracerts_file = $extracerts_filename;
    }
    /**
     * Quoted-Printable-encode a DKIM header.
     *
     * @param string $txt
     */
    public function DKIM_QP($txt): string
    {
        $line = '';
        $len = strlen($txt);
        for ($i = 0; $i < $len; ++$i) {
            $ord = ord($txt[$i]);
            if (0x21 <= $ord && $ord <= 0x3a || $ord === 0x3c || 0x3e <= $ord && $ord <= 0x7e) {
                $line .= $txt[$i];
            } else {
                $line .= '=' . sprintf('%02X', $ord);
            }
        }
        return $line;
    }
    /**
     * Generate a DKIM signature.
     *
     * @param string $signHeader
     *
     * @throws Exception
     *
     * @return string The DKIM signature value
     */
    public function DKIM_Sign($sign_header): string
    {
        if (!defined('PKCS7_TEXT')) {
            if ($this->exceptions) {
                throw new Exception($this->lang('extension_missing') . 'openssl');
            }
            return '';
        }
        $priv_key_str = !empty($this->DKIM_private_string) ? $this->DKIM_private_string : file_get_contents($this->DKIM_private);
        if ('' !== $this->DKIM_passphrase) {
            $priv_key = openssl_pkey_get_private($priv_key_str, $this->DKIM_passphrase);
        } else {
            $priv_key = openssl_pkey_get_private($priv_key_str);
        }
        if (openssl_sign($sign_header, $signature, $priv_key, 'sha256WithRSAEncryption')) {
            if (\PHP_MAJOR_VERSION < 8) {
                openssl_pkey_free($priv_key);
            }
            return base64_encode((string) $signature);
        }
        if (\PHP_MAJOR_VERSION < 8) {
            openssl_pkey_free($priv_key);
        }
        return '';
    }
    /**
     * Generate a DKIM canonicalization header.
     * Uses the 'relaxed' algorithm from RFC6376 section 3.4.2.
     * Canonicalized headers should *always* use CRLF, regardless of mailer setting.
     *
     * @see https://tools.ietf.org/html/rfc6376#section-3.4.2
     *
     * @param string $signHeader Header
     */
    public function dkim_header_c($sign_header): string
    {
        //Normalize breaks to CRLF (regardless of the mailer)
        $sign_header = static::normalize_breaks($sign_header, self::CRLF);
        //Unfold header lines
        //Note PCRE \s is too broad a definition of whitespace; RFC5322 defines it as `[ \t]`
        //@see https://tools.ietf.org/html/rfc5322#section-2.2
        //That means this may break if you do something daft like put vertical tabs in your headers.
        $sign_header = preg_replace('/\r\n[ \t]+/', ' ', $sign_header);
        //Break headers out into an array
        $lines = explode(self::CRLF, (string) $sign_header);
        foreach ($lines as $key => $line) {
            //If the header is missing a :, skip it as it's invalid
            //This is likely to happen because the explode() above will also split
            //on the trailing LE, leaving an empty line
            if (!str_contains($line, ':')) {
                continue;
            }
            [$heading, $value] = explode(':', $line, 2);
            //Lower-case header name
            $heading = strtolower($heading);
            //Collapse white space within the value, also convert WSP to space
            $value = preg_replace('/[ \t]+/', ' ', $value);
            //RFC6376 is slightly unclear here - it says to delete space at the *end* of each value
            //But then says to delete space before and after the colon.
            //Net result is the same as trimming both ends of the value.
            //By elimination, the same applies to the field name
            $lines[$key] = trim($heading, " \t") . ':' . trim((string) $value, " \t");
        }
        return implode(self::CRLF, $lines);
    }
    /**
     * Generate a DKIM canonicalization body.
     * Uses the 'simple' algorithm from RFC6376 section 3.4.3.
     * Canonicalized bodies should *always* use CRLF, regardless of mailer setting.
     *
     * @see https://tools.ietf.org/html/rfc6376#section-3.4.3
     *
     * @param string $body Message Body
     */
    public function dkim_body_c($body): string
    {
        if (empty($body)) {
            return self::CRLF;
        }
        //Normalize line endings to CRLF
        $body = static::normalize_breaks($body, self::CRLF);
        //Reduce multiple trailing line breaks to a single one
        return static::strip_trailing_wsp($body) . self::CRLF;
    }
    /**
     * Create the DKIM header and body in a new message header.
     *
     * @param string $headers_line Header lines
     * @param string $subject      Subject
     * @param string $body         Body
     *
     * @throws Exception
     *
     * @return string
     */
    public function DKIM_Add(string $headers_line, string $subject, $body)
    {
        $dki_msignature_type = 'rsa-sha256';
        //Signature & hash algorithms
        $dki_mcanonicalization = 'relaxed/simple';
        //Canonicalization methods of header & body
        $dki_mquery = 'dns/txt';
        //Query method
        $dki_mtime = time();
        //Always sign these headers without being asked
        //Recommended list from https://tools.ietf.org/html/rfc6376#section-5.4.1
        $auto_sign_headers = ['from', 'to', 'cc', 'date', 'subject', 'reply-to', 'message-id', 'content-type', 'mime-version', 'x-mailer'];
        if (stripos($headers_line, 'Subject') === false) {
            $headers_line .= 'Subject: ' . $subject . static::$LE;
        }
        $header_lines = explode(static::$LE, $headers_line);
        $current_header_label = '';
        $current_header_value = '';
        $parsed_headers = [];
        $header_line_index = 0;
        $header_line_count = count($header_lines);
        foreach ($header_lines as $header_line) {
            $matches = [];
            if (preg_match('/^([^ \t]*?)(?::[ \t]*)(.*)$/', $header_line, $matches)) {
                if ($current_header_label !== '') {
                    //We were previously in another header; This is the start of a new header, so save the previous one
                    $parsed_headers[] = ['label' => $current_header_label, 'value' => $current_header_value];
                }
                $current_header_label = $matches[1];
                $current_header_value = $matches[2];
            } elseif (preg_match('/^[ \t]+(.*)$/', $header_line, $matches)) {
                //This is a folded continuation of the current header, so unfold it
                $current_header_value .= ' ' . $matches[1];
            }
            ++$header_line_index;
            if ($header_line_index >= $header_line_count) {
                //This was the last line, so finish off this header
                $parsed_headers[] = ['label' => $current_header_label, 'value' => $current_header_value];
            }
        }
        $copied_headers = [];
        $headers_to_sign_keys = [];
        $headers_to_sign = [];
        foreach ($parsed_headers as $header) {
            //Is this header one that must be included in the DKIM signature?
            if (in_array(strtolower($header['label']), $auto_sign_headers, true)) {
                $headers_to_sign_keys[] = $header['label'];
                $headers_to_sign[] = $header['label'] . ': ' . $header['value'];
                if ($this->dkim_copy_header_fields) {
                    $copied_headers[] = $header['label'] . ':' . str_replace('|', '=7C', $this->DKIM_QP($header['value']));
                }
                continue;
            }
            //Is this an extra custom header we've been asked to sign?
            if (in_array($header['label'], $this->dkim_extra_headers, true)) {
                //Find its value in custom headers
                foreach ($this->custom_header as $custom_header) {
                    if ($custom_header[0] === $header['label']) {
                        $headers_to_sign_keys[] = $header['label'];
                        $headers_to_sign[] = $header['label'] . ': ' . $header['value'];
                        if ($this->dkim_copy_header_fields) {
                            $copied_headers[] = $header['label'] . ':' . str_replace('|', '=7C', $this->DKIM_QP($header['value']));
                        }
                        //Skip straight to the next header
                        continue 2;
                    }
                }
            }
        }
        $copied_header_fields = '';
        if ($this->dkim_copy_header_fields && count($copied_headers) > 0) {
            //Assemble a DKIM 'z' tag
            $copied_header_fields = ' z=';
            $first = true;
            foreach ($copied_headers as $copied_header) {
                if (!$first) {
                    $copied_header_fields .= static::$LE . ' |';
                }
                //Fold long values
                if (strlen($copied_header) > self::STD_LINE_LENGTH - 3) {
                    $copied_header_fields .= substr(chunk_split($copied_header, self::STD_LINE_LENGTH - 3, static::$LE . self::FWS), 0, -strlen(static::$LE . self::FWS));
                } else {
                    $copied_header_fields .= $copied_header;
                }
                $first = false;
            }
            $copied_header_fields .= ';' . static::$LE;
        }
        $header_keys = ' h=' . implode(':', $headers_to_sign_keys) . ';' . static::$LE;
        $header_values = implode(static::$LE, $headers_to_sign);
        $body = $this->dkim_body_c($body);
        //Base64 of packed binary SHA-256 hash of body
        $dki_mb64 = base64_encode(pack('H*', hash('sha256', $body)));
        $ident = '';
        if ('' !== $this->DKIM_identity) {
            $ident = ' i=' . $this->DKIM_identity . ';' . static::$LE;
        }
        //The DKIM-Signature header is included in the signature *except for* the value of the `b` tag
        //which is appended after calculating the signature
        //https://tools.ietf.org/html/rfc6376#section-3.5
        $dkim_signature_header = 'DKIM-Signature: v=1;' . ' d=' . $this->DKIM_domain . ';' . ' s=' . $this->DKIM_selector . ';' . static::$LE . ' a=' . $dki_msignature_type . ';' . ' q=' . $dki_mquery . ';' . ' t=' . $dki_mtime . ';' . ' c=' . $dki_mcanonicalization . ';' . static::$LE . $header_keys . $ident . $copied_header_fields . ' bh=' . $dki_mb64 . ';' . static::$LE . ' b=';
        //Canonicalize the set of headers
        $canonicalized_headers = $this->dkim_header_c($header_values . static::$LE . $dkim_signature_header);
        $signature = $this->DKIM_Sign($canonicalized_headers);
        $signature = trim(chunk_split($signature, self::STD_LINE_LENGTH - 3, static::$LE . self::FWS));
        return static::normalize_breaks($dkim_signature_header . $signature);
    }
    /**
     * Detect if a string contains a line longer than the maximum line length
     * allowed by RFC 2822 section 2.1.1.
     *
     * @param string $str
     */
    public static function has_line_longer_than_max($str): bool
    {
        return (bool) preg_match('/^(.{' . (self::MAX_LINE_LENGTH + strlen(static::$LE)) . ',})/m', $str);
    }
    /**
     * If a string contains any "special" characters, double-quote the name,
     * and escape any double quotes with a backslash.
     *
     * @param string $str
     *
     * @return string
     *
     * @see RFC822 3.4.1
     */
    public static function quoted_string($str)
    {
        if (preg_match('/[ ()<>@,;:"\/\[\]?=]/', $str)) {
            //If the string contains any of these chars, it must be double-quoted
            //and any double quotes must be escaped with a backslash
            return '"' . str_replace('"', '\"', $str) . '"';
        }
        //Return the string untouched, it doesn't need quoting
        return $str;
    }
    /**
     * Allows for public read access to 'to' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public function get_to_addresses()
    {
        return $this->to;
    }
    /**
     * Allows for public read access to 'cc' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public function get_cc_addresses()
    {
        return $this->cc;
    }
    /**
     * Allows for public read access to 'bcc' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public function get_bcc_addresses()
    {
        return $this->bcc;
    }
    /**
     * Allows for public read access to 'ReplyTo' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public function get_reply_to_addresses()
    {
        return $this->reply_to;
    }
    /**
     * Allows for public read access to 'all_recipients' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public function get_all_recipient_addresses()
    {
        return $this->all_recipients;
    }
    /**
     * Perform a callback.
     *
     * @param bool   $isSent
     * @param array  $to
     * @param array  $cc
     * @param array  $bcc
     * @param string $subject
     * @param string $body
     * @param string $from
     * @param array  $extra
     */
    protected function do_callback($is_sent, $to, $cc, $bcc, $subject, $body, $from, $extra)
    {
        if (!empty($this->action_function) && is_callable($this->action_function)) {
            call_user_func($this->action_function, $is_sent, $to, $cc, $bcc, $subject, $body, $from, $extra);
        }
    }
    /**
     * Get the OAuth instance.
     *
     * @return OAuth
     */
    public function get_o_auth()
    {
        return $this->oauth;
    }
    /**
     * Set an OAuth instance.
     */
    public function set_o_auth(O_Auth $oauth): void
    {
        $this->oauth = $oauth;
    }
}