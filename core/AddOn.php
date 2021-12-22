<?php

namespace GravityFormsDateLimiter;

use GFFormsModel;
use GravityFormsDateLimiter\Utilities\Constants;
use GravityFormsDateLimiter\Utilities\Html;
use GravityFormsDateLimiter\Utilities\Prefix;

class AddOn extends \GFAddOn {

    public function __construct() {

        /**
         * @var string Version number of the Add-On
         */
        $this->_version = Constants::get( 'VERSION' );

        /**
         * @var string Gravity Forms minimum version requirement
         */
        $this->_min_gravityforms_version = '2.5';

        /**
         * @var string URL-friendly identifier used for form settings, add-on settings, text domain localization...
         */
        $this->_slug = sanitize_title( Constants::get( 'FILENAME' ) );

        /**
         * @var string Relative path to the plugin from the plugins folder. Example "gravityforms/gravityforms.php"
         */
        $this->_path = Constants::get( 'BASENAME' );

        /**
         * @var string Full path the the plugin. Example: __FILE__
         */
        $this->_full_path = Constants::get( 'PATH' );

        /**
         * @var string URL to the Gravity Forms website. Example: 'http://www.gravityforms.com' OR affiliate link.
         */
        $this->_url = 'https://skape.co/';

        /**
         * @var string Title of the plugin to be used on the settings page, form settings and plugins page. Example: 'Gravity Forms MailChimp Add-On'
         */
        $this->_title = __( 'Gravity Forms Date Limiter', 'skape' );

        /**
         * @var string Short version of the plugin title to be used on menus and other places where a less verbose string is useful. Example: 'MailChimp'
         */
        $this->_short_title = __( 'Date Limiter', 'skape' );

        parent::__construct();
    }

    /**
     * Plugin starting point. Handles hooks and loading of language files.
     */
    public function init() {

        add_filter(	'gform_tooltips', [ $this, 'addTooltips' ] );
        add_action( 'gform_editor_js', [ $this,'editorJavascript' ] );
        add_action( 'gform_enqueue_scripts', [ $this, 'frontendScripts' ], 10, 2 );

        add_filter( 'gform_field_settings_tabs', [ $this, 'editorTab' ], 10, 2 );
        add_action( 'gform_field_settings_tab_content', [ $this, 'editorTabContent' ], 10, 2 );

        parent::init();
    }

    private function dd( ...$args ) {
        echo '<pre>';
        print_r( ...$args );
        echo '</pre>';
        exit;
    }

    /**
     * Prefix, unique-ify and escape DOM ID.
     *
     * @param string $id
     * @return string
     */
    private function domId( string $id ) {
        return esc_attr( Html::uniqueId( Prefix::make( $this->_slug . '_', $id ) ) );
    }

    /**
     * @param null|string $date
     * @param null|string $modifier
     * @return null|string
     */
    private function modfiyDate( $date, $modifier ) {
        if ( !empty( $date ) ) {
            $date = strtotime( $date );

            if ( !empty( $modifier ) ) {
                $date = strtotime( $modifier, $date );
            }

            return date( 'Y-m-d', $date );
        }

        return null;
    }

    private function parseValues( array $data ) {

        $string = implode( '&', array_filter( array_map( function( $input ) {
            $name = $input[ 'name' ] ?? null;
            $value = $input[ 'value' ] ?? null;

            if ( $name ) {
                return $name . '=' . urlencode( $value );
            }

            return null;
        }, $data ) ) );

        parse_str( $string, $output );

        switch ( $output[ 'min' ] ) {
            case 'custom':
                $output[ 'min_date' ] = $this->modfiyDate( $output[ 'min_calendar' ], $output[ 'min_modifier' ] );
                break;
            case 'current':
                $output[ 'min_date' ] = $this->modfiyDate( date( 'Y-m-d' ), $output[ 'min_modifier' ] );
                break;
            default:
                $output[ 'min_date' ] = null;
                break;
        }

        switch ( $output[ 'max' ] ) {
            case 'custom':
                $output[ 'max_date' ] = $this->modfiyDate( $output[ 'max_calendar' ], $output[ 'max_modifier' ] );
                break;
            case 'current':
                $output[ 'max_date' ] = $this->modfiyDate( date( 'Y-m-d' ), $output[ 'max_modifier' ] );
                break;
            default:
                $output[ 'max_date' ] = null;
                break;
        }

        return $output;
    }

