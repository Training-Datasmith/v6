<?php

declare (strict_types=1);
/**
 * This helper builds the Personalization object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a Personalization object for
 * the /mail/send API call
 *
 * Each Personalization can be thought of as an envelope - it defines
 * who should receive an individual message and how that message should be handled
 *
 * @package SendGrid\Mail
 */
class Personalization implements \JsonSerializable
{
    /** @var $tos To[] objects */
    private $tos;
    /** @var $from From object */
    private $from;
    /** @var $ccs Cc[] objects */
    private $ccs;
    /** @var $bccs Bcc[] objects */
    private $bccs;
    /** @var $subject Subject object */
    private ?\Send_Grid\Mail\Subject $subject = null;
    /** @var $headers Header[] array of header key values */
    private ?array $headers = null;
    /** @var $substitutions Substitution[] array of substitution key values, used for legacy templates */
    private ?array $substitutions = null;
    /** @var bool if we are using dynamic templates this will be true */
    private $has_dynamic_template = false;
    /** @var $custom_args CustomArg[] array of custom arg key values */
    private ?array $custom_args = null;
    /** @var $send_at SendAt object */
    private $send_at;
    /**
     * Add a To object to a Personalization object
     *
     * @param To $email To object
     */
    public function add_to($email): void
    {
        $this->tos[] = $email;
    }
    /**
     * Retrieve To object(s) from a Personalization object
     *
     * @return To[]
     */
    public function get_tos()
    {
        return $this->tos;
    }
    /**
     * Add a From object to a Personalization object
     *
     * @param From $email From object
     */
    public function add_from($email): void
    {
        $this->from = $email;
    }
    /**
     * Retrieve From object from a Personalization object
     *
     * @return From|null
     */
    public function get_from()
    {
        return $this->from;
    }
    /**
     * Add a Cc object to a Personalization object
     *
     * @param Cc $email Cc object
     */
    public function add_cc($email): void
    {
        $this->ccs[] = $email;
    }
    /**
     * Retrieve Cc object(s) from a Personalization object
     *
     * @return Cc[]
     */
    public function get_ccs()
    {
        return $this->ccs;
    }
    /**
     * Add a Bcc object to a Personalization object
     *
     * @param Bcc $email Bcc object
     */
    public function add_bcc($email): void
    {
        $this->bccs[] = $email;
    }
    /**
     * Retrieve Bcc object(s) from a Personalization object
     *
     * @return Bcc[]
     */
    public function get_bccs()
    {
        return $this->bccs;
    }
    /**
     * Add a subject object to a Personalization object
     *
     * @param Subject|string $subject Subject object or string
     *
     * @throws TypeException
     */
    public function set_subject($subject): void
    {
        if (!$subject instanceof Subject) {
            Assert::string($subject, 'subject', '"$subject" must be an instance of SendGrid\Mail\Subject or a string');
            $subject = new Subject($subject);
        }
        $this->subject = $subject;
    }
    /**
     * Retrieve a Subject object from a Personalization object
     *
     * @return Subject|null
     */
    public function get_subject()
    {
        return $this->subject;
    }
    /**
     * Add a Header object to a Personalization object
     *
     * @param Header $header Header object
     */
    public function add_header($header): void
    {
        Assert::is_instance_of($header, 'header', Header::class);
        $this->headers[$header->get_key()] = $header->get_value();
    }
    /**
     * Retrieve header key/value pairs from a Personalization object
     *
     * @return array|null
     */
    public function get_headers()
    {
        return $this->headers;
    }
    /**
     * Add a Substitution object or key/value to a Personalization object
     *
     * @param Substitution|string $data DynamicTemplateData object or the key of a
     *                                  dynamic data
     * @param string|null $value The value of dynamic data
     *
     * @throws TypeException
     */
    public function add_dynamic_template_data($data, $value = null): void
    {
        $this->add_substitution($data, $value);
    }
    /**
     * Retrieve dynamic template data key/value pairs from a Personalization object
     *
     * @return array|null
     */
    public function get_dynamic_template_data()
    {
        return $this->get_substitutions();
    }
    /**
     * Add a Substitution object or key/value to a Personalization object
     *
     * @param Substitution|string $substitution Substitution object or the key of a
     *                                          substitution
     * @param string|null $value The value of a substitution
     *
     * @throws TypeException
     */
    public function add_substitution($substitution, $value = null): void
    {
        if (!$substitution instanceof Substitution) {
            $key = $substitution;
            $substitution = new Substitution($key, $value);
        }
        $this->substitutions[$substitution->get_key()] = $substitution->get_value();
    }
    /**
     * Retrieve substitution key/value pairs from a Personalization object
     *
     * @return array|null
     */
    public function get_substitutions()
    {
        return $this->substitutions;
    }
    /**
     * Add a CustomArg object to a Personalization object
     *
     * @param CustomArg $custom_arg CustomArg object
     *
     * @throws TypeException
     */
    public function add_custom_arg($custom_arg): void
    {
        Assert::is_instance_of($custom_arg, 'custom_arg', Custom_Arg::class);
        $this->custom_args[$custom_arg->get_key()] = (string) $custom_arg->get_value();
    }
    /**
     * Retrieve custom arg key/value pairs from a Personalization object
     *
     * @return array|null
     */
    public function get_custom_args()
    {
        return $this->custom_args;
    }
    /**
     * Add a SendAt object to a Personalization object
     *
     * @param SendAt $send_at SendAt object
     *
     * @throws TypeException
     */
    public function set_send_at($send_at): void
    {
        Assert::is_instance_of($send_at, 'send_at', Send_At::class);
        $this->send_at = $send_at;
    }
    /**
     * Retrieve a SendAt object from a Personalization object
     *
     * @return SendAt|null
     */
    public function get_send_at()
    {
        return $this->send_at;
    }
    /**
     * Specify if this personalization is using dynamic templates
     *
     * @param bool $has_dynamic_template are we using dynamic templates
     *
     * @throws TypeException
     */
    public function set_has_dynamic_template($has_dynamic_template): void
    {
        Assert::boolean($has_dynamic_template, 'has_dynamic_template');
        $this->has_dynamic_template = $has_dynamic_template;
    }
    /**
     * Determine if this Personalization object is using dynamic templates
     *
     * @return bool
     */
    public function get_has_dynamic_template()
    {
        return $this->has_dynamic_template;
    }
    /**
     * Return an array representing a Personalization object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        if ($this->get_has_dynamic_template()) {
            $dynamic_substitutions = $this->get_substitutions();
            $substitutions = null;
        } else {
            $substitutions = $this->get_substitutions();
            $dynamic_substitutions = null;
        }
        return array_filter(['to' => $this->get_tos(), 'from' => $this->get_from(), 'cc' => $this->get_ccs(), 'bcc' => $this->get_bccs(), 'subject' => $this->get_subject(), 'headers' => $this->get_headers(), 'substitutions' => $substitutions, 'dynamic_template_data' => $dynamic_substitutions, 'custom_args' => $this->get_custom_args(), 'send_at' => $this->get_send_at()], static fn(\Send_Grid\Mail\From|\Send_Grid\Mail\Subject|\Send_Grid\Mail\Send_At|array|null $value) => $value !== null) ?: null;
    }
}