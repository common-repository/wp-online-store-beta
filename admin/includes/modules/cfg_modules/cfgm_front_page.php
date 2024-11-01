<?php
/*
  $Id cfgm_front_page.php v1.0 20101110 Kymation$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_front_page {
    var $code = 'front_page';
    var $directory;
    var $language_directory = DIR_FS_CATALOG_LANGUAGES;
    var $key = 'MODULE_FRONT_PAGE_INSTALLED';
    var $title;
    var $template_integration = true;

    function cfgm_front_page() {
      $this->directory = DIR_FS_CATALOG_MODULES . 'front_page/';
      $this->title = MODULE_CFG_MODULE_FRONT_PAGE_TITLE;
    }
  }
?>