    /**
     * Add tool tip translations.
     *
     * @return array
     */
    public function addTooltips() {
        $tooltips[ 'date_limiter.min' ] = esc_html__('When set, any dates before this value will be unavailable.', 'skape' );
        $tooltips[ 'date_limiter.min_modifier' ] = esc_html__('Use this field to add a dynamic date modifier to the minimum value.', 'skape' );

        $tooltips[ 'date_limiter.max' ] = esc_html__('When set, any dates after this value will be unavailable.', 'skape' );
        $tooltips[ 'date_limiter.max_modifier' ] = esc_html__('Use this field to add a dynamic date modifier to the maximum value.', 'skape' );

        $tooltips[ 'date_limiter.days_of_the_week' ] = esc_html__('Only dates that fall on the selected days will be available.', 'skape' );

        return $tooltips;
    }

    /**
     * Javascript for the admin editor
     *
     * @return void
     */
    public function editorJavascript() {
        wp_enqueue_script( $this->_slug . '_backend', Constants::get( 'URL' ) . '/build/js/backend.js', [ 'jquery', 'gform_form_editor', 'jquery-ui-datepicker' ], $this->_version );
        wp_enqueue_style( $this->_slug . '_backend', Constants::get( 'URL' ) . '/build/css/backend.css', [], $this->_version );
    }

    /**
     * Enqueue frontend scripts and localized data.
     *
     * @param array $form
     * @param boolean $is_ajax
     * @return void
     */
    public function frontendScripts( $form, $is_ajax ) {
        $form_id = $form[ 'id' ];
        $fields_data = [];

        foreach( $form[ 'fields' ] as $field ) {

            // Property matches storage key in JS
            if ( property_exists( $field, 'gravityFormsDateLimiter' ) && $field->gravityFormsDateLimiter ) {
                $fields_data[] = [
                    'formId' => $field->formId,
                    'fieldId' => $field->id,
                    'dateLimiter' => $this->parseValues( $field->gravityFormsDateLimiter )
                ];
            }
        }

        if ( count( $fields_data ) ) {
            wp_enqueue_script( $this->_slug . '_frontend', Constants::get( 'URL' ) . '/build/js/frontend.js', [ 'jquery' ], $this->_version );
            wp_localize_script( $this->_slug . '_frontend', 'gravityFormsDateLimiter' . $form_id, [
                'fields' =>  $fields_data
            ] );
        }
    }

    /**
     * Add our custom tab to the editor.
     *
     * @param array $tabs
     * @param array $form
     * @return mixed
     */
    public function editorTab( $tabs, $form ) {
        $tabs[] = [
            'id' => $this->_slug, // Tab unique DOM id.
            'title' => $this->_short_title, // Title displayed on the tab toggle button.
            'toggle_classes' => [], // Classes added to the tab toggle button.
            'body_classes' => [], // Classes added to the tab body.
        ];

        return $tabs;
    }

