<?php

declare (strict_types=1);
/**
 * This helper builds the BatchId object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a BatchId object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Batch_Id implements \JsonSerializable
{
    /** @var $batch_id string This ID represents a batch of emails to be sent at the same time */
    private $batch_id;
    /**
     * Optional constructor
     *
     * @param string|null $batch_id This ID represents a batch of emails to
     *                              be sent at the same time
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($batch_id = null)
    {
        if (isset($batch_id)) {
            $this->set_batch_id($batch_id);
        }
    }
    /**
     * Add the batch id to a BatchId object
     *
     * @param string $batch_id This ID represents a batch of emails to be sent
     *                         at the same time
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_batch_id($batch_id): void
    {
        Assert::string($batch_id, 'batch_id');
        $this->batch_id = $batch_id;
    }
    /**
     * Return the batch id from a BatchId object
     *
     * @return string
     */
    public function get_batch_id()
    {
        return $this->batch_id;
    }
    /**
     * Return an array representing a BatchId object for the Twilio SendGrid API
     *
     * @return null|string
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return $this->get_batch_id();
    }
}