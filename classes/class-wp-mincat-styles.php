<?php
/**
 * @package WP_MinCat
 */

class WP_MinCat_Styles {

    /* Properties
    ---------------------------------------------------------------------------------- */

    /* Instance
    ---------------------------------------------- */

    /**
     * Instance of the class.
     *
     * @var WP_MinCat_Styles
     */
    protected static $instance = null;

    /**
     * Get accessor method for instance property.
     *
     * @return WP_MinCat_Styles Instance of the class.
     */
    public static function get_instance() {

        if ( null == self::$instance ) {

            self::$instance = new self;

        }

        return self::$instance;

    }

    /* Styles
    ---------------------------------------------- */

    /**
     * All styles.
     *
     * @var array
     */
    private $styles = array();
    private $styles_loaded = false;

    /* Constructor
    ---------------------------------------------------------------------------------- */

    /**
     * Initialize class.
     */
    public function __construct() {

        // Head.
//        add_action( 'wp_print_styles', array( $this, 'process_styles' ), PHP_INT_MAX );
//        add_filter( 'style_loader_src', array( $this, 'remove_normal_wordpress_styles' ), PHP_INT_MAX );

        // Footer.
//        add_filter( 'print_late_styles', array( $this, 'my_print_late_styles' ), PHP_INT_MAX );

        // Other...
//        add_action( 'wp_default_styles', array( $this, 'my_wp_default_styles' ), PHP_INT_MAX, 10 );
        add_filter( 'print_styles_array', array( $this, 'my_print_styles_array' ), PHP_INT_MAX );
//        add_filter( 'style_loader_tag', array( $this, 'my_style_loader_tag' ), PHP_INT_MAX, 10 );
//        add_action( 'wp_head', array( $this, 'my_wp_head' ), PHP_INT_MAX );

    }

    /* Methods
    ---------------------------------------------------------------------------------- */

    public function process_styles() {

        global $wp_styles;

        $i = 0;

    }

    public function remove_normal_wordpress_styles() {

        /*
         * Forcibly remove normal WordPress styles being printed since there is no WordPress hook
         * to exclude styles like you can with scripts.
         */

        return '';

    }

    public function my_print_late_styles( $one ) {

        global $wp_styles;

        $i = 0; // `$one` comes in as `true`, for whatever reason.

        return false;

    }

    function my_wp_default_styles( $one, $two, $three, $four, $five, $six, $seven, $eight, $nine, $ten ) {

        global $wp_styles;

        $i = 0; // Doesn't really seem necessary... First the first of all the hooks.

    }

    function my_print_styles_array( $style_handles ) {

        global $wp_styles;

        $i = 0; // `$one` is an array of slugs, but for some reason, WordPress calls it twice. But dependencies have been ordered correctly by now.

        $this->styles = $style_handles;

        if ( ! $this->styles_loaded ) {

            $this->mincat_and_print_styles( $this->styles );

            $this->styles_loaded = true;

        }

        $wp_styles->queue = array();

        return array();

    }

    function my_style_loader_tag( $one, $two, $three, $four, $five, $six, $seven, $eight, $nine, $ten ) {

        global $wp_styles;

        $i = 0; // Doesn't get called for some reason.

    }

    function my_wp_head() {

        global $wp_styles;

        $i = 0;

        $this->mincat_and_print_styles( $wp_styles->done );

    }

    /* Helpers
    ---------------------------------------------------------------------------------- */

    private function get_styles_content( $style_handles ) {

        global $wp_styles;

        $contents = '';

        foreach ( $style_handles as $handle ) {

            // Look up registered script using the handle.
            $script = $wp_styles->registered[ $handle ];

            // Get the scripts source.
            $src = $script->src;

            // Make sure the script has a source, most notably, `jquery` doesn't have a source.
            if ( empty( $src ) ) {

                // No source content to get, so onward!
                continue;

            }

            // TODO: See if we can get script contents without turning them into urls, as local file path reads should be faster.

            // Check for schemeless url.
            if ( '//' === substr( $src, 0, 2 ) ) {

                // Get scheme.
                $scheme = ( is_ssl() ) ? 'https:' : 'http:';

                // Add scheme.
                $src = $scheme . $src;

            }

            // Check source is a valid url.
            if ( ! filter_var( $src, FILTER_VALIDATE_URL ) ) {

                // Source appears to be a relative path, so make it legit url.
                $src = $wp_styles->base_url . $src;

                // Check source is a valid url.
                if ( ! filter_var( $src, FILTER_VALIDATE_URL ) ) {

                    // Nope, still not a good url, so on to the next.
                    continue;

                }
            }

            // Get the contents of the source.
            $src_contents = wp_remote_get( $src );

            // Append source contents to all the scripts contents.
            $contents .= wp_remote_retrieve_body( $src_contents );

        }

        return $contents;

    }

    private function mincat_and_print_styles( $style_handles ) {

        // Assume files are already minified.
        $suffix = '.min';

        // Check if we're in debug mode.
        if ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) {

            // Scripts are not minifieid.
            $suffix = '';

        }

        // TODO: Need to include version numbers in the reidentifiable slug.

        // Create a reidentifiable slug of all the styles included.
        $header_scripts_slug = implode( '.', $style_handles );

        // Create a shorter, reidentifiable slug of all the scripts included, just in case there are a slew of scripts.
        $header_scripts_md5 = md5( $header_scripts_slug );

        // Get path to mincat directory, where mincat files are stored.
        $mincat_dir = trailingslashit( WP_CONTENT_DIR ) . 'mincat';

        // Check if mincat directory exists.
        if ( ! file_exists( $mincat_dir ) ) {

            // The mincat directory does not exist, so create it.
            wp_mkdir_p( $mincat_dir );

        }

        // Get the filename of the scripts included in this head.
        $header_scripts_filename = trailingslashit( $mincat_dir ) . $header_scripts_md5 . $suffix . '.css';

        // Check if the file already exists so we can skip recreating it every time and just serve the existing one.
        if ( ! file_exists( $header_scripts_filename ) ) {

            // Get all of the scripts contents.
            $scripts_contents = $this->get_styles_content( $style_handles );

            // Write the contents to a file.
            file_put_contents( $header_scripts_filename , $scripts_contents );

        }

        // TODO: I don't like hardcoding a `/wp-content` path, so see if we can be more smart about that.
        // TODO: Reactor to resue `$header_scripts_md5 . $suffix . '.js'` from above when creating `$header_scripts_filename`.

        // Get relative source path to mincat file.
        $src = '/wp-content/mincat/' . $header_scripts_md5 . $suffix . '.css';

        // Add script tag for mincat file.
        echo '<link rel="stylesheet" href="' . $src . '" type="text/css" media="all" />' . PHP_EOL;

    }

}
