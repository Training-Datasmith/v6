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
 * Archive controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Archive
{
    /**
     * Archive handle
     *
     * @var handle
     */
    private $_archive = false;
    /**
     * Archive contents
     */
    private ?array $_contents = null;
    /**
     * Enabled?
     */
    private bool $_enabled;
    /**
     * Zip class class
     *
     * @var class
     */
    private object $_zip;
    ##############################################
    public function __construct($archive, $create = false)
    {
        $this->_enabled = extension_loaded('zip') ? true : false;
        if ($this->_enabled && !empty($archive)) {
            $this->_zip = new Zip_Archive();
            $this->open($archive, $create);
            return;
        }
    }
    public function __destruct()
    {
        // Automatically close the zip file, writing all changes (if necessary)
        if ($this->_enabled) {
            $this->_close();
        }
    }
    //=====[ Public ]=======================================
    /**
     * Add file to the archive
     *
     * @param array/string $files
     * @param string $string
     * @return bool
     */
    public function add($files, $string = false)
    {
        if ($this->_enabled) {
            if (is_array($files)) {
                foreach ($files as $file) {
                    $this->_add_file((string) $file);
                }
            } else {
                if (!empty($string) && is_string($string)) {
                    return $this->_zip->add_from_string($files, $string);
                }
                return $this->_add_file($files);
            }
        }
        return false;
    }
    /**
     * Get archive contents
     *
     * @return string/false
     */
    public function contents()
    {
        if ($this->_enabled) {
            for ($i = 0; $i < $this->_zip->num_files; ++$i) {
                $this->_contents[$i] = $this->_zip->stat_index($i);
                $comment = $this->_zip->get_comment_index($i);
                if ($comment) {
                    $this->_contents[$i]['comment'] = $comment;
                }
            }
            return !is_null($this->_contents) ? $this->_contents : false;
        }
        return false;
    }
    /**
     * Delete file from archive
     *
     * @param string $filename
     * @return bool
     */
    public function delete($filename)
    {
        if ($this->_enabled && !empty($filename)) {
            $index = $this->_zip->locate_name($filename, ZIPARCHIVE::FL_NODIR);
            if ($index) {
                return $this->delete_index((int) $index);
            }
        }
        return false;
    }
    //=====[ Private ]=======================================
    /**
     * Get error
     */
    private function error(): string
    {
        if ($this->_enabled) {
            switch ($this->_archive) {
                case ZIPARCHIVE::ER_CRC:
                    $message = 'ZIP CRC error';
                    break;
                case ZIPARCHIVE::ER_EXISTS:
                    $message = 'ZIP archive already exists';
                    break;
                case ZIPARCHIVE::ER_INCONS:
                    $message = 'ZIP archive inconsistency';
                    break;
                case ZIPARCHIVE::ER_INVAL:
                    $message = 'Invalid arguments';
                    break;
                case ZIPARCHIVE::ER_MEMORY:
                    $message = 'Memory allocation failure';
                    break;
                case ZIPARCHIVE::ER_MULTIDISK:
                    $message = 'Multi-disk archives are not supported';
                    break;
                case ZIPARCHIVE::ER_NOENT:
                    $message = 'File does not exist';
                    break;
                case ZIPARCHIVE::ER_NOZIP:
                    $message = 'Not a valid ZIP archive';
                    break;
                case ZIPARCHIVE::ER_OPEN:
                    $message = 'Unable to open archive';
                    break;
                case ZIPARCHIVE::ER_READ:
                    $message = 'ZIP file read error';
                    break;
                case ZIPARCHIVE::ER_SEEK:
                    $message = 'ZIP file seek error';
                    break;
                default:
                    $message = 'Unknown error: ' . $this->_archive;
            }
        } else {
            $message = 'ZIP library was not detected in your PHP installation';
        }
        trigger_error($message, E_USER_WARNING);
        $this->_enabled = false;
        return $message;
    }
    /**
     * Extract archive
     *
     * @param string $target Directory
     * @return bool
     */
    public function extract($target)
    {
        if ($this->_enabled && !empty($target)) {
            return $this->_zip->extract_to($target);
        }
        return false;
    }
    /**
     * Open archive
     *
     * @param string $archive
     * @param bool $create
     */
    private function open($archive, $create = false): bool
    {
        if ($this->_enabled) {
            $archive = str_replace('/', '/', $archive);
            if ($create) {
                $this->_archive = $this->_zip->open($archive, ZIPARCHIVE::CREATE);
            } elseif (file_exists($archive)) {
                $this->_archive = $this->_zip->open($archive);
            }
            if ($this->_archive === true) {
                $this->contents();
                return true;
            }
            $this->error();
            return false;
        }
        return false;
    }
    /**
     * Read archive
     *
     * @param string $filename
     * @return bool
     */
    public function read($filename = false)
    {
        if ($this->_enabled && !empty($filename)) {
            $index = $this->_zip->locate_name($filename, ZIPARCHIVE::FL_NODIR);
            if ($index) {
                return $this->_zip->get_from_index((int) $index);
            }
        }
        return false;
    }
    /**
     * Rename archive
     *
     * @param string $filename
     * @param string $new_name
     */
    public function rename($filename, $new_name): bool
    {
        if ($this->_enabled) {
            ## not done yet
        }
        return false;
    }
    /**
     * Revert archive
     *
     * @return bool
     */
    public function revert()
    {
        return $this->_enabled ? $this->_zip->unchange_all() : false;
    }
    /**
     * Adds file to archive.
     *
     * @param string $filename
     *
     * @return bool
     */
    private function _add_file($filename)
    {
        if (!$this->_enabled) {
            return false;
        }
        if (!file_exists($filename)) {
            return false;
        }
        if (is_dir($filename)) {
            return $this->_zip->add_empty_dir($filename);
        }
        return $this->_zip->add_file($filename);
    }
    /**
     * Close archive
     *
     * @return bool
     */
    private function _close()
    {
        return $this->_enabled ? $this->_zip->close() : false;
    }
}