<?php

/**
 * Class third part from
 * http://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors/
 */
class Application_CliColors {

   protected $foreground_colors = array();
   protected $background_colors = array();
   
   const COLOR_BLACK         = 'black';
   const COLOR_DARK_GRAY     = 'dark_gray';
   const COLOR_GRAY          = 'gray';   
   const COLOR_BLUE          = 'blue';
   const COLOR_LIGHT_BLUE    = 'light_blue';
   const COLOR_GREEN         = 'green';
   const COLOR_LIGHT_GREEN   = 'light_green';
   const COLOR_CYAN          = 'cyan';
   const COLOR_LIGHT_CYAN    = 'light_cyan';
   const COLOR_RED           = 'red';
   const COLOR_LIGHT_READ    = 'light_read';
   const COLOR_PURPLE        = 'purple';
   const COLOR_LIGHT_PURPLE  = 'light_purple';
   const COLOR_BROWN         = 'brown';
   const COLOR_YELLOW        = 'yellow';
   const COLOR_LIGHT_GRAY    = 'light_gray';
   const COLOR_WHITE         = 'white';
   const COLOR_MAGENTO       = 'magento';
   
   public function __construct() 
   {
      // Set up shell colors
      $this->foreground_colors[self::COLOR_BLACK]        = '0;30';
      $this->foreground_colors[self::COLOR_DARK_GRAY]    = '1;30';
      $this->foreground_colors[self::COLOR_BLUE]         = '0;34';
      $this->foreground_colors[self::COLOR_LIGHT_BLUE]   = '1;34';
      $this->foreground_colors[self::COLOR_GREEN]        = '0;32';
      $this->foreground_colors[self::COLOR_LIGHT_GREEN]  = '1;32';
      $this->foreground_colors[self::COLOR_CYAN]         = '0;36';
      $this->foreground_colors[self::COLOR_LIGHT_CYAN]   = '1;36';
      $this->foreground_colors[self::COLOR_RED]          = '0;31';
      $this->foreground_colors[self::COLOR_LIGHT_READ]   = '1;31';
      $this->foreground_colors[self::COLOR_PURPLE]       = '0;35';
      $this->foreground_colors[self::COLOR_LIGHT_PURPLE] = '1;35';
      $this->foreground_colors[self::COLOR_BROWN]        = '0;33';
      $this->foreground_colors[self::COLOR_YELLOW]       = '1;33';
      $this->foreground_colors[self::COLOR_LIGHT_GRAY]   = '0;37';
      $this->foreground_colors[self::COLOR_WHITE]        = '1;37';

      $this->background_colors[self::COLOR_BLACK]        = '40';
      $this->background_colors[self::COLOR_RED]          = '41';
      $this->background_colors[self::COLOR_GREEN]        = '42';
      $this->background_colors[self::COLOR_YELLOW]       = '43';
      $this->background_colors[self::COLOR_BLUE]         = '44';
      $this->background_colors[self::COLOR_MAGENTO]      = '45';
      $this->background_colors[self::COLOR_CYAN]         = '46';
      $this->background_colors[self::COLOR_GRAY]         = '47';
   }

   // Returns colored string
   public function getColoredString($string, $foreground_color = null, $background_color = null) 
   {
      $colored_string = "";

      // Check if given foreground color found
      if (isset($this->foreground_colors[$foreground_color])) {
         $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
      }
      // Check if given background color found
      if (isset($this->background_colors[$background_color])) {
         $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
      }

      // Add string and end coloring
      $colored_string .= $string . "\033[0m";

      return $colored_string;
   }

   public function printColoredString($string, $foreground_color = null, $background_color = null)
   {
      echo PHP_EOL.$this->getColoredString($string, $foreground_color, $background_color);
   }
   
   
   // Returns all foreground color names
   public function getForegroundColors() 
   {
      return array_keys($this->foreground_colors);
   }

   // Returns all background color names
   public function getBackgroundColors() 
   {
      return array_keys($this->background_colors);
   }

}
