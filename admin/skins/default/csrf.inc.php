<?php

declare(strict_types=1);
/* Map of GET variable that require CSRF check */
$csrf_maps = [
    /* START DELETE */
    ['_g' => 'settings','node' => 'index','action' => 'delete','admin_id' => false], // Delete admin
    ['_g' => 'filemanager','delete' => false], // Delete files & folders
    ['_g' => 'settings','node' => 'geo','delete' => 'country','id' => false], // Delete country
    ['_g' => 'settings','node' => 'geo','delete' => 'zone','id' => false], // Delete zones
    ['_g' => 'customers','action' => 'delete','customer_id' => false], // Delete customer
    ['_g' => 'customers','action' => 'edit','customer_id' => false, 'delete_addr' => false], // Delete address
    ['_g' => 'orders','delete' => false], // Delete order
    ['_g' => 'orders','action' => 'edit','order_id' => false, 'delete-note' => false], // Delete order note
    ['_g' => 'customers','node' => 'email','action' => 'delete','newsletter_id' => false], // Delete newsletter
    ['_g' => 'customers','node' => 'subscribers','delete' => false], // Delete subscriber
    ['_g' => 'categories','delete' => false], // Delete category
    ['_g' => 'products','node' => 'index','delete' => false], // Delete product
    ['_g' => 'products','action' => 'edit','product_id' => false,'delete_review' => false], // Delete product review
    ['_g' => 'products','node' => 'reviews','delete' => false], // Delete review
    ['_g' => 'products','node' => 'options','delete' => 'group','id' => false], // Delete option group
    ['_g' => 'products','node' => 'options','delete' => 'attribute','id' => false], // Delete option attribute
    ['_g' => 'products','node' => 'options','delete' => 'set','id' => false], // Delete option set
    ['_g' => 'products','node' => 'coupons','delete' => false], // Delete promo code
    ['_g' => 'products','node' => 'manufacturers','delete' => false], // Delete manufacturer
    ['_g' => 'documents','delete' => false], // Delete document
    ['_g' => 'documents','node' => 'email','action' => 'delete','type' => 'template','template_id' => false], // Delete email template
    ['_g' => 'settings','node' => 'hooks','delete_snippet' => false], // Delete code snippet
    ['_g' => 'settings','node' => 'currency','delete' => false], // Delete currency
    ['_g' => 'settings','node' => 'tax','delete_class' => false], // Delete tax class
    ['_g' => 'settings','node' => 'tax','delete_detail' => false], // Delete tax detail
    ['_g' => 'settings','node' => 'tax','delete_rule' => false], // Delete tax rule
    ['_g' => 'settings','node' => 'language','delete' => false], // Delete language
    ['_g' => 'plugins','type' => false,'module' => false,'delete' => '1'], // Delete extension
    /* END DELETE */
    ['_g' => 'customers','node' => 'email','action' => 'send','newsletter_id' => false],
    ['_g' => 'logout'],
];
