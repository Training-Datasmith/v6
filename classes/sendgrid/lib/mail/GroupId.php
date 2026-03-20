<?php

declare (strict_types=1);
/**
 * This helper builds the GroupId object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a GroupId object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Group_Id implements \JsonSerializable
{
    /** @var $group_id int The unsubscribe group to associate with this email */
    private $group_id;
    /**
     * Optional constructor
     *
     * @param int|null $group_id The unsubscribe group to associate with this email
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($group_id = null)
    {
        if (isset($group_id)) {
            $this->set_group_id($group_id);
        }
    }
    /**
     * Add the group id to a GroupId object
     *
     * @param int $group_id The unsubscribe group to associate with this email
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_group_id($group_id): void
    {
        Assert::integer($group_id, 'group_id');
        $this->group_id = $group_id;
    }
    /**
     * Retrieve the group id from a GroupId object
     *
     * @return int
     */
    public function get_group_id()
    {
        return $this->group_id;
    }
    /**
     * Return an array representing a GroupId object for the Twilio SendGrid API
     *
     * @return int
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return $this->get_group_id();
    }
}