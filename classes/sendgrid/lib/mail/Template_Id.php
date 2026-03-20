<?php

declare (strict_types=1);
/**
 * This helper builds the TemplateId object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a TemplateId object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Template_Id implements \JsonSerializable
{
    /**
     * @var $template_id string The id of a template that you would like to use. If you use a
     * template that contains a subject and content (either text or html), you do
     * not need to specify those at the personalizations nor message level
     */
    private $template_id;
    /**
     * Optional constructor
     *
     * @param string|null $template_id The id of a template that you would like
     *                                 to use. If you use a template that contains
     *                                 a subject and content (either text or html),
     *                                 you do not need to specify those at the
     *                                 personalizations nor message level
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($template_id = null)
    {
        if (isset($template_id)) {
            $this->set_template_id($template_id);
        }
    }
    /**
     * Add a template id to a TemplateId object
     *
     * @param string $template_id The id of a template that you would like
     *                            to use. If you use a template that contains
     *                            a subject and content (either text or html),
     *                            you do not need to specify those at the
     *                            personalizations nor message level
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_template_id($template_id): void
    {
        Assert::string($template_id, 'template_id');
        $this->template_id = $template_id;
    }
    /**
     * Retrieve a template id from a TemplateId object
     *
     * @return string
     */
    public function get_template_id()
    {
        return $this->template_id;
    }
    /**
     * Return an array representing a TemplateId object for the Twilio SendGrid API
     *
     * @return string
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return $this->get_template_id();
    }
}