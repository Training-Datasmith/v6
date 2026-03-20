<?php

declare (strict_types=1);
/**
 * This helper builds the Attachment object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a Attachment object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Attachment implements \JsonSerializable
{
    /** @var $content string Base64 encoded content */
    private $content;
    /** @var $type string Mime type of the attachment */
    private $type;
    /** @var $filename string File name of the attachment */
    private $filename;
    /** @var $disposition string How the attachment should be displayed: inline or attachment, default is attachment */
    private $disposition;
    /** @var $content_id string Used when disposition is inline to display the file within the body of the email */
    private $content_id;
    /**
     * Optional constructor
     *
     * @param string $content     Base64 encoded content
     * @param string $type        Mime type of the attachment
     * @param string $filename    File name of the attachment
     * @param string $disposition How the attachment should be displayed: inline
     *                            or attachment, default is attachment
     * @param string $content_id  Used when disposition is inline to display the
     *                            file within the body of the email
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($content = null, $type = null, $filename = null, $disposition = null, $content_id = null)
    {
        if (isset($content)) {
            $this->set_content($content);
        }
        if (isset($type)) {
            $this->set_type($type);
        }
        if (isset($filename)) {
            $this->set_filename($filename);
        }
        if (isset($disposition)) {
            $this->set_disposition($disposition);
        }
        if (isset($content_id)) {
            $this->set_content_id($content_id);
        }
    }
    /**
     * Add the content to a Attachment object
     *
     * @param string $content Base64 encoded content
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_content($content): void
    {
        Assert::min_length($content, 'content', 1);
        if (!$this->is_base64($content)) {
            $this->content = base64_encode($content);
        } else {
            $this->content = $content;
        }
    }
    /**
     * Retrieve the content from a Attachment object
     *
     * @return string
     */
    public function get_content()
    {
        return $this->content;
    }
    /**
     * Add the mime type to a Attachment object
     *
     * @param string $type Mime type of the attachment
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_type($type): void
    {
        Assert::min_length($type, 'type', 1);
        $this->type = $type;
    }
    /**
     * Retrieve the mime type from a Attachment object
     *
     * @return string
     */
    public function get_type()
    {
        return $this->type;
    }
    /**
     * Add the file name to a Attachment object
     *
     * @param string $filename File name of the attachment
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_filename($filename): void
    {
        Assert::string($filename, 'filename');
        $this->filename = $filename;
    }
    /**
     * Retrieve the file name from a Attachment object
     *
     * @return string
     */
    public function get_filename()
    {
        return $this->filename;
    }
    /**
     * Add the disposition to a Attachment object
     *
     * @param string $disposition How the attachment should be displayed:
     *                            inline or attachment, default is attachment
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_disposition($disposition): void
    {
        Assert::any_of($disposition, 'disposition', ['inline', 'attachment']);
        $this->disposition = $disposition;
    }
    /**
     * Retrieve the disposition from a Attachment object
     *
     * @return string
     */
    public function get_disposition()
    {
        return $this->disposition;
    }
    /**
     * Add the content id to a Attachment object
     *
     * @param string $content_id Used when disposition is inline to display
     *                           the file within the body of the email
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_content_id($content_id): void
    {
        Assert::string($content_id, 'content_id');
        $this->content_id = $content_id;
    }
    /**
     * Retrieve the content id from a Attachment object
     *
     * @return string
     */
    public function get_content_id()
    {
        return $this->content_id;
    }
    /**
     *  Verifies whether or not the provided string is a valid base64 string
     *
     * @param $string string The string that has to be checked
     */
    private function is_base64($string): bool
    {
        $decoded_data = base64_decode((string) $string, true);
        $encoded_data = base64_encode($decoded_data);
        if ($encoded_data != $string) {
            return false;
        }
        return true;
    }
    /**
     * Return an array representing a Attachment object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['content' => $this->get_content(), 'type' => $this->get_type(), 'filename' => $this->get_filename(), 'disposition' => $this->get_disposition(), 'content_id' => $this->get_content_id()], fn(string $value) => $value !== null) ?: null;
    }
}