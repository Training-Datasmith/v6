<?php

declare (strict_types=1);
/**
 * This helper builds the MailSettings object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a MailSettings object for the /mail/send API call
 *
 * A collection of different mail settings that you can use to specify how you would
 * like this email to be handled
 *
 * @package SendGrid\Mail
 */
class Mail_Settings implements \JsonSerializable
{
    /** @var $bcc Bcc object */
    private ?\Send_Grid\Mail\Bcc_Settings $bcc = null;
    /** @var $bypass_bounce_management BypassBounceManagement object */
    private ?\Send_Grid\Mail\Bypass_Bounce_Management $bypass_bounce_management = null;
    /** @var $bypass_list_management BypassListManagement object */
    private ?\Send_Grid\Mail\Bypass_List_Management $bypass_list_management = null;
    /** @var $bypass_spam_management BypassSpamManagement object */
    private ?\Send_Grid\Mail\Bypass_Spam_Management $bypass_spam_management = null;
    /** @var $bypass_unsubscribe_management BypassUnsubscribeManagement object */
    private ?\Send_Grid\Mail\Bypass_Unsubscribe_Management $bypass_unsubscribe_management = null;
    /** @var $footer Footer object */
    private ?\Send_Grid\Mail\Footer $footer = null;
    /** @var $sandbox_mode SandBoxMode object */
    private ?\Send_Grid\Mail\Sand_Box_Mode $sandbox_mode = null;
    /** @var $spam_check SpamCheck object */
    private ?\Send_Grid\Mail\Spam_Check $spam_check = null;
    /**
     * Optional constructor
     *
     * @param BccSettings|null                 $bcc_settings                  BccSettings object
     * @param BypassBounceManagement|null      $bypass_bounce_management      BypassBounceManagement
     *                                                                        object
     * @param BypassListManagement|null        $bypass_list_management        BypassListManagement
     *                                                                        object
     * @param BypassSpamManagement|null        $bypass_spam_management        BypassSpamManagement
     *                                                                        object
     * @param BypassUnsubscribeManagement|null $bypass_unsubscribe_management BypassUnsubscribeManagement
     *                                                                        object
     * @param Footer|null                      $footer                        Footer object
     * @param SandBoxMode|null                 $sandbox_mode                  SandBoxMode object
     * @param SpamCheck|null                   $spam_check                    SpamCheck object
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($bcc_settings = null, $bypass_bounce_management = null, $bypass_list_management = null, $bypass_spam_management = null, $bypass_unsubscribe_management = null, $footer = null, $sandbox_mode = null, $spam_check = null)
    {
        if (isset($bcc_settings)) {
            $this->set_bcc_settings($bcc_settings);
        }
        if (isset($bypass_bounce_management)) {
            $this->set_bypass_bounce_management($bypass_bounce_management);
        }
        if (isset($bypass_list_management)) {
            $this->set_bypass_list_management($bypass_list_management);
        }
        if (isset($bypass_spam_management)) {
            $this->set_bypass_spam_management($bypass_spam_management);
        }
        if (isset($bypass_unsubscribe_management)) {
            $this->set_bypass_unsubscribe_management($bypass_unsubscribe_management);
        }
        if (isset($footer)) {
            $this->set_footer($footer);
        }
        if (isset($sandbox_mode)) {
            $this->set_sandbox_mode($sandbox_mode);
        }
        if (isset($spam_check)) {
            $this->set_spam_check($spam_check);
        }
    }
    /**
     * Set the bcc settings on a MailSettings object
     *
     * @param BccSettings|bool $enable The BccSettings object or an indication
     *                                 if the setting is enabled
     * @param string|null      $email  The email address that you would like
     *                                 to receive the BCC
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_bcc_settings($enable, $email = null): void
    {
        if ($enable instanceof Bcc_Settings) {
            $bcc = $enable;
            $this->bcc = $bcc;
            return;
        }
        Assert::boolean($enable, 'enable', 'Value "$enable" must be an instance of SendGrid\Mail\BccSettings or a boolean.');
        $this->bcc = new Bcc_Settings($enable, $email);
    }
    /**
     * Retrieve the bcc settings from a MailSettings object
     *
     * @return Bcc
     */
    public function get_bcc_settings()
    {
        return $this->bcc;
    }
    /**
     * Set bypass bounce management settings on a MailSettings object
     *
     * @param BypassBounceManagement|bool $enable The BypassBounceManagement
     *                                            object or an indication
     *                                            if the setting is enabled
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_bypass_bounce_management($enable): void
    {
        if ($enable instanceof Bypass_Bounce_Management) {
            $bypass_bounce_management = $enable;
            $this->bypass_bounce_management = $bypass_bounce_management;
            return;
        }
        Assert::boolean($enable, 'enable', 'Value "$enable" must be an instance of SendGrid\Mail\BypassBounceManagement
                                                or a boolean.');
        $this->bypass_bounce_management = new Bypass_Bounce_Management($enable);
    }
    /**
     * Set bypass list management settings on a MailSettings object
     *
     * @param BypassListManagement|bool $enable The BypassListManagement
     *                                          object or an indication
     *                                          if the setting is enabled
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_bypass_list_management($enable): void
    {
        if ($enable instanceof Bypass_List_Management) {
            $bypass_list_management = $enable;
            $this->bypass_list_management = $bypass_list_management;
            return;
        }
        Assert::boolean($enable, 'enable', 'Value "$enable" must be an instance of SendGrid\Mail\BypassListManagement
                                                or a boolean.');
        $this->bypass_list_management = new Bypass_List_Management($enable);
    }
    /**
     * Set bypass spam management settings on a MailSettings object
     *
     * @param BypassSpamManagement|bool $enable The BypassSpamManagement
     *                                          object or an indication
     *                                          if the setting is enabled
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_bypass_spam_management($enable): void
    {
        if ($enable instanceof Bypass_Spam_Management) {
            $bypass_spam_management = $enable;
            $this->bypass_spam_management = $bypass_spam_management;
            return;
        }
        Assert::boolean($enable, 'enable', 'Value "$enable" must be an instance of SendGrid\Mail\BypassSpamManagement or a boolean.');
        $this->bypass_spam_management = new Bypass_Spam_Management($enable);
    }
    /**
     * Set bypass unsubscribe management settings on a MailSettings object
     *
     * @param BypassUnsubscribeManagement|bool $enable The BypassUnsubscribeManagement
     *                                                 object or an indication
     *                                                 if the setting is enabled
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_bypass_unsubscribe_management($enable): void
    {
        if ($enable instanceof Bypass_Unsubscribe_Management) {
            $bypass_unsubscribe_management = $enable;
            $this->bypass_unsubscribe_management = $bypass_unsubscribe_management;
            return;
        }
        Assert::boolean($enable, 'enable', 'Value "$enable" must be an instance of SendGrid\Mail\BypassUnsubscribeManagement
                                                or a boolean.');
        $this->bypass_unsubscribe_management = new Bypass_Unsubscribe_Management($enable);
    }
    /**
     * Retrieve bypass bounce management settings from a MailSettings object
     *
     * @return BypassBounceManagement
     */
    public function get_bypass_bounce_management()
    {
        return $this->bypass_bounce_management;
    }
    /**
     * Retrieve bypass list management settings from a MailSettings object
     *
     * @return BypassListManagement
     */
    public function get_bypass_list_management()
    {
        return $this->bypass_list_management;
    }
    /**
     * Retrieve bypass spam management settings from a MailSettings object
     *
     * @return BypassSpamManagement
     */
    public function get_bypass_spam_management()
    {
        return $this->bypass_spam_management;
    }
    /**
     * Retrieve bypass unsubscribe management settings from a MailSettings object
     *
     * @return BypassUnsubscribeManagement
     */
    public function get_bypass_unsubscribe_management()
    {
        return $this->bypass_unsubscribe_management;
    }
    /**
     * Set the footer settings on a MailSettings object
     *
     * @param Footer|bool $enable The Footer object or an indication
     *                            if the setting is enabled
     * @param string|null $text   The plain text content of your footer
     * @param string|null $html   The HTML content of your footer
     *
     * @throws TypeException
     */
    public function set_footer($enable, $text = null, $html = null): void
    {
        if ($enable instanceof Footer) {
            $footer = $enable;
            $this->footer = $footer;
            return;
        }
        $this->footer = new Footer($enable, $text, $html);
    }
    /**
     * Retrieve the footer settings from a MailSettings object
     *
     * @return Footer
     */
    public function get_footer()
    {
        return $this->footer;
    }
    /**
     * Set sandbox mode settings on a MailSettings object
     *
     * @param SandBoxMode|bool $enable The SandBoxMode object or an
     *                                 indication if the setting is enabled
     *
     * @throws TypeException
     */
    public function set_sandbox_mode($enable): void
    {
        if ($enable instanceof Sand_Box_Mode) {
            $sandbox_mode = $enable;
            $this->sandbox_mode = $sandbox_mode;
            return;
        }
        Assert::boolean($enable, 'enable', 'Value "$enable" must be an instance of SendGrid\Mail\SandBoxMode or a boolean.');
        $this->sandbox_mode = new Sand_Box_Mode($enable);
    }
    /**
     * Retrieve sandbox mode settings on a MailSettings object
     *
     * @return SandBoxMode
     */
    public function get_sandbox_mode()
    {
        return $this->sandbox_mode;
    }
    /**
     * Enable sandbox mode on a MailSettings object
     *
     * @throws TypeException
     */
    public function enable_sandbox_mode(): void
    {
        $this->set_sandbox_mode(true);
    }
    /**
     * Disable sandbox mode on a MailSettings object
     *
     * @throws TypeException
     */
    public function disable_sandbox_mode(): void
    {
        $this->set_sandbox_mode(false);
    }
    /**
     * Set spam check settings on a MailSettings object
     *
     * @param SpamCheck|bool $enable      The SpamCheck object or an
     *                                    indication if the setting is enabled
     * @param int            $threshold   The threshold used to determine if your
     *                                    content qualifies as spam on a scale
     *                                    from 1 to 10, with 10 being most strict,
     *                                    or most
     * @param string         $post_to_url An Inbound Parse URL that you would like
     *                                    a copy of your email along with the spam
     *                                    report to be sent to
     *
     * @throws TypeException
     */
    public function set_spam_check($enable, $threshold = null, $post_to_url = null): void
    {
        if ($enable instanceof Spam_Check) {
            $spam_check = $enable;
            $this->spam_check = $spam_check;
            return;
        }
        Assert::boolean($enable, 'enable', 'Value "$enable" must be an instance of SendGrid\Mail\SpamCheck or a boolean.');
        $this->spam_check = new Spam_Check($enable, $threshold, $post_to_url);
    }
    /**
     * Retrieve spam check settings from a MailSettings object
     *
     * @return SpamCheck
     */
    public function get_spam_check()
    {
        return $this->spam_check;
    }
    /**
     * Return an array representing a MailSettings object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['bcc' => $this->get_bcc_settings(), 'bypass_bounce_management' => $this->get_bypass_bounce_management(), 'bypass_list_management' => $this->get_bypass_list_management(), 'bypass_spam_management' => $this->get_bypass_spam_management(), 'bypass_unsubscribe_management' => $this->get_bypass_unsubscribe_management(), 'footer' => $this->get_footer(), 'sandbox_mode' => $this->get_sandbox_mode(), 'spam_check' => $this->get_spam_check()], fn(\Send_Grid\Mail\Bcc|\Send_Grid\Mail\Bypass_Bounce_Management|\Send_Grid\Mail\Bypass_List_Management|\Send_Grid\Mail\Bypass_Spam_Management|\Send_Grid\Mail\Bypass_Unsubscribe_Management|\Send_Grid\Mail\Footer|\Send_Grid\Mail\Sand_Box_Mode|\Send_Grid\Mail\Spam_Check $value) => $value !== null) ?: null;
    }
}