<?php

declare (strict_types=1);
/**
 * This helper builds the request body for a /mail/send API call
 */
namespace Send_Grid\Mail;

use InvalidArgumentException;
use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a request body for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Mail implements \JsonSerializable
{
    /** @var $from From Email address of the sender */
    private ?\Send_Grid\Mail\From $from = null;
    /** @var $subject Subject Subject of the email */
    private ?\Send_Grid\Mail\Subject $subject = null;
    /** @var $contents Content[] Content(s) of the email */
    private ?array $contents = null;
    /** @var $attachments Attachment[] Email attachments */
    private ?array $attachments = null;
    /** @var $template_id TemplateId Id of a template that you would like to use */
    private ?\Send_Grid\Mail\Template_Id $template_id = null;
    /** @var $sections Section[] Key/value pairs that define block sections of code to be used as substitutions */
    private ?array $sections = null;
    /** @var $headers Header[] Header names and the value to substitute for them */
    private ?array $headers = null;
    /** @var $categories Category[] Category names for this message */
    private ?array $categories = null;
    /**
     * @var $custom_args CustomArg[] Values that are specific to the entire send that will be carried along with the
     *                               email and its activity data
     */
    private ?array $custom_args = null;
    /**
     * @var $substitutions Substitution[] Substitutions that will apply to the text and html content of the body of your
     *                                    email, in addition to the subject and reply-to parameters
     */
    private $substitutions;
    /** @var $send_at SendAt A unix timestamp allowing you to specify when you want your email to be delivered */
    private ?\Send_Grid\Mail\Send_At $send_at = null;
    /** @var $batch_id BatchId This ID represents a batch of emails to be sent at the same time */
    private ?\Send_Grid\Mail\Batch_Id $batch_id = null;
    /** @var $asm ASM Specifies how to handle unsubscribes */
    private ?\Send_Grid\Mail\Asm $asm = null;
    /** @var $ip_pool_name IpPoolName The IP Pool that you would like to send this email from */
    private $ip_pool_name;
    /**
     * @var $mail_settings MailSettings A collection of different mail settings that you can use to specify how you
     *                                  would like this email to be handled
     */
    private $mail_settings;
    /**
     * @var $tracking_settings TrackingSettings Settings to determine how you would like to track the metrics of how
     *                                          your recipients interact with your email
     */
    private $tracking_settings;
    /** @var $reply_to ReplyTo Email to be use when replied to */
    private ?\Send_Grid\Mail\Reply_To $reply_to = null;
    /** @var $personalization Personalization[] Messages and their metadata */
    private $personalization;
    /**
     * If passing parameters into this constructor, include $from, $to, $subject,
     * $plainTextContent, $htmlContent and $globalSubstitutions at a minimum.
     * If you don't supply any, a Personalization object will be created for you.
     *
     * @param From|null                 $from                Email address of the sender
     * @param To|To[]|null              $to                  Recipient(s) email address(es)
     * @param Subject|Subject[]|null    $subject             Subject(s)
     * @param PlainTextContent|null     $plainTextContent    Plain text version of content
     * @param HtmlContent|null          $htmlContent         Html version of content
     * @param Substitution[]|array|null $globalSubstitutions Substitutions for entire email
     *
     * @throws TypeException
     */
    public function __construct($from = null, $to = null, $subject = null, $plain_text_content = null, $html_content = null, array $global_substitutions = null)
    {
        if (!isset($from) && !isset($to) && !isset($subject) && !isset($plain_text_content) && !isset($html_content) && !isset($global_substitutions)) {
            $this->personalization[] = new Personalization();
            return;
        }
        if (isset($from)) {
            $this->set_from($from);
        }
        if (isset($to)) {
            if (!\is_array($to)) {
                $to = [$to];
            }
            $subject_count = 0;
            foreach ($to as $email) {
                if (\is_array($subject) || $email->is_personalized()) {
                    $personalization = new Personalization();
                    $this->add_to($email, null, null, null, $personalization);
                } else {
                    $this->add_to($email);
                    $personalization = \end($this->personalization);
                }
                if (\is_array($subject) && $subject_count < \count($subject)) {
                    $personalization->set_subject($subject[$subject_count]);
                    $subject_count++;
                }
                if (\is_array($global_substitutions)) {
                    foreach ($global_substitutions as $key => $value) {
                        if ($value instanceof Substitution) {
                            $personalization->add_substitution($value);
                        } else {
                            $personalization->add_substitution($key, $value);
                        }
                    }
                }
            }
        }
        if (isset($subject) && !\is_array($subject)) {
            $this->set_subject($subject);
        }
        if (isset($plain_text_content)) {
            Assert::is_instance_of($plain_text_content, 'plainTextContent', Content::class);
            $this->add_content($plain_text_content);
        }
        if (isset($html_content)) {
            Assert::is_instance_of($html_content, 'htmlContent', Content::class);
            $this->add_content($html_content);
        }
    }
    /**
     * Adds a To, Cc or Bcc object to a Personalization object
     *
     * @param string                    $emailType            Object type name: To, Cc or Bcc
     * @param string                    $email                Recipient email address
     * @param string|null               $name                 Recipient name
     * @param Substitution[]|array|null $substitutions        Personalized
     *                                                        substitutions
     * @param int|null                  $personalizationIndex Index into the array of existing
     *                                                        Personalization objects
     * @param Personalization|null      $personalization      A pre-created
     *                                                        Personalization object
     *
     * @throws TypeException
     */
    private function add_recipient_email(string $email_type, $email, $name = null, $substitutions = null, $personalization_index = null, $personalization = null): void
    {
        $personalization_function_call = 'add' . $email_type;
        $email_type_class = '\SendGrid\Mail\\' . $email_type;
        if (!$email instanceof $email_type_class) {
            $email = new $email_type_class($email, $name, $substitutions);
        }
        if ($personalization_index === null && $personalization === null && $email_type === 'To' && $email->is_personalized()) {
            $personalization = new Personalization();
        }
        $personalization = $this->get_personalization($personalization_index, $personalization);
        $personalization->{$personalization_function_call}($email);
        if ($subs = $email->get_substitutions()) {
            foreach ($subs as $key => $value) {
                $personalization->add_substitution($key, $value);
            }
        }
        if ($email->get_subject()) {
            $personalization->set_subject($email->get_subject());
        }
    }
    /**
     * Adds an array of To, Cc or Bcc objects to a Personalization object
     *
     * @param string               $emailType            Object type name: To, Cc  or Bcc
     * @param To[]|Cc[]|Bcc[]      $emails               Array of email recipients
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     *
     * @throws TypeException
     */
    private function add_recipient_emails(string $email_type, $emails, $personalization_index = null, $personalization = null): void
    {
        $email_function_call = 'add' . $email_type;
        if (\current($emails) instanceof Email_Address) {
            foreach ($emails as $email) {
                $this->{$email_function_call}($email, $name = null, $substitutions = null, $personalization_index, $personalization);
            }
        } else {
            foreach ($emails as $email => $name) {
                $this->{$email_function_call}($email, $name, $substitutions = null, $personalization_index, $personalization);
            }
        }
    }
    /**
     * Add a Personalization object to the Mail object
     *
     * @param Personalization $personalization A Personalization object
     *
     * @throws TypeException
     */
    public function add_personalization($personalization): void
    {
        Assert::is_instance_of($personalization, 'personalization', Personalization::class);
        $this->personalization[] = $personalization;
    }
    /**
     * Retrieves a Personalization object, adds a pre-created Personalization
     * object, or creates and adds a Personalization object.
     *
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     *
     * @return Personalization
     *
     * @throws TypeException
     */
    public function get_personalization($personalization_index = null, $personalization = null)
    {
        /**
         * Approach:
         * - Append if provided personalization + return
         * - Return last added if not provided personalizationIndex (create on empty)
         * - Return existing personalizationIndex
         * - InvalidArgumentException on unexpected personalizationIndex ( > count)
         * - Create + add Personalization and return
         */
        //  If given a Personalization instance
        if (null !== $personalization) {
            //  Just append it onto Mail and return it
            $this->add_personalization($personalization);
            return $personalization;
        }
        //  Retrieve count of existing Personalization instances
        $personalization_count = $this->get_personalization_count();
        //  Not providing a personalizationIndex?
        if (null === $personalization_index) {
            //  Create new Personalization instance depending on current count
            if (0 === $personalization_count) {
                $this->add_personalization(new Personalization());
            }
            //  Return last added Personalization instance
            return end($this->personalization);
        }
        //  Existing personalizationIndex in personalization?
        if (isset($this->personalization[$personalization_index])) {
            //  Return referred personalization
            return $this->personalization[$personalization_index];
        }
        //  Non-existent personalizationIndex given
        //  Only allow creation of next Personalization if given
        //  personalizationIndex equals personalizationCount
        if ($personalization_index < 0 || $personalization_index > $personalization_count) {
            throw new InvalidArgumentException('personalizationIndex ' . $personalization_index . ' must be less than ' . $personalization_count);
        }
        //  Create new Personalization and return it
        $personalization = new Personalization();
        $this->add_personalization($personalization);
        return $personalization;
    }
    /**
     * Retrieves Personalization object collection from the Mail object.
     *
     * @return Personalization[]|null
     */
    public function get_personalizations()
    {
        return $this->personalization;
    }
    /**
     * Retrieve the number of Personalization objects associated with the Mail object
     */
    public function get_personalization_count(): int
    {
        return isset($this->personalization) ? \count($this->personalization) : 0;
    }
    /**
     * Adds an email recipient to a Personalization object
     *
     * @param string|To            $to                   Email address or To object
     * @param string               $name                 Recipient name
     * @param array|Substitution[] $substitutions        Personalized substitutions
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     *
     * @throws TypeException
     */
    public function add_to($to, $name = null, $substitutions = null, $personalization_index = null, $personalization = null): void
    {
        $this->add_recipient_email('To', $to, $name, $substitutions, $personalization_index, $personalization);
    }
    /**
     * Adds multiple email recipients to a Personalization object
     *
     * @param To[]|array           $toEmails             Array of To objects or key/value pairs of
     *                                                   email address/recipient names
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     *
     * @throws TypeException
     */
    public function add_tos($to_emails, $personalization_index = null, $personalization = null): void
    {
        Assert::min_items($to_emails, 'toEmails', 1);
        Assert::max_items($to_emails, 'toEmails', 1000);
        $this->add_recipient_emails('To', $to_emails, $personalization_index, $personalization);
    }
    /**
     * Adds an email cc recipient to a Personalization object
     *
     * @param string|Cc                 $cc                   Email address or Cc object
     * @param string                    $name                 Recipient name
     * @param Substitution[]|array|null $substitutions        Personalized
     *                                                        substitutions
     * @param int|null                  $personalizationIndex Index into the array of existing
     *                                                        Personalization objects
     * @param Personalization|null      $personalization      A pre-created
     *                                                        Personalization object
     *
     * @throws TypeException
     */
    public function add_cc($cc, $name = null, $substitutions = null, $personalization_index = null, $personalization = null): void
    {
        $this->add_recipient_email('Cc', $cc, $name, $substitutions, $personalization_index, $personalization);
    }
    /**
     * Adds multiple email cc recipients to a Personalization object
     *
     * @param Cc[]|array           $ccEmails             Array of Cc objects or key/value pairs of
     *                                                   email address/recipient names
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     *
     * @throws TypeException
     */
    public function add_ccs($cc_emails, $personalization_index = null, $personalization = null): void
    {
        Assert::min_items($cc_emails, 'ccEmails', 1);
        Assert::max_items($cc_emails, 'ccEmails', 1000);
        $this->add_recipient_emails('Cc', $cc_emails, $personalization_index, $personalization);
    }
    /**
     * Adds an email bcc recipient to a Personalization object
     *
     * @param string|Bcc                $bcc                  Email address or Bcc object
     * @param string                    $name                 Recipient name
     * @param Substitution[]|array|null $substitutions        Personalized
     *                                                        substitutions
     * @param int|null                  $personalizationIndex Index into the array of existing
     *                                                        Personalization objects
     * @param Personalization|null      $personalization      A pre-created
     *                                                        Personalization object
     *
     * @throws TypeException
     */
    public function add_bcc($bcc, $name = null, $substitutions = null, $personalization_index = null, $personalization = null): void
    {
        $this->add_recipient_email('Bcc', $bcc, $name, $substitutions, $personalization_index, $personalization);
    }
    /**
     * Adds multiple email bcc recipients to a Personalization object
     *
     * @param Bcc[]|array          $bccEmails            Array of Bcc objects or key/value pairs of
     *                                                   email address/recipient names
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     *
     * @throws TypeException
     */
    public function add_bccs($bcc_emails, $personalization_index = null, $personalization = null): void
    {
        Assert::min_items($bcc_emails, 'bccEmails', 1);
        Assert::max_items($bcc_emails, 'bccEmails', 1000);
        $this->add_recipient_emails('Bcc', $bcc_emails, $personalization_index, $personalization);
    }
    /**
     * Add a subject to a Personalization or Mail object
     *
     * If you don't provide a Personalization object or index, the
     * subject will be global to entire message. Note that
     * subjects added to Personalization objects override
     * global subjects.
     *
     * @param string|Subject       $subject              Email subject
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     * @throws TypeException
     */
    public function set_subject($subject, $personalization_index = null, $personalization = null): void
    {
        if (!$subject instanceof Subject) {
            $subject = new Subject($subject);
        }
        if ($personalization !== null) {
            $personalization->set_subject($subject);
            $this->add_personalization($personalization);
            return;
        }
        if ($personalization_index !== null) {
            $this->personalization[$personalization_index]->set_subject($subject);
            return;
        }
        $this->set_global_subject($subject);
    }
    /**
     * Retrieve a subject attached to a Personalization object
     *
     * @param int $personalizationIndex   Index into the array of existing
     *                                    Personalization objects
     * @return Subject
     */
    public function get_subject($personalization_index = 0)
    {
        return $this->personalization[$personalization_index]->get_subject();
    }
    /**
     * Add a header to a Personalization or Mail object
     *
     * If you don't provide a Personalization object or index, the
     * header will be global to entire message. Note that
     * headers added to Personalization objects override
     * global headers.
     *
     * @param string|Header        $key                  Key or Header object
     * @param string|null          $value                Value
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     * @throws TypeException
     */
    public function add_header($key, $value = null, $personalization_index = null, $personalization = null): void
    {
        $header = null;
        if ($key instanceof Header) {
            $h = $key;
            $header = new Header($h->get_key(), $h->get_value());
        } else {
            $header = new Header($key, $value);
        }
        $personalization = $this->get_personalization($personalization_index, $personalization);
        $personalization->add_header($header);
    }
    /**
     * Adds multiple headers to a Personalization or Mail object
     *
     * If you don't provide a Personalization object or index, the
     * header will be global to entire message. Note that
     * headers added to Personalization objects override
     * global headers.
     *
     * @param array|Header[]       $headers              Array of Header objects or key values
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     * @throws TypeException
     */
    public function add_headers($headers, $personalization_index = null, $personalization = null): void
    {
        if (\current($headers) instanceof Header) {
            foreach ($headers as $header) {
                $this->add_header($header);
            }
        } else {
            foreach ($headers as $key => $value) {
                $this->add_header($key, $value, $personalization_index, $personalization);
            }
        }
    }
    /**
     * Retrieve the headers attached to a Personalization object
     *
     * @param int $personalizationIndex   Index into the array of existing
     *                                    Personalization objects
     * @return Header[]
     */
    public function get_headers($personalization_index = 0)
    {
        return $this->personalization[$personalization_index]->get_headers();
    }
    /**
     * Add a Substitution object or key/value to a Personalization object
     *
     * @param Substitution|string  $key                  Substitution object or the key of a
     *                                                   dynamic data
     * @param string|null          $value                Value
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     * @throws TypeException
     */
    public function add_dynamic_template_data($key, $value = null, $personalization_index = null, $personalization = null): void
    {
        $this->add_substitution($key, $value, $personalization_index, $personalization);
    }
    /**
     * Add a Substitution object or key/value to a Personalization object
     *
     * @param array|Substitution[] $datas                Array of Substitution
     *                                                   objects or key/values
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     * @throws TypeException
     */
    public function add_dynamic_template_datas($datas, $personalization_index = null, $personalization = null): void
    {
        $this->add_substitutions($datas, $personalization_index, $personalization);
    }
    /**
     * Retrieve dynamic template data key/value pairs from a Personalization object
     *
     * @param int|0 $personalizationIndex Index into the array of existing
     *                                    Personalization objects
     * @return array
     */
    public function get_dynamic_template_datas($personalization_index = 0)
    {
        return $this->get_substitutions($personalization_index);
    }
    /**
     * Add a substitution to a Personalization or Mail object
     *
     * If you don't provide a Personalization object or index, the
     * substitution will be global to entire message. Note that
     * substitutions added to Personalization objects override
     * global substitutions.
     *
     * @param string|Substitution  $key                  Key or Substitution object
     * @param string|null          $value                Value
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     * @throws TypeException
     */
    public function add_substitution($key, $value = null, $personalization_index = null, $personalization = null): void
    {
        $substitution = null;
        if ($key instanceof Substitution) {
            $s = $key;
            $substitution = new Substitution($s->get_key(), $s->get_value());
        } else {
            $substitution = new Substitution($key, $value);
        }
        $personalization = $this->get_personalization($personalization_index, $personalization);
        $personalization->add_substitution($substitution);
    }
    /**
     * Adds multiple substitutions to a Personalization or Mail object
     *
     * If you don't provide a Personalization object or index, the
     * substitution will be global to entire message. Note that
     * substitutions added to Personalization objects override
     * global headers.
     *
     * @param array|Substitution[] $substitutions        Array of Substitution
     *                                                   objects or key/values
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     * @throws TypeException
     */
    public function add_substitutions($substitutions, $personalization_index = null, $personalization = null): void
    {
        if (\current($substitutions) instanceof Substitution) {
            foreach ($substitutions as $substitution) {
                $this->add_substitution($substitution);
            }
        } else {
            foreach ($substitutions as $key => $value) {
                $this->add_substitution($key, $value, $personalization_index, $personalization);
            }
        }
    }
    /**
     * Retrieve the substitutions attached to a Personalization object
     *
     * @param int|0 $personalizationIndex Index into the array of existing
     *                                    Personalization objects
     * @return Substitution[]
     */
    public function get_substitutions($personalization_index = 0)
    {
        return $this->personalization[$personalization_index]->get_substitutions();
    }
    /**
     * Add a custom arg to a Personalization or Mail object
     *
     * Note that custom args added to Personalization objects
     * override global custom args.
     *
     * @param string|CustomArg     $key                  Key or CustomArg object
     * @param string|null          $value                Value
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     * @throws TypeException
     */
    public function add_custom_arg($key, $value = null, $personalization_index = null, $personalization = null): void
    {
        $custom_arg = null;
        if ($key instanceof Custom_Arg) {
            $ca = $key;
            $custom_arg = new Custom_Arg($ca->get_key(), $ca->get_value());
        } else {
            $custom_arg = new Custom_Arg($key, $value);
        }
        $personalization = $this->get_personalization($personalization_index, $personalization);
        $personalization->add_custom_arg($custom_arg);
    }
    /**
     * Adds multiple custom args to a Personalization or Mail object
     *
     * If you don't provide a Personalization object or index, the
     * custom arg will be global to entire message. Note that
     * custom args added to Personalization objects override
     * global custom args.
     *
     * @param array|CustomArg[]    $custom_args          Array of CustomArg objects or
     *                                                   key/values
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     * @throws TypeException
     */
    public function add_custom_args($custom_args, $personalization_index = null, $personalization = null): void
    {
        if (\current($custom_args) instanceof Custom_Arg) {
            foreach ($custom_args as $custom_arg) {
                $this->add_custom_arg($custom_arg);
            }
        } else {
            foreach ($custom_args as $key => $value) {
                $this->add_custom_arg($key, $value, $personalization_index, $personalization);
            }
        }
    }
    /**
     * Retrieve the custom args attached to a Personalization object
     *
     * @param int|0 $personalizationIndex Index into the array of existing
     *                                    Personalization objects
     * @return CustomArg[]
     */
    public function get_custom_args($personalization_index = 0)
    {
        return $this->personalization[$personalization_index]->get_custom_args();
    }
    /**
     * Add a unix timestamp allowing you to specify when you want your
     * email to be delivered to a Personalization or Mail object
     *
     * If you don't provide a Personalization object or index, the
     * send at timestamp will be global to entire message. Note that
     * timestamps added to Personalization objects override
     * global timestamps.
     *
     * @param int|SendAt           $send_at              A unix timestamp
     * @param int|null             $personalizationIndex Index into the array of existing
     *                                                   Personalization objects
     * @param Personalization|null $personalization      A pre-created
     *                                                   Personalization object
     * @throws TypeException
     */
    public function set_send_at($send_at, $personalization_index = null, $personalization = null): void
    {
        if (!$send_at instanceof Send_At) {
            $send_at = new Send_At($send_at);
        }
        $personalization = $this->get_personalization($personalization_index, $personalization);
        $personalization->set_send_at($send_at);
    }
    /**
     * Retrieve the unix timestamp attached to a Personalization object
     *
     * @param int|0 $personalizationIndex Index into the array of existing
     *                                    Personalization objects
     * @return SendAt|null
     */
    public function get_send_at($personalization_index = 0)
    {
        return $this->personalization[$personalization_index]->get_send_at();
    }
    /**
     * Add the sender email address to a Mail object
     *
     * @param string|From $email Email address or From object
     * @param string|null $name  Sender name
     *
     * @throws TypeException
     */
    public function set_from($email, $name = null): void
    {
        if ($email instanceof From) {
            $this->from = $email;
        } else {
            Assert::email($email, 'email', '"$email" must be an instance of SendGrid\Mail\From or a valid email address');
            $this->from = new From($email, $name);
        }
    }
    /**
     * Retrieve the sender attached to a Mail object
     *
     * @return From
     */
    public function get_from()
    {
        return $this->from;
    }
    /**
     * Add the reply to email address to a Mail object
     *
     * @param string|ReplyTo $email Email address or From object
     * @param string|null    $name  Reply to name
     *
     * @throws TypeException
     */
    public function set_reply_to($email, $name = null): void
    {
        if ($email instanceof Reply_To) {
            $this->reply_to = $email;
        } else {
            $this->reply_to = new Reply_To($email, $name);
        }
    }
    /**
     * Retrieve the reply to information attached to a Mail object
     *
     * @return ReplyTo
     */
    public function get_reply_to()
    {
        return $this->reply_to;
    }
    /**
     * Add a subject to a Mail object
     *
     * Note that
     * subjects added to Personalization objects override
     * global subjects.
     *
     * @param string|Subject $subject Email subject
     *
     * @throws TypeException
     */
    public function set_global_subject($subject): void
    {
        if (!$subject instanceof Subject) {
            $subject = new Subject($subject);
        }
        $this->subject = $subject;
    }
    /**
     * Retrieve a subject attached to a Mail object
     *
     * @return Subject
     */
    public function get_global_subject()
    {
        return $this->subject;
    }
    /**
     * Add content to a Mail object
     *
     * For a list of pre-configured mime types, please see
     * MimeType.php
     *
     * @param string|Content $type  Mime type or Content object
     * @param string|null    $value Contents (e.g. text or html)
     *
     * @throws TypeException
     */
    public function add_content($type, $value = null): void
    {
        if ($type instanceof Content) {
            $content = $type;
        } else {
            $content = new Content($type, $value);
        }
        $this->contents[] = $content;
    }
    /**
     * Adds multiple Content objects to a Mail object
     *
     * @param array|Content[] $contents Array of Content objects
     *                                  or key value pairs
     *
     * @throws TypeException
     */
    public function add_contents($contents): void
    {
        if (\current($contents) instanceof Content) {
            foreach ($contents as $content) {
                $this->add_content($content);
            }
        } else {
            foreach ($contents as $key => $value) {
                $this->add_content($key, $value);
            }
        }
    }
    /**
     * Retrieve the contents attached to a Mail object
     *
     * Will return array of Content Objects with text/plain MimeType first
     * Array re-ordered before return where this is not already the case
     *
     * @return Content[]
     */
    public function get_contents()
    {
        if ($this->contents) {
            if ($this->contents[0]->get_type() !== Mime_Type::TEXT && \count($this->contents) > 1) {
                foreach ($this->contents as $key => $value) {
                    if ($value->get_type() === Mime_Type::TEXT) {
                        $plain_content = $value;
                        unset($this->contents[$key]);
                        break;
                    }
                }
                if (isset($plain_content)) {
                    array_unshift($this->contents, $plain_content);
                }
            }
        }
        return $this->contents;
    }
    /**
     * Add an attachment to a Mail object
     *
     * @param string|Attachment $attachment  Attachment object or
     *                                       Base64 encoded content
     * @param string|null       $type        Mime type of the attachment
     * @param string|null       $filename    File name of the attachment
     * @param string|null       $disposition How the attachment should be
     *                                       displayed: inline or attachment
     *                                       default is attachment
     * @param string|null       $content_id  Used when disposition is inline
     *                                       to display the file within the
     *                                       body of the email
     * @throws TypeException
     */
    public function add_attachment($attachment, $type = null, $filename = null, $disposition = null, $content_id = null): void
    {
        if (\is_array($attachment)) {
            $attachment = new Attachment($attachment[0], $attachment[1], $attachment[2], $attachment[3], $attachment[4]);
        } elseif (!$attachment instanceof Attachment) {
            $attachment = new Attachment($attachment, $type, $filename, $disposition, $content_id);
        }
        $this->attachments[] = $attachment;
    }
    /**
     * Adds multiple attachments to a Mail object
     *
     * @param array|Attachment[] $attachments Array of Attachment objects or
     *                                        arrays
     * @throws TypeException
     */
    public function add_attachments($attachments): void
    {
        foreach ($attachments as $attachment) {
            $this->add_attachment($attachment);
        }
    }
    /**
     * Retrieve the attachments attached to a Mail object
     *
     * @return Attachment[]
     */
    public function get_attachments()
    {
        return $this->attachments;
    }
    /**
     * Add a template id to a Mail object
     *
     * @param TemplateId|string $template_id The id of the template to be
     *                                       applied to this email
     * @throws TypeException
     */
    public function set_template_id($template_id): void
    {
        if (!$template_id instanceof Template_Id) {
            $template_id = new Template_Id($template_id);
        }
        $this->template_id = $template_id;
    }
    /**
     * Retrieve a template id attached to a Mail object
     *
     * @return TemplateId
     */
    public function get_template_id()
    {
        return $this->template_id;
    }
    /**
     * Add a section to a Mail object
     *
     * @param string|Section $key   Key or Section object
     * @param string|null    $value Value
     */
    public function add_section($key, $value = null): void
    {
        if ($key instanceof Section) {
            $section = $key;
            $this->sections[$section->get_key()] = $section->get_value();
            return;
        }
        $this->sections[$key] = (string) $value;
    }
    /**
     * Adds multiple sections to a Mail object
     *
     * @param array|Section[] $sections Array of CustomArg objects
     *                                  or key/values
     */
    public function add_sections($sections): void
    {
        if (\current($sections) instanceof Section) {
            foreach ($sections as $section) {
                $this->add_section($section);
            }
        } else {
            foreach ($sections as $key => $value) {
                $this->add_section($key, $value);
            }
        }
    }
    /**
     * Retrieve the section(s) attached to a Mail object
     *
     * @return Section[]
     */
    public function get_sections()
    {
        return $this->sections;
    }
    /**
     * Add a header to a Mail object
     *
     * Note that headers added to Personalization objects override
     * global headers.
     *
     * @param string|Header $key   Key or Header object
     * @param string|null   $value Value
     */
    public function add_global_header($key, $value = null): void
    {
        if ($key instanceof Header) {
            $header = $key;
            $this->headers[$header->get_key()] = $header->get_value();
            return;
        }
        $this->headers[$key] = (string) $value;
    }
    /**
     * Adds multiple headers to a Mail object
     *
     * Note that headers added to Personalization objects override
     * global headers.
     *
     * @param array|Header[] $headers Array of Header objects
     *                                or key values
     */
    public function add_global_headers($headers): void
    {
        if (\current($headers) instanceof Header) {
            foreach ($headers as $header) {
                $this->add_global_header($header);
            }
        } else {
            foreach ($headers as $key => $value) {
                $this->add_global_header($key, $value);
            }
        }
    }
    /**
     * Retrieve the headers attached to a Mail object
     *
     * @return Header[]
     */
    public function get_global_headers()
    {
        return $this->headers;
    }
    /**
     * Add a substitution to a Mail object
     *
     * Note that substitutions added to Personalization objects override
     * global substitutions.
     *
     * @param string|Substitution $key   Key or Substitution object
     * @param string|null         $value Value
     */
    public function add_global_substitution($key, $value = null): void
    {
        if ($key instanceof Substitution) {
            $substitution = $key;
            $this->substitutions[$substitution->get_key()] = $substitution->get_value();
            return;
        }
        $this->substitutions[$key] = $value;
    }
    /**
     * Adds multiple substitutions to a Mail object
     *
     * Note that substitutions added to Personalization objects override
     * global headers.
     *
     * @param array|Substitution[] $substitutions Array of Substitution
     *                                            objects or key/values
     */
    public function add_global_substitutions($substitutions): void
    {
        if (\current($substitutions) instanceof Substitution) {
            foreach ($substitutions as $substitution) {
                $this->add_global_substitution($substitution);
            }
        } else {
            foreach ($substitutions as $key => $value) {
                $this->add_global_substitution($key, $value);
            }
        }
    }
    /**
     * Retrieve the substitutions attached to a Mail object
     *
     * @return Substitution[]
     */
    public function get_global_substitutions()
    {
        return $this->substitutions;
    }
    /**
     * Add a category to a Mail object
     *
     * @param string|Category $category Category object or category name
     * @throws TypeException
     */
    public function add_category($category): void
    {
        if (!$category instanceof Category) {
            $category = new Category($category);
        }
        Assert::accept($category, 'category', function (): bool {
            $categories = $this->categories;
            if (!\is_array($categories)) {
                $categories = [];
            }
            return \count($categories) < 10;
        }, 'Number of elements in "$categories" can not exceed 10.');
        $this->categories[] = $category;
    }
    /**
     * Adds multiple categories to a Mail object
     *
     * @param array|Category[] $categories Array of Category objects or arrays
     * @throws TypeException
     */
    public function add_categories($categories): void
    {
        foreach ($categories as $category) {
            $this->add_category($category);
        }
    }
    /**
     * Retrieve the categories attached to a Mail object
     *
     * @return Category[]
     */
    public function get_categories()
    {
        return $this->categories;
    }
    /**
     * Add a custom arg to a Mail object
     *
     * Note that custom args added to Personalization objects override
     * global custom args.
     *
     * @param string|CustomArg $key   Key or CustomArg object
     * @param string|null      $value Value
     */
    public function add_global_custom_arg($key, $value = null): void
    {
        if ($key instanceof Custom_Arg) {
            $custom_arg = $key;
            $this->custom_args[$custom_arg->get_key()] = $custom_arg->get_value();
            return;
        }
        $this->custom_args[$key] = (string) $value;
    }
    /**
     * Adds multiple custom args to a Mail object
     *
     * Note that custom args added to Personalization objects override
     * global custom args.
     *
     * @param array|CustomArg[] $custom_args Array of CustomArg objects
     *                                       or key/values
     */
    public function add_global_custom_args($custom_args): void
    {
        if (\current($custom_args) instanceof Custom_Arg) {
            foreach ($custom_args as $custom_arg) {
                $this->add_global_custom_arg($custom_arg);
            }
        } else {
            foreach ($custom_args as $key => $value) {
                $this->add_global_custom_arg($key, $value);
            }
        }
    }
    /**
     * Retrieve the custom args attached to a Mail object
     *
     * @return CustomArg[]
     */
    public function get_global_custom_args()
    {
        return $this->custom_args;
    }
    /**
     * Add a unix timestamp allowing you to specify when you want your
     * email to be delivered to a Mail object
     *
     * Note that timestamps added to Personalization objects override
     * global timestamps.
     *
     * @param int|SendAt $send_at A unix timestamp
     * @throws TypeException
     */
    public function set_global_send_at($send_at): void
    {
        if (!$send_at instanceof Send_At) {
            $send_at = new Send_At($send_at);
        }
        $this->send_at = $send_at;
    }
    /**
     * Retrieve the unix timestamp attached to a Mail object
     *
     * @return SendAt
     */
    public function get_global_send_at()
    {
        return $this->send_at;
    }
    /**
     * Add a batch id to a Mail object
     *
     * @param string|BatchId $batch_id Id for a batch of emails
     *                                 to be sent at the same time
     * @throws TypeException
     */
    public function set_batch_id($batch_id): void
    {
        if (!$batch_id instanceof Batch_Id) {
            $batch_id = new Batch_Id($batch_id);
        }
        $this->batch_id = $batch_id;
    }
    /**
     * Retrieve the batch id attached to a Mail object
     *
     * @return BatchId
     */
    public function get_batch_id()
    {
        return $this->batch_id;
    }
    /**
     * Add a Asm describing how to handle unsubscribes to a Mail object
     *
     * @param int|Asm $group_id          Asm object or unsubscribe group id
     *                                   to associate this email with
     * @param array   $groups_to_display Array of integer ids of unsubscribe
     *                                   groups to be displayed on the
     *                                   unsubscribe preferences page
     * @throws TypeException
     */
    public function set_asm($group_id, $groups_to_display = null): void
    {
        if ($group_id instanceof Asm) {
            $asm = $group_id;
            $this->asm = $asm;
        } else {
            $this->asm = new Asm($group_id, $groups_to_display);
        }
    }
    /**
     * Retrieve the Asm object describing how to handle unsubscribes attached
     * to a Mail object
     *
     * @return Asm
     */
    public function get_asm()
    {
        return $this->asm;
    }
    /**
     * Add the IP pool name to a Mail object
     *
     * @param string|IpPoolName $ip_pool_name The IP Pool that you would
     *                                        like to send this email from
     * @throws TypeException
     */
    public function set_ip_pool_name($ip_pool_name): void
    {
        if ($ip_pool_name instanceof Ip_Pool_Name) {
            $this->ip_pool_name = $ip_pool_name->get_ip_pool_name();
        } else {
            $this->ip_pool_name = new Ip_Pool_Name($ip_pool_name);
        }
    }
    /**
     * Retrieve the IP pool name attached to a Mail object
     *
     * @return IpPoolName
     */
    public function get_ip_pool_name()
    {
        return $this->ip_pool_name;
    }
    /**
     * Add a MailSettings object to a Mail object
     *
     * @param MailSettings $mail_settings A collection of different
     *                                    mail settings that you can
     *                                    use to specify how you would
     *                                    like this email to be handled
     */
    public function set_mail_settings($mail_settings): void
    {
        $this->mail_settings = $mail_settings;
    }
    /**
     * Retrieve the MailSettings object attached to a Mail object
     *
     * @return MailSettings
     */
    public function get_mail_settings()
    {
        return $this->mail_settings;
    }
    /**
     * Set the Bcc settings on a MailSettings object
     *
     * @param bool|BccSettings $enable A BccSettings object or a boolean
     *                                 to determine if this setting is active
     * @param string|null      $email  The email address to be bcc'ed
     * @throws TypeException
     */
    public function set_bcc_settings($enable, $email = null): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_bcc_settings($enable, $email);
    }
    /**
     * Enable bypass bounce management on a MailSettings object
     *
     * Allows you to bypass the bounce list to ensure that the email is delivered to recipients.
     * Spam report and unsubscribe lists will still be checked; addresses on these other lists
     * will not receive the message.
     *
     * This filter cannot be combined with the bypass_list_management filter.
     *
     * @throws TypeException
     */
    public function enable_bypass_bounce_management(): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_bypass_bounce_management(true);
    }
    /**
     * Enable bypass list management on a MailSettings object
     *
     * Allows you to bypass all unsubscribe groups and suppressions to ensure
     * that the email is delivered to every single recipient. This should only
     * be used in emergencies when it is absolutely necessary that every
     * recipient receives your email.
     *
     * @throws TypeException
     */
    public function enable_bypass_list_management(): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_bypass_list_management(true);
    }
    /**
     * Enable bypass spam management on a MailSettings object
     *
     * Allows you to bypass the spam report list to ensure that the email is delivered to recipients.
     * Bounce and unsubscribe lists will still be checked; addresses on these other lists will not
     * receive the message.
     *
     * This filter cannot be combined with the bypass_list_management filter.
     *
     * @throws TypeException
     */
    public function enable_bypass_spam_management(): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_bypass_spam_management(true);
    }
    /**
     * Enable bypass unsubscribe management on a MailSettings object
     *
     * Allows you to bypass the global unsubscribe list to ensure that the email is delivered
     * to recipients. Bounce and spam report lists will still be checked; addresses on these
     * other lists will not receive the message. This filter applies only to global unsubscribes
     * and will not bypass group unsubscribes.
     *
     * This filter cannot be combined with the bypass_list_management filter.
     *
     * @throws TypeException
     */
    public function enable_bypass_unsubscribe_management(): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_bypass_unsubscribe_management(true);
    }
    /**
     * Disable bypass bounce management on a MailSettings object
     *
     * Allows you to bypass the bounce list to ensure that the email is delivered to recipients.
     * Spam report and unsubscribe lists will still be checked; addresses on these other lists
     * will not receive the message.
     *
     * This filter cannot be combined with the bypass_list_management filter.
     *
     * @throws TypeException
     */
    public function disable_bypass_bounce_management(): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_bypass_bounce_management(false);
    }
    /**
     * Disable bypass list management on a MailSettings object
     *
     * Allows you to bypass all unsubscribe groups and suppressions to ensure
     * that the email is delivered to every single recipient. This should only
     * be used in emergencies when it is absolutely necessary that every
     * recipient receives your email.
     *
     * @throws TypeException
     */
    public function disable_bypass_list_management(): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_bypass_list_management(false);
    }
    /**
     * Disable bypass spam management on a MailSettings object
     *
     * Allows you to bypass the spam report list to ensure that the email is delivered to recipients.
     * Bounce and unsubscribe lists will still be checked; addresses on these other lists will not
     * receive the message.
     *
     * This filter cannot be combined with the bypass_list_management filter.
     *
     * @throws TypeException
     */
    public function disable_bypass_spam_management(): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_bypass_spam_management(false);
    }
    /**
     * Disable bypass global unsubscribe management on a MailSettings object
     *
     * Allows you to bypass the global unsubscribe list to ensure that the email is delivered
     * to recipients. Bounce and spam report lists will still be checked; addresses on these
     * other lists will not receive the message. This filter applies only to global unsubscribes
     * and will not bypass group unsubscribes.
     *
     * This filter cannot be combined with the bypass_list_management filter.
     *
     * @throws TypeException
     */
    public function disable_bypass_unsubscribe_management(): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_bypass_unsubscribe_management(false);
    }
    /**
     * Set the Footer settings on a MailSettings object
     *
     * @param bool|Footer $enable A Footer object or a boolean
     *                            to determine if this setting is active
     * @param string|null $text   The plain text content of the footer
     * @param string|null $html   The HTML content of the footer
     *
     * @throws TypeException
     */
    public function set_footer($enable = null, $text = null, $html = null): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_footer($enable, $text, $html);
    }
    /**
     * Enable sandbox mode on a MailSettings object
     *
     * This allows you to send a test email to ensure that your request
     * body is valid and formatted correctly.
     *
     * @throws TypeException
     */
    public function enable_sand_box_mode(): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_sand_box_mode(true);
    }
    /**
     * Disable sandbox mode on a MailSettings object
     *
     * This to ensure that your request is not in sandbox mode.
     *
     * @throws TypeException
     */
    public function disable_sand_box_mode(): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_sand_box_mode(false);
    }
    /**
     * Set the spam check settings on a MailSettings object
     *
     * @param bool|SpamCheck $enable      A SpamCheck object or a boolean
     *                                    to determine if this setting is active
     * @param int|null       $threshold   The threshold used to determine if your
     *                                    content qualifies as spam on a scale from
     *                                    1 to 10, with 10 being most strict, or
     *                                    most likely to be considered as spam
     * @param string|null    $post_to_url An Inbound Parse URL that you would like
     *                                    a copy of your email along with the spam
     *                                    report to be sent to
     *
     * @throws TypeException
     */
    public function set_spam_check($enable = null, $threshold = null, $post_to_url = null): void
    {
        if (!$this->mail_settings instanceof Mail_Settings) {
            $this->mail_settings = new Mail_Settings();
        }
        $this->mail_settings->set_spam_check($enable, $threshold, $post_to_url);
    }
    /**
     * Add a TrackingSettings object to a Mail object
     *
     * @param TrackingSettings $tracking_settings Settings to determine how you
     *                                            would like to track the metrics
     *                                            of how your recipients interact
     *                                            with your email
     */
    public function set_tracking_settings($tracking_settings): void
    {
        $this->tracking_settings = $tracking_settings;
    }
    /**
     * Retrieve the TrackingSettings object attached to a Mail object
     *
     * @return TrackingSettings
     */
    public function get_tracking_settings()
    {
        return $this->tracking_settings;
    }
    /**
     * Set the click tracking settings on a TrackingSettings object
     *
     * @param bool|ClickTracking $enable      A ClickTracking object or a boolean
     *                                        to determine if this setting is active
     * @param bool|null          $enable_text Indicates if this setting should be
     *                                        included in the text/plain portion of
     *                                        your email
     *
     * @throws TypeException
     */
    public function set_click_tracking($enable = null, $enable_text = null): void
    {
        if (!$this->tracking_settings instanceof Tracking_Settings) {
            $this->tracking_settings = new Tracking_Settings();
        }
        $this->tracking_settings->set_click_tracking($enable, $enable_text);
    }
    /**
     * Set the open tracking settings on a TrackingSettings object
     *
     * @param bool|OpenTracking $enable           A OpenTracking object or a boolean
     *                                            to determine if this setting is
     *                                            active
     * @param string|null       $substitution_tag Allows you to specify a
     *                                            substitution tag that you can
     *                                            insert in the body of your email
     *                                            at a location that you desire.
     *                                            This tag will be replaced by the
     *                                            open tracking pixel
     *
     * @throws TypeException
     */
    public function set_open_tracking($enable = null, $substitution_tag = null): void
    {
        if (!$this->tracking_settings instanceof Tracking_Settings) {
            $this->tracking_settings = new Tracking_Settings();
        }
        $this->tracking_settings->set_open_tracking($enable, $substitution_tag);
    }
    /**
     * Set the subscription tracking settings on a TrackingSettings object
     *
     * @param bool|SubscriptionTracking $enable           A SubscriptionTracking
     *                                                    object or a boolean to
     *                                                    determine if this setting
     *                                                    is active
     * @param string|null               $text             Text to be appended to the
     *                                                    email, with the
     *                                                    subscription tracking
     *                                                    link. You may control
     *                                                    where the link is by using
     *                                                    the tag <% %>
     * @param string|null               $html             HTML to be appended to the
     *                                                    email, with the
     *                                                    subscription tracking
     *                                                    link. You may control
     *                                                    where the link is by using
     *                                                    the tag <% %>
     * @param string|null               $substitution_tag A tag that will be
     *                                                    replaced with the
     *                                                    unsubscribe URL. for
     *                                                    example:
     *                                                    [unsubscribe_url]. If this
     *                                                    parameter is used, it will
     *                                                    override both the text and
     *                                                    html parameters. The URL
     *                                                    of the link will be placed
     *                                                    at the substitution tag’s
     *                                                    location, with no
     *                                                    additional formatting
     */
    public function set_subscription_tracking($enable = null, $text = null, $html = null, $substitution_tag = null): void
    {
        if (!$this->tracking_settings instanceof Tracking_Settings) {
            $this->tracking_settings = new Tracking_Settings();
        }
        $this->tracking_settings->set_subscription_tracking($enable, $text, $html, $substitution_tag);
    }
    /**
     * Set the Google anatlyics settings on a TrackingSettings object
     *
     * @param bool|Ganalytics $enable       A Ganalytics object or a boolean to
     *                                      determine if this setting
     *                                      is active
     * @param string|null     $utm_source   Name of the referrer source. (e.g.
     *                                      Google, SomeDomain.com, or
     *                                      Marketing Email)
     * @param string|null     $utm_medium   Name of the marketing medium.
     *                                      (e.g. Email)
     * @param string|null     $utm_term     Used to identify any paid keywords.
     * @param string|null     $utm_content  Used to differentiate your campaign
     *                                      from advertisements
     * @param string|null     $utm_campaign The name of the campaign
     *
     * @throws TypeException
     */
    public function set_ganalytics($enable = null, $utm_source = null, $utm_medium = null, $utm_term = null, $utm_content = null, $utm_campaign = null): void
    {
        if (!$this->tracking_settings instanceof Tracking_Settings) {
            $this->tracking_settings = new Tracking_Settings();
        }
        $this->tracking_settings->set_ganalytics($enable, $utm_source, $utm_medium, $utm_term, $utm_content, $utm_campaign);
    }
    /**
     * Return an array representing a request object for the Twilio SendGrid API
     *
     * @return null|array
     * @throws TypeException
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        // Detect if we are using the new dynamic templates
        if ($this->get_template_id() !== null && str_starts_with($this->get_template_id()->get_template_id(), 'd-')) {
            foreach ($this->personalization as $personalization) {
                $personalization->set_has_dynamic_template(true);
            }
        }
        return array_filter(['personalizations' => array_values(array_filter($this->get_personalizations(), static fn($value) => null !== $value && null !== $value->jsonSerialize())), 'from' => $this->get_from(), 'reply_to' => $this->get_reply_to(), 'subject' => $this->get_global_subject(), 'content' => $this->get_contents(), 'attachments' => $this->get_attachments(), 'template_id' => $this->get_template_id(), 'sections' => $this->get_sections(), 'headers' => $this->get_global_headers(), 'categories' => $this->get_categories(), 'custom_args' => $this->get_global_custom_args(), 'send_at' => $this->get_global_send_at(), 'batch_id' => $this->get_batch_id(), 'asm' => $this->get_asm(), 'ip_pool_name' => $this->get_ip_pool_name(), 'substitutions' => $this->get_global_substitutions(), 'mail_settings' => $this->get_mail_settings(), 'tracking_settings' => $this->get_tracking_settings()], static fn(\Send_Grid\Mail\From|\Send_Grid\Mail\Reply_To|\Send_Grid\Mail\Subject|\Send_Grid\Mail\Template_Id|\Send_Grid\Mail\Send_At|\Send_Grid\Mail\Batch_Id|\Send_Grid\Mail\Asm|\Send_Grid\Mail\Ip_Pool_Name|\Send_Grid\Mail\Mail_Settings|\Send_Grid\Mail\Tracking_Settings|array $value) => $value !== null) ?: null;
    }
}