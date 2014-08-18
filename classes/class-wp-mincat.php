<?php
/**
 * @package WP_MinCat
 */

class WP_MinCat {

    /* Properties
    ---------------------------------------------------------------------------------- */

    /**
     * Instance of the class.
     *
     * @var WP_MinCat
     */
    protected static $instance = null;

    /**
     * Get accessor method for instance property.
     *
     * @return WP_MinCat Instance of the class.
     */
    public static function get_instance() {

        if ( null == self::$instance ) {

            self::$instance = new self;

        }

        return self::$instance;

    }

    /* Version
    ---------------------------------------------- */

    /**
     * Version, used for cache-busting of style and script file references.
     *
     * @var string
     */
    protected $version = '0.0.0';

    /**
     * Getter method for version.
     *
     * @return string Plugin version.
     */
    public function get_version() {

        return $this->version;

    }

    /* Methods
    ---------------------------------------------------------------------------------- */

}
