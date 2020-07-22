<?php

function formatMoney($number, $fractional=false) {
    if ($fractional) {
        $number = sprintf('%.2f', $number);
    }
    while (true) {
        $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
        if ($replaced != $number) {
            $number = $replaced;
        } else {
            break;
        }
    }
    return $number;
}


class SimpleXMLExtended extends SimpleXMLElement {
    public function addCData($cdata_text) {
        $node = dom_import_simplexml($this); 
        $ownerDocument = $node->ownerDocument; 
        $node->appendChild($ownerDocument->createCDATASection($cdata_text)); 
    } 
}

  

class Gumtree_Query {

    /**
     * Gets property location name
     *
     * @access public
     * @param null $post_id
     * @param string $separator
     * @return bool|string
     */
    public static function get_property_location_name( $post_id = null, $separator = '/' ) {
        static $property_locations;

        if ( $post_id == null ) {
            $post_id = get_the_ID();
        }

        if ( ! empty( $property_locations[$post_id] ) ) {
            return $property_locations[$post_id];
        }

        $locations = wp_get_post_terms( $post_id, 'locations' );

        if ( is_array( $locations ) && count( $locations ) > 0 ) {
            $output = '';

            $locations = array_reverse($locations);

            foreach ( $locations as $key => $location ) {
                $output .= $location->name;

                if ( array_key_exists( $key + 1, $locations ) ) {
                    $output .= ' <span class="separator">' . $separator . '</span> ';
                }
            }

            $property_locations[$post_id] = $output;

            return $output;
        }

        return false;
    }

    /**
     * Gets property contract name
     *
     * @access public
     * @param null $post_id
     * @return bool
     */
    public static function get_property_contract_name( $post_id = null ) {
        if ( $post_id == null ) {
            $post_id = get_the_ID();
        }

        $types = wp_get_post_terms( $post_id, 'contracts' );

        if ( is_array( $types ) && count( $types ) > 0 ) {
            $type = array_shift( $types );
            return $type->name;
        }

        return false;
    }

    /**
     * Gets property type name
     *
     * @access public
     * @param null $post_id
     * @return bool
     */
    public static function get_property_type_name( $post_id = null ) {
        static $property_type_names;

        if ( $post_id == null ) {
            $post_id = get_the_ID();
        }

        if ( ! empty( $property_type_names[$post_id] ) ) {
            return $property_type_names[$post_id];
        }

        $types = wp_get_post_terms( $post_id, 'property_types' );

        if ( is_array( $types ) && count( $types ) > 0 ) {
            $type = array_shift( $types );
            $property_type_names[$post_id] = $type->name;
            return $type->name;
        }

        return false;
    }
}



class Gumtree_Price {
  /**
   * Gets property price
   *
   * @access public
   * @param null $post_id
   * @return bool|string
   */
  public static function get_property_price( $post_id = null ) {
      if ( $post_id == null ) {
          $post_id = get_the_ID();
      }

      $custom = get_post_meta( $post_id, REALIA_PROPERTY_PREFIX . 'price_custom', true );

      if ( ! empty( $custom ) ) {
          return $custom;
      }

      $price = get_post_meta( $post_id, REALIA_PROPERTY_PREFIX . 'price', true );

      if ( empty( $price ) || ! is_numeric( $price ) ) {
          return false;
      }

      return $price;
  }
}