<?php

declare (strict_types=1);
/**
 * This helper builds the EmailAddress object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a EmailAddress object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Email_Address implements \JsonSerializable
{
    /** @var $name string The name of the person associated with the email */
    private $name;
    /** @var $email string The email address */
    private $email;
    /** @var $substitutions Substitution[] An array of key/value substitutions
     * to be be applied to the text and html content of the email body
     */
    private $substitutions;
    /** @var $subject Subject The personalized subject of the email */
    private ?\Send_Grid\Mail\Subject $subject = null;
    /**
     * Optional constructor
     *
     * @param string|null $emailAddress  The email address
     * @param string|null $name          The name of the person associated with
     *                                   the email
     * @param array|null  $substitutions An array of key/value substitutions to
     *                                   be be applied to the text and html content
     *                                   of the email body
     * @param string|null $subject       The personalized subject of the email
     * @throws TypeException
     */
    public function __construct($email_address = null, $name = null, $substitutions = null, $subject = null)
    {
        if (isset($email_address)) {
            $this->set_email_address($email_address);
        }
        if (isset($name) && $name !== null) {
            $this->set_name($name);
        }
        if (isset($substitutions)) {
            $this->set_substitutions($substitutions);
        }
        if (isset($subject)) {
            $this->set_subject($subject);
        }
    }
    /**
     * Add the email address to a EmailAddress object
     *
     * @param string $emailAddress The email address
     *
     * @throws TypeException
     */
    public function set_email_address($email_address): void
    {
        Assert::email($email_address, 'emailAddress');
        $this->email = $email_address;
    }
    /**
     * Retrieve the email address from a EmailAddress object
     *
     * @return string
     */
    public function get_email_address()
    {
        return $this->email;
    }
    /**
     * Retrieve the email address from a EmailAddress object
     *
     * @return string
     */
    public function get_email()
    {
        return $this->get_email_address();
    }
    /**
     * Add a name to a EmailAddress object
     *
     * @param string $name The name of the person associated with the email
     *
     * @throws TypeException
     */
    public function set_name($name): void
    {
        Assert::string($name, 'name');
        $this->name = !empty($name) ? $name : null;
    }
    /**
     * Retrieve the name from a EmailAddress object
     *
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }
    /**
     * Add substitutions to a EmailAddress object
     *
     * @param array $substitutions An array of key/value substitutions to
     *                             be be applied to the text and html content
     *                             of the email body
     *
     * @throws TypeException
     */
    public function set_substitutions($substitutions): void
    {
        Assert::max_items($substitutions, 'substitutions', 10000);
        $this->substitutions = $substitutions;
    }
    /**
     * Retrieve substitutions from a EmailAddress object
     */
    public function get_substitutions()
    {
        return $this->substitutions;
    }
    /**
     * Add a subject to a EmailAddress object
     *
     * @param string $subject The personalized subject of the email
     *
     * @throws TypeException
     */
    public function set_subject($subject): void
    {
        Assert::string($subject, 'subject');
        // Now that we know it is a string, we can safely create a new subject
        $this->subject = new Subject($subject);
    }
    /**
     * Retrieve a subject from an EmailAddress object
     *
     * @return Subject
     */
    public function get_subject()
    {
        return $this->subject;
    }
    /**
     * Determine if this EmailAddress object is personalized by either
     * containing substitutions or a specific subject.
     */
    public function is_personalized(): bool
    {
        if ($this->get_substitutions()) {
            return true;
        }
        return (bool) $this->get_subject();
    }
    /**
     * Return an array representing an EmailAddress object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['name' => $this->get_name(), 'email' => $this->get_email()], fn(string $value) => $value !== null) ?: null;
    }
}