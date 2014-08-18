<?php
/**
 * @package WP_MinCat
 */

class WP_MinCat_Scripts {

    /* Properties
    ---------------------------------------------------------------------------------- */

    /* Footer Scripts
    ---------------------------------------------- */

    /**
     * All scripts loaded in the footer.
     *
     * @var array
     */
    private $footer_scripts = array();

    /* Head Scripts
    ---------------------------------------------- */

    /**
     * All scripts loaded in the head.
     *
     * @var array
     */
    private $head_scripts = array();

    /* Instance
    ---------------------------------------------- */

    /**
     * Instance of the class.
     *
     * @var WP_MinCat_Scripts
     */
    protected static $instance = null;

    /**
     * Get accessor method for instance property.
     *
     * @return WP_MinCat_Scripts Instance of the class.
     */
    public static function get_instance() {

        if ( null == self::$instance ) {

            self::$instance = new self;

        }

        return self::$instance;

    }

    /* Constructor
    ---------------------------------------------------------------------------------- */

    /**
     * Initialize class.
     */
    public function __construct() {

        /* JavaScript
        ---------------------------------------------- */

        // All scripts.
        add_action( 'script_loader_src',  array( $this, 'remove_normal_wordpress_scripts' ), PHP_INT_MAX );
        add_action( 'wp_print_scripts',  array( $this, 'process_scripts' ), PHP_INT_MAX );

        // Head scripts.
        add_filter( 'print_head_scripts',  array( $this, 'disable_normal_wordpress_scripts_print' ), PHP_INT_MAX );

        // Footer scripts.
        add_filter( 'print_footer_scripts',  array( $this, 'disable_normal_wordpress_scripts_print' ), PHP_INT_MAX );
        add_action( 'wp_print_footer_scripts',  array( $this, 'print_mincat_footer_scripts' ), PHP_INT_MAX );

    }

    /* Methods
    ---------------------------------------------------------------------------------- */

    public function disable_normal_wordpress_scripts_print() {

        // Disable the scripts WordPress normally prints out.
        return false;

    }

    public function print_mincat_footer_scripts() {

        // Print footer scripts.
        $this->mincat_and_print_scripts( $this->footer_scripts, true );

    }

    public function process_scripts() {

        global $wp_scripts;

        // Wait until scripts have been broken out into their head and footer groups.
        if ( $wp_scripts->group ) {

            // Get head and footer scripts.
            $this->head_scripts = $wp_scripts->done;
            $this->footer_scripts = $wp_scripts->in_footer;

            // Print head scripts.
            $this->mincat_and_print_scripts( $this->head_scripts );

        }

    }

    function remove_normal_wordpress_scripts() {

        /*
         * Forcibly remove normal WordPress scripts being printed because the
         * `print_footer_scripts` and `print_head_scripts` don't actually seem to work.
         */

        return '';

    }

    /* Helpers
    ---------------------------------------------------------------------------------- */

    private function get_scripts_content( $script_handles ) {

        global $wp_scripts;

        $contents = '';

        foreach ( $script_handles as $handle ) {

            // Look up registered script using the handle.
            $script = $wp_scripts->registered[ $handle ];

            // Get the scripts source.
            $src = $script->src;

            // Make sure the script has a source, most notably, `jquery` doesn't have a source.
            if ( empty( $src ) ) {

                // No source content to get, so onward!
                continue;

            }

            // TODO: See if we can get script contents without turning them into urls, as local file path reads should be faster.

            // Check source is a valid url.
            if ( ! filter_var( $src, FILTER_VALIDATE_URL ) ) {

                // Source appears to be a relative path, so make it legit url.
                $src = $wp_scripts->base_url . $src;

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

    private function mincat_and_print_scripts( $script_handles, $async = false ) {

        // Assume files are already minified.
        $suffix = '.min';

        // Check if we're in debug mode.
        if ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) {

            // Scripts are not minifieid.
            $suffix = '';

        }

        // TODO: Need to include version numbers in the reidentifiable slug.

        // Create a reidentifiable slug of all the scripts included.
        $header_scripts_slug = implode( '.', $script_handles );

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
        $header_scripts_filename = trailingslashit( $mincat_dir ) . $header_scripts_md5 . $suffix . '.js';

        // Check if the file already exists so we can skip recreating it every time and just serve the existing one.
        if ( ! file_exists( $header_scripts_filename ) ) {

            // Get all of the scripts contents.
            $scripts_contents = $this->get_scripts_content( $script_handles );

            // Write the contents to a file.
            file_put_contents( $header_scripts_filename , $scripts_contents );

        }

        // TODO: I don't like hardcoding a `/wp-content` path, so see if we can be more smart about that.
        // TODO: Reactor to resue `$header_scripts_md5 . $suffix . '.js'` from above when creating `$header_scripts_filename`.

        // Get relative source path to mincat file.
        $src = '/wp-content/mincat/' . $header_scripts_md5 . $suffix . '.js';

        // Assume async is not enabled.
        $async_string = '';

        // Check async flag.
        if ( $async ) {

            // Load script asynchronously.
            $async_string = ' async';

        }

        // Add script tag for mincat file.
        echo '<script type="text/javascript" src="' . $src . '"' . $async_string . '></script>' . PHP_EOL;

    }

}
