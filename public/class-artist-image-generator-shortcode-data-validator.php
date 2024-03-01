<?php
/**
 * Shortcodes Data Validator
 * 
 * @link       https://pierrevieville.fr
 * @since      1.0.18
 * 
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Artist_Image_Generator
 * @subpackage Artist_Image_Generator/public
 * @author     Pierre Viéville <contact@pierrevieville.fr>
 */
class Artist_Image_Generator_Shortcode_Data_Validator {
    public function validateString($value, $possibleValues, $defaultValue)
    {
        $value = strtolower($value);
        return in_array($value, $possibleValues) ? $value : $defaultValue;
    }

    public function validateInt($value, $min, $max, $defaultValue)
    {
        $value = intval($value);
        return ($value >= $min && $value <= $max) ? $value : $defaultValue;
    }

    public function validateSize($size, $possibleSizes, $defaultSize)
    {
        $size = strtolower($size);
        return in_array($size, $possibleSizes) ? $size : $defaultSize;
    }
}