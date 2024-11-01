<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_CATALOG,
    'apps' => array(
      array(
        'code' => FILENAME_CATEGORIES,
        'title' => BOX_CATALOG_CATEGORIES_PRODUCTS,
        'link' => tep_href_link(FILENAME_CATEGORIES)
      ),
      array(
        'code' => FILENAME_PRODUCTS_SORTER,
        'title' => BOX_CATALOG_PRODUCTS_SORTER,
        'link' => tep_href_link(FILENAME_PRODUCTS_SORTER)
      ),
	  array(
        'code' => FILENAME_EXTRA_FIELDS,
        'title' => BOX_CATALOG_PRODUCTS_EXTRA_FIELDS,
        'link' => tep_href_link(FILENAME_EXTRA_FIELDS)
      ),
      
      array(
        'code' => FILENAME_PRODUCTS_ATTRIBUTES,
        'title' => BOX_CATALOG_CATEGORIES_PRODUCTS_ATTRIBUTES,
        'link' => tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES)
      ),
      array(
        'code' => 'easypopulate.php',
        'title' => 'Easy Populate',
        'link' => tep_href_link('easypopulate.php')
      ),	  
      array(
        'code' => FILENAME_MANUFACTURERS,
        'title' => BOX_CATALOG_MANUFACTURERS,
        'link' => tep_href_link(FILENAME_MANUFACTURERS)
      ),
      array(
        'code' => FILENAME_REVIEWS,
        'title' => BOX_CATALOG_REVIEWS,
        'link' => tep_href_link(FILENAME_REVIEWS)
      ),
      array(
        'code' => FILENAME_SPECIALS,
        'title' => BOX_CATALOG_SPECIALS,
        'link' => tep_href_link(FILENAME_SPECIALS)
      ),
// Discount Code 2.6 - start
array(
'code' => FILENAME_DISCOUNT_CODES,
'title' => BOX_CATALOG_DISCOUNT_CODES,
'link' => tep_href_link(FILENAME_DISCOUNT_CODES)
),
// Discount Code 2.6 - end	  
      array(
        'code' => FILENAME_PRODUCTS_EXPECTED,
        'title' => BOX_CATALOG_PRODUCTS_EXPECTED,
        'link' => tep_href_link(FILENAME_PRODUCTS_EXPECTED)
      )
    )
  );
?>
