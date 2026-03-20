<?php

declare (strict_types=1);
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
/**
 * XML controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class XML extends Xml_Writer
{
    ##############################################
    public function __construct($xml_header = true, $indent_string = ' ')
    {
        $this->open_memory();
        $this->set_indent(true);
        $this->set_indent_string($indent_string);
        if ($xml_header) {
            $this->start_document('1.0', 'UTF-8');
        }
    }
    //=====[ Public ]=======================================
    /**
     * Add an array to the element
     *
     * @param array $array
     */
    public function add_array($array): bool
    {
        if (is_array($array)) {
            foreach ($array as $index => $data) {
                if (is_array($data)) {
                    if (!isset($data['value'])) {
                        $this->start_element($index);
                        $this->add_array($data);
                        $this->end_element();
                    } else {
                        $this->set_element($index, $data['value'], $data['attributes'], $data['cdata']);
                    }
                } else {
                    $this->set_element($index, false, $data);
                }
            }
            return true;
        }
        return false;
    }
    /**
     * End element
     * @param bool $full_end
     */
    public function end_element($full_end = true): void
    {
        if ($full_end) {
            parent::full_end_element();
        } else {
            parent::end_element();
        }
    }
    /**
     * Get current document
     *
     * @param bool $flush
     */
    public function get_document($flush = true): string
    {
        $this->end_document();
        return $this->output_memory($flush);
    }
    /**
     * Display XML
     */
    public function output(): void
    {
        Debug::get_instance()->supress();
        header('Content-Type: text/xml');
        echo $this->get_document();
    }
    /**
     * Set an XML element
     *
     * @param string $name
     * @param mixed $value
     * @param string $attributes
     * @param mixed $cdata
     */
    public function set_element($name, $value = null, $attributes = false, $cdata = true): void
    {
        $this->start_element($name, $attributes);
        if ($cdata) {
            $this->write_c_data($value);
        } else {
            $this->text($value);
        }
        $this->end_element(true);
    }
    /**
     * Start a new element
     *
     * @param string $name
     * @param string $attributes
     */
    public function start_element($name, $attributes = false): void
    {
        parent::start_element($name);
        if (is_array($attributes)) {
            foreach ($attributes as $attribute => $value) {
                parent::write_attribute($attribute, $value);
            }
        }
    }
}