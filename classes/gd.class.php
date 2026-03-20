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
class GD
{
    private bool $_abort = false;
    private readonly string $_gd_target_dir;
    private readonly bool $_gd_webp_support;
    private bool|array|null $_gd_image_data = null;
    private array|bool $_gd_image_exif = [];
    private ?int $_gd_image_type = null;
    private bool|\Gd_Image|null $_gd_image_source = null;
    private bool|\Gd_Image|null $_gd_image_output = null;
    private $_gd_image_array = [];
    ##############################################
    public function __construct(string $target_dir, private $_gd_image_max = false, private $_gd_jpeg_quality = 80)
    {
        if (!str_ends_with($target_dir, '/')) {
            $target_dir .= '/';
        }
        $this->_gd_target_dir = $target_dir;
        $this->_gd_webp_support = function_exists('imagecreatefromwebp');
    }
    //=====[ Public ]=======================================
    /**
     * Set GD params false
     */
    public function __destruct()
    {
        $this->gd_clear();
    }
    public function gd_clear(): void
    {
        if ($this->_gd_image_output) {
            imagedestroy($this->_gd_image_output);
        }
        if ($this->_gd_image_source) {
            imagedestroy($this->_gd_image_source);
        }
        $this->_gd_image_output = false;
        $this->_gd_image_source = false;
        $this->_gd_image_data = false;
        $this->_gd_image_exif = [];
    }
    /**
     * Crop image
     *
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     */
    public function gd_crop($x, $y, $w, $h): void
    {
        if ($im = $this->gd_get_current_data()) {
            $oh = imagesy($im);
            $ow = imagesx($im);
            $h = $oh < $h ? $oh : $h;
            $w = $ow < $w ? $ow : $w;
            if ($this->_gd_image_output) {
                imagedestroy($this->_gd_image_output);
            }
            $this->_gd_image_output = imagecreatetruecolor($w, $h);
            if ($this->_gd_image_type == IMAGETYPE_GIF) {
                $trans_index = imagecolortransparent($im);
                if ($trans_index >= 0) {
                    $trans_color = imagecolorsforindex($im, $trans_index);
                    $trans_new = imagecolorallocate($this->_gd_image_output, $trans_color['red'], $trans_color['green'], $trans_color['blue']);
                    imagefill($this->_gd_image_output, 0, 0, $trans_new);
                    imagecolortransparent($this->_gd_image_output, $trans_new);
                }
            } else {
                imagealphablending($this->_gd_image_output, false);
                imagesavealpha($this->_gd_image_output, true);
            }
            imagecopyresampled($this->_gd_image_output, $im, 0, 0, $x, $y, $w, $h, $w, $h);
            $this->_gd_image_array[0] = $w;
            $this->_gd_image_array[1] = $h;
        }
    }
    /**
     * Return image output
     *
     * @return data
     */
    private function gd_get_current_data()
    {
        // Detect what data source we should be using
        // If output is empty, use the source
        return !empty($this->_gd_image_output) ? $this->_gd_image_output : $this->_gd_image_source;
    }
    /**
     * Load file
     *
     * @param string $file
     */
    public function gd_load_file($file): bool
    {
        if (file_exists($file)) {
            $this->_gd_image_data = getimagesize($file);
            $this->_gd_image_exif = $this->_gd_image_data[2] == IMAGETYPE_JPEG && function_exists('exif_read_data') ? @exif_read_data($file) : [];
            if ($this->_gd_image_exif === false) {
                $this->_gd_image_exif = [];
            }
            $this->_gd_image_type = $this->_gd_image_data[2];
            switch ($this->_gd_image_type) {
                case IMAGETYPE_GIF:
                    $this->_gd_image_source = imagecreatefromgif($file);
                    break;
                case IMAGETYPE_JPEG:
                    $this->_allocate_memory();
                    if ($this->_abort) {
                        return false;
                    }
                    $this->_gd_image_source = imagecreatefromjpeg($file);
                    break;
                case IMAGETYPE_PNG:
                    $this->_gd_image_source = imagecreatefrompng($file);
                    imagesavealpha($this->_gd_image_source, true);
                    break;
                case IMAGETYPE_WEBP:
                    if ($this->_gd_webp_support) {
                        $this->_gd_image_source = imagecreatefromwebp($file);
                    } else {
                        return false;
                    }
                    break;
                default:
                    return false;
            }
            return true;
        }
        return false;
    }
    /**
     * Orientate image based on EXIF data
     *
     * @return $im object
     */
    private function gd_orientate($im)
    {
        if (isset($this->_gd_image_exif['Orientation']) && !empty($this->_gd_image_exif['Orientation'])) {
            switch ($this->_gd_image_exif['Orientation']) {
                case 3:
                    return imagerotate($im, 180, 0);
                case 6:
                    return imagerotate($im, -90, 0);
                case 8:
                    return imagerotate($im, 90, 0);
            }
        }
        return $im;
    }
    /**
     * Resize image
     * @param int $resize
     */
    private function gd_resize($resize): bool
    {
        // Resize the image, while maintaining the proportions
        $im = $this->gd_get_current_data();
        if ($im) {
            // Get the existing image details
            $width = imagesx($im);
            $height = imagesy($im);
            // Calculate the resized dimensions
            $x_ratio = $resize / $width;
            $y_ratio = $resize / $height;
            // Perform a few calculations to work out the new (constrained) dimensions
            $proceed = true;
            if ($width <= $resize && $height <= $resize) {
                // no resize needed
                $out_width = $width;
                $out_height = $height;
                $proceed = false;
            } elseif ($x_ratio * $height < $resize) {
                $out_height = ceil($x_ratio * $height);
                $out_width = $resize;
            } else {
                $out_width = ceil($y_ratio * $width);
                $out_height = $resize;
            }
            if ($proceed) {
                // Create the output file and resample
                if ($this->_gd_image_output) {
                    imagedestroy($this->_gd_image_output);
                }
                $this->_gd_image_output = imagecreatetruecolor($out_width, $out_height);
                if ($this->_gd_image_type == IMAGETYPE_GIF) {
                    $trans_index = imagecolortransparent($im);
                    if ($trans_index >= 0) {
                        $trans_color = imagecolorsforindex($im, $trans_index);
                        $trans_new = imagecolorallocate($this->_gd_image_output, $trans_color['red'], $trans_color['green'], $trans_color['blue']);
                        imagefill($this->_gd_image_output, 0, 0, $trans_new);
                        imagecolortransparent($this->_gd_image_output, $trans_new);
                    }
                } else {
                    imagealphablending($this->_gd_image_output, false);
                    imagesavealpha($this->_gd_image_output, true);
                }
                imagecopyresampled($this->_gd_image_output, $im, 0, 0, 0, 0, $out_width, $out_height, $width, $height);
                return true;
            }
        }
        return false;
    }
    /**
     * Save modified file
     *
     * @param bool $resize
     * @return bool
     */
    public function gd_save(string $filename, $resize = false)
    {
        if ($this->_abort) {
            return false;
        }
        // Do we need to resize the file before saving?
        if ($resize || $this->_gd_image_max) {
            $this->gd_resize($resize ?: $this->_gd_image_max);
        }
        $im = $this->gd_get_current_data();
        if ($im) {
            $file = $this->_gd_target_dir . $filename;
            $source = $im;
            $im = $this->gd_orientate($im);
            imageinterlace($im, true);
            $result = false;
            switch ($this->_gd_image_type) {
                case IMAGETYPE_GIF:
                    $result = imagegif($im, $file);
                    break;
                case IMAGETYPE_JPEG:
                    $result = imagejpeg($im, $file, $this->_gd_jpeg_quality);
                    break;
                case IMAGETYPE_PNG:
                    imagesavealpha($im, true);
                    $result = imagepng($im, $file);
                    break;
                case IMAGETYPE_WEBP:
                    if ($this->_gd_webp_support) {
                        $result = imagewebp($im, $file, $this->_gd_jpeg_quality);
                    } else {
                        if ($im !== $source) {
                            imagedestroy($im);
                        }
                        return false;
                    }
                    break;
                default:
                    trigger_error(__METHOD__ . ' - Unknown file type', E_USER_NOTICE);
                    if ($im !== $source) {
                        imagedestroy($im);
                    }
                    return false;
            }
            if ($im !== $source) {
                imagedestroy($im);
            }
            return $result;
        }
        return false;
    }
    //=====[ Private ]=======================================
    /**
     * Calculate and set memory for jpeg
     * Credit to Karolis Tamutis karolis.t_AT_gmail.com
     *
     * @return false
     */
    private function _allocate_memory()
    {
        $this->_abort = false;
        $mem_limit = ini_get('memory_limit');
        if ($mem_limit == -1) {
            return true;
        }
        $suffix = strtoupper(substr($mem_limit, -1));
        if ($suffix === 'G') {
            $mem_limit = (int) $mem_limit * 1024;
        } elseif ($suffix === 'K') {
            $mem_limit = (int) $mem_limit / 1024;
        } else {
            $mem_limit = (int) $mem_limit;
        }
        $bits = $this->_gd_image_data['bits'] ?? 8;
        $channels = $this->_gd_image_data['channels'] ?? 4;
        $memory_needed = round(($this->_gd_image_data[0] * $this->_gd_image_data[1] * $bits * $channels / 8 + 2 ** 16) * 1.65);
        if (function_exists('memory_get_usage') && memory_get_usage() + $memory_needed > $mem_limit * 1024 ** 2) {
            $new_memory_limit = $mem_limit + ceil((memory_get_usage() + $memory_needed - $mem_limit * 1024 ** 2) / 1024 ** 2) . 'M';
            // ini_set may be a disabled function
            if (!function_exists('ini_set')) {
                $this->_abort = true;
                $this->gd_clear();
                return false;
            }
            // check ini_set works
            if (!ini_set('memory_limit', $new_memory_limit)) {
                $this->_abort = true;
                $this->gd_clear();
                return false;
            }
            return true;
        }
    }
}