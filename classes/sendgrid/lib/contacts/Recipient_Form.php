<?php

declare (strict_types=1);
/**
 * This helper builds a html form and provides a submission endpoint
 * for the form that makes a /contactdb/recipients API call.
 */
namespace Send_Grid\Contacts;

/**
 * This class is used to build a html form and provides a submission
 * endpoint for the form that makes a /contactdb/recipients API call.
 *
 * @package SendGrid\Contacts
 */
class Recipient_Form implements \Stringable
{
    /** @var $html string HTML content for the form */
    private readonly string $html;
    /**
     * Form constructor
     *
     * @param string $url The url the form should submit to
     */
    public function __construct(string $url)
    {
        $html = '<form action="' . $url . '" method="post">
    First Name: <input type="text" name="first-name"><br>
    Last Name: <input type="text" name="last-name"><br>
    E-mail: <input type="text" name="email"><br>
    <input type="submit">
</form>';
        $this->html = $html;
    }
    /**
     * Return the HTML form
     */
    public function __toString(): string
    {
        return (string) $this->html;
    }
}