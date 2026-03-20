<?php

declare (strict_types=1);
/**
 * This helper builds the Asm object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a Asm object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Asm implements \JsonSerializable
{
    /** @var $group_id int The unsubscribe group to associate with this email */
    private $group_id;
    /**
     * @var $groups_to_display int[] An array containing the unsubscribe groups that you
     * would like to be displayed on the unsubscribe preferences page.
     */
    private $groups_to_display;
    /**
     * Optional constructor
     *
     * @param int|GroupId|null $group_id A GroupId object or the
     *                                   unsubscribe group to
     *                                   associate with this email
     * @param int[]|GroupsToDisplay|null $groups_to_display A GroupsToDisplay
     *                                                      object or an array
     *                                                      containing the
     *                                                      unsubscribe groups
     *                                                      that you would like
     *                                                      to be displayed
     *                                                      on the unsubscribe
     *                                                      preferences page.
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($group_id = null, $groups_to_display = null)
    {
        if (isset($group_id)) {
            $this->set_group_id($group_id);
        }
        if (isset($groups_to_display)) {
            $this->set_groups_to_display($groups_to_display);
        }
    }
    /**
     * Add the group id to a Asm object
     *
     * @param int|GroupId $group_id The unsubscribe group to associate with this
     *                              email
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_group_id($group_id): void
    {
        if ($group_id instanceof Group_Id) {
            $this->group_id = $group_id->get_group_id();
        } else {
            Assert::integer($group_id, 'group_id', 'Value "$group_id" must be an instance of SendGrid\Mail\GroupId or an integer.');
            $this->group_id = new Group_Id($group_id);
        }
    }
    /**
     * Retrieve the GroupId object from a Asm object
     *
     * The unsubscribe group to associate with this email
     *
     * @return int
     */
    public function get_group_id()
    {
        return $this->group_id;
    }
    /**
     * Add the groups to display id(s) to a Asm object
     *
     * @param int[]|GroupsToDisplay $groups_to_display A GroupsToDisplay
     *                                                 object or an array
     *                                                 containing the
     *                                                 unsubscribe groups
     *                                                 that you would like
     *                                                 to be displayed
     *                                                 on the unsubscribe
     *                                                 preferences page.
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_groups_to_display($groups_to_display): void
    {
        if ($groups_to_display instanceof Groups_To_Display) {
            $this->groups_to_display = $groups_to_display->get_groups_to_display();
        } else {
            Assert::is_array($groups_to_display, 'groups_to_display', 'Value "$groups_to_display" must be an instance of SendGrid\Mail\GroupsToDisplay or an array.');
            Assert::max_items($groups_to_display, 'groups_to_display', 25);
            $this->groups_to_display = new Groups_To_Display($groups_to_display);
        }
    }
    /**
     * Retrieve the groups to display id(s) from a Asm object
     *
     * @return int[]
     */
    public function get_groups_to_display()
    {
        return $this->groups_to_display;
    }
    /**
     * Return an array representing a Asm object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['group_id' => $this->get_group_id(), 'groups_to_display' => $this->get_groups_to_display()], fn(int|array $value) => $value !== null) ?: null;
    }
}