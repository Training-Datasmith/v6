<?php

declare (strict_types=1);
/**
 * This helper builds the Category object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a Category object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Category implements \JsonSerializable
{
    /** @var $category string A category name for an email message. Each category name may not exceed 255 characters */
    private $category;
    /**
     * Optional constructor
     *
     * @param string|null $category A category name for an email message.
     *                              Each category name may not exceed 255
     *                              characters
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($category = null)
    {
        if (isset($category)) {
            $this->set_category($category);
        }
    }
    /**
     * Add a category to a Category object
     *
     * @param string $category A category name for an email message.
     *                         Each category name may not exceed 255
     *                         characters
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_category($category): void
    {
        Assert::max_length($category, 'category', 255);
        $this->category = $category;
    }
    /**
     * Retrieve a category from a Category object
     *
     * @return string
     */
    public function get_category()
    {
        return $this->category;
    }
    /**
     * Return an array representing a Category object for the Twilio SendGrid API
     *
     * @return string
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return $this->get_category();
    }
}