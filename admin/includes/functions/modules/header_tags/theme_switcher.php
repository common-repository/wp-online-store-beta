<?php

/*
  $Id: theme_switcher.php v1.1 20110103 Kymation $
  $Loc: catalog/admin/includes/functions/modules/theme_switcher/ $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  ////
  // Multi-level array sort function (From the PHP manual)
  if (!function_exists('array_msort')) {
    function array_msort( $array, $cols ) {
      $colarr = array ();
      foreach ($cols as $col => $order) {
        $colarr[$col] = array ();
        foreach ($array as $k => $row) {
          $colarr[$col]['_' . $k] = strtolower($row[$col]);
        }
      }
      $params = array ();
      foreach ($cols as $col => $order) {
        $params[] = & $colarr[$col];
        $order = (array) $order;
        foreach ($order as $order_element) {
          //pass by reference, as required by php 5.3
          $params[] = & $order_element;
        }
      }
      call_user_func_array('array_multisort', $params);
      $ret = array ();
      $keys = array ();
      $first = true;
      foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
          if ($first) {
            $keys[$k] = substr($k, 1);
          }
          $k = $keys[$k];

          if (!isset ($ret[$k])) {
            $ret[$k] = $array[$k];
          }

          $ret[$k][$col] = $array[$k][$col];
        }
        $first = false;
      }
      return $ret;
    }
  }

  ////
  // Get a list of the files or directories in a directory
  if (!function_exists('tep_get_directory_list')) {
    function tep_get_directory_list($directory, $file = true, $exclude = array ()) {
      $d = dir($directory);
      $list = array ();
      while ($entry = $d->read()) {
        if ($file == true) { // We want a list of files, not directories
          $parts_array = explode('.', $entry);
          $extension = $parts_array[1];
          // Don't add files or directories that we don't want
          if ($entry != '.' && $entry != '..' && $entry != '.htaccess' && $extension != 'php') {
            if (!is_dir($directory . "/" . $entry)) {
              $list[] = $entry;
            }
          }
        } else { // We want the directories and not the files
          if (is_dir($directory . "/" . $entry) && $entry != '.' && $entry != '..') { // && $entry != 'i18n'
            if (count($exclude) == 0 || !in_array($entry, $exclude)) {
              $list[] = array (
                'id' => $entry,
                'text' => $entry
              );
            }
          }
        }
      }
      $d->close();

      $list = array_msort($list, array (
        'id' => SORT_ASC
      ));
      $list = array_values($list);
      sort($list);

      return $list;
    }
  }

  ////
  // Generate a pulldown menu of the available themes
  if (!function_exists('tep_cfg_pull_down_themes')) {
    function tep_cfg_pull_down_themes($theme_name, $key = '') {
      $themes_array = array ();
      $theme_directory = DIR_FS_CATALOG . 'ext/jquery/ui';

      if (file_exists($theme_directory) && is_dir($theme_directory)) {
        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

        $exclude = array (
          'i18n'
        );
        $themes_array = tep_get_directory_list($theme_directory, false, $exclude);
      }

      return tep_draw_pull_down_menu($name, $themes_array, $theme_name);
    }
  }

?>
