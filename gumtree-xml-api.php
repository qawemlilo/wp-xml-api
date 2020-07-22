<?php

/*
   Plugin Name: Gumtree XML API
   Plugin URI: https://github.com/qawemlilo/wp-xml-api/
   Version: 0.1
   Author: Qawelesizwe Mlilo
   Description: Gumtree XML API
   Text Domain: gumtree-xml-api
   License: GPLv3
  */

$GumtreeXmlApi_minimalRequiredPhpVersion = '5.0';


include_once('Gumtree_Query.php');


/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function GumtreeXmlApi_noticePhpVersionWrong() {
    global $GumtreeXmlApi_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
      __('Error: plugin "Gumtree XML API" requires a newer version of PHP to be running.',  'gumtree-xml-api').
            '<br/>' . __('Minimal version of PHP required: ', 'gumtree-xml-api') . '<strong>' . $GumtreeXmlApi_minimalRequiredPhpVersion . '</strong>' .
            '<br/>' . __('Your server\'s PHP version: ', 'gumtree-xml-api') . '<strong>' . phpversion() . '</strong>' .
         '</div>';
}



function gumtree_feed() {
    $args = [
        'numberposts' => 99999,
        'post_type' => 'property',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];

    $posts = get_posts($args);
        
    $xml = new SimpleXMLExtended("<listings xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"/>");

    foreach($posts as $post) {
        $property = $xml->addChild('Property');

        $property->addChild("UniqueID", "$post->ID");
        $property->addChild("Reference", "$post->ID");
        $property->addChild("CreatedOn", "$post->post_date");
        $property->addChild("UpdatedOn", "$post->post_modified");
        $property->addChild("PropertyState", "Active");
        
        $location = Gumtree_Query::get_property_location_name($post->ID,",");
        $property->addChild("City", "$location");
        $property->addChild("Province", "Western Cape");

        $price = Gumtree_Price::get_property_price($post->ID);
        $property->addChild("Price", "$price");

        $contract = Gumtree_Query::get_property_contract_name($post->ID);
        $property->addChild("status", "$contract");

        $listingType = Gumtree_Query::get_property_type_name($post->ID);
        $property->addChild("ListingType", "$listingType");

        $bedrooms = get_post_meta($post->ID, REALIA_PROPERTY_PREFIX . 'attributes_beds', true );
        $property->addChild("Bedrooms", "$bedrooms");

        $bathrooms = get_post_meta($post->ID, REALIA_PROPERTY_PREFIX . 'attributes_baths', true );
        $property->addChild("Bathrooms", "$bathrooms");

        $property->Heading = NULL; 
        $property->Heading->addCData("$post->post_title");

        $property->Description = NULL; 
        $property->Description->addCData("$post->post_content");

        $thumbnail_id = get_post_thumbnail_id( $post->ID );
        $thumbnailMedium = wp_get_attachment_image_src( $thumbnail_id, 'medium' );
        $imgUrl = wp_get_attachment_image_src( $thumbnail_id, 'full');
        $imgID = basename($imgUrl[0]);

        $property->Images = NULL;
        $property->Images->Image = NULL;
        $property->Images->Image->addChild("ImageID", "$imgID");
        $property->Images->Image->addChild("ImageURL", "$imgUrl[0]");
        $property->Images->Image->addChild("TempImageURL", "$thumbnailMedium[0]");
    }

    Header('Content-type: text/xml');
    echo $xml->asXML();
}


function GumtreeXmlApi_PhpVersionCheck() {
    global $GumtreeXmlApi_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $GumtreeXmlApi_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'GumtreeXmlApi_noticePhpVersionWrong');
        return false;
    }
    return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function GumtreeXmlApi_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('gumtree-xml-api', false, $pluginDir . '/languages/');
}

function gumtree_xml_feed() {
    add_feed('gumtree-xml', 'gumtree_feed');
}


//////////////////////////////////
// Run initialization
/////////////////////////////////


function load_gumtree_feed() {
    add_feed('gumtree', 'gumtree_feed');
}


// Initialize i18n
add_action('plugins_loadedi','GumtreeXmlApi_i18n_init');

add_action('init','load_gumtree_feed');



// Run the version check.
// If it is successful, continue with initialization for this plugin
if (GumtreeXmlApi_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('gumtree-xml-api_init.php');
    GumtreeXmlApi_init(__FILE__);
}