    /**
     * Render our custom tab content
     * @param array $form
     * @return void
     */
    public function editorTabContent( $form ) {
        ?>
        <li data-date-limiter="min" class="field_setting">
            <div class="gfdl-row">
                <div class="gfdl-col">
                    <?php $id = $this->domId( 'min' ); ?>
                    <label for="<?= $id; ?>" class="section_label">
                        <?php esc_html_e('Minimum Date', 'skape' ); ?>
                        <?php gform_tooltip( 'date_limiter.min' ); ?>
                    </label>
                    <select name="min" id="<?= $id; ?>">
                        <option value="none"><?php esc_html_e( 'No limitations', 'skape' ); ?></option>
                        <option value="current"><?php esc_html_e( 'Current date', 'skape' ); ?></option>
                        <option value="custom"><?php esc_html_e( 'Specific date', 'skape' ); ?></option>
                    </select>
                </div>
                <div class="gfdl-col" data-date-limiter-show-when="min = custom">
                    <?php $id = $this->domId( 'min_calendar' ); ?>
                    <label for="<?= $id; ?>" class="section_label">
                        <?php esc_html_e('Minimum Date Selector', 'skape' ); ?>
                    </label>
                    <input type="date" id="<?= $id; ?>"  name="min_calendar">
                </div>
            </div>
        </li>

        <li data-date-limiter="min_modifier" class="field_setting" data-date-limiter-show-when="min != none">
            <?php $id = $this->domId( 'min' ); ?>
            <label for="<?= $id; ?>" class="section_label">
                <?php esc_html_e('Minimum Date Modifier', 'skape' ); ?>
                <?php gform_tooltip( 'date_limiter.min_modifier' ); ?>
            </label>
            <input type="text" name="min_modifier" id="<?= $id; ?>" placeholder="<?php esc_attr_e( 'i.e. +2 days', 'skape' ); ?>">
        </li>

        <li data-date-limiter="max" class="field_setting">
            <div class="gfdl-row">
                <div class="gfdl-col">
                    <?php $id = $this->domId( 'max' ); ?>
                    <label for="<?= $id; ?>" class="section_label">
                        <?php esc_html_e('Maximum Date', 'skape' ); ?>
                        <?php gform_tooltip( 'date_limiter.max' ); ?>
                    </label>
                    <select name="max" id="<?= $id; ?>">
                        <option value="none"><?php esc_html_e( 'No limitations', 'skape' ); ?></option>
                        <option value="current"><?php esc_html_e( 'Current date', 'skape' ); ?></option>
                        <option value="custom"><?php esc_html_e( 'Specific date', 'skape' ); ?></option>
                    </select>
                </div>
                <div class="gfdl-col" data-date-limiter-show-when="max = custom">
                    <?php $id = $this->domId( 'max_calendar' ); ?>
                    <label for="<?= $id; ?>" class="section_label">
                        <?php esc_html_e('Maximum Date Selector', 'skape' ); ?>
                    </label>
                    <input type="date" id="<?= $id; ?>"  name="max_calendar">
                </div>
            </div>
        </li>

        <li data-date-limiter="max_modifier" class="field_setting" data-date-limiter-show-when="max != none">
            <?php $id = $this->domId( 'min' ); ?>
            <label for="<?= $id; ?>" class="section_label">
                <?php esc_html_e('Maximum Date Modifier', 'skape' ); ?>
                <?php gform_tooltip( 'date_limiter.max_modifier' ); ?>
            </label>
            <input type="text" name="max_modifier" id="<?= $id; ?>" placeholder="<?php esc_attr_e( 'i.e. +1 week', 'skape' ); ?>">
        </li>

        <li data-date-limiter="days_of_the_week" class="field_setting">
            <label class="section_label">
                <?php esc_html_e('Days of the Week', 'skape' ); ?>
                <?php gform_tooltip( 'date_limiter.days_of_the_week' ); ?>
            </label>
            <ol class="gfdl-row gfdl-row-cols-5 gfdl-row-gutter-0" style="padding: 0; margin: 0;">
                <?php foreach( [
                    strtotime( 'last sunday +1 day' ), // Mon
                    strtotime( 'last sunday +2 day' ), // Tue
                    strtotime( 'last sunday +3 day' ), // Wed
                    strtotime( 'last sunday +4 day' ), // Thur
                    strtotime( 'last sunday +5 day' ), // Fri
                    strtotime( 'last sunday +6 day' ), // Sat
                    strtotime( 'last sunday +7 day' ), // Sun
                ] as $day ): ?>
                    <?php $id = $this->domId( 'days_of_the_week-' . $day ); ?>
                    <li style="padding-right: 0;">
                        <input type="checkbox" name="days_of_the_week[]" value="<?= esc_html( date_i18n( 'N', $day ) ); ?>" id="<?= $id; ?>" />
                        <label for="<?= $id; ?>" class="inline">
                            <?= esc_html( date_i18n( 'D', $day ) ); ?>
                        </label>
                    </li>
                <?php endforeach ?>
            </ol>
        </li>
        <?php
    }


}
