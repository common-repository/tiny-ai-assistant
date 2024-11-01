<?php
/**
 * Plugin Name: Tiny AI Assistant
 * Plugin URI: https://tiny-ai-assistant.aichatbot.hu
 * Version: 1.1
 * Author: PDK Ltd.
 * Author URI: https://webdesign-honlapkeszites.hu
 * Description: A plugin that integrate AI services into TinyMCE Editor
 * License: GPLv3
 * Text Domain: tiny-ai-assistant
 */
//require __DIR__ . '/vendor/autoload.php'; // remove this line if you use a PHP Framework.

 class tinyAIEX {

    private $available_options = array() ;

    private $tiny_options = array() ;
                                      
    private $new_commands = array() ;

    private $comm_class_dir = "curl" ;

    private $licence_key = "" ;

    private $temperature = 0 ;

    private $model = 0 ;

    private $openai_api_key = "" ;    

    private $mode = "free" ;

    /**
    * Constructor. Called when the plugin is initialised.
    */
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'init', array(  $this, 'setup_tinyAIEX_plugin' ) );
            add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), array(  $this, 'tinyAIEX_add_plugin_page_settings_link') );      
            add_filter( 'plugins_api',  array(  $this, 'tinyAIEX_plugin_api_call'), 10, 3 );          
            add_action( 'wp_ajax_tinyAIEX_command', 'tinyAIEX_command' );
            add_action( 'wp_ajax_tinyAIEX_add_command', 'tinyAIEX_add_command' );
            add_action( 'wp_ajax_tinyAIEX_remove_commands', 'tinyAIEX_remove_commands' );
            add_action( 'admin_menu', array(  $this, 'add_tinyAIEX_settings_page' ) );
            add_action( 'admin_init', array(  $this, 'tinyAIEX_register_settings' ) );    
            add_action( 'admin_print_footer_scripts', array(  $this, 'tinyAIEX_custom_script' ) );
            add_action( 'admin_enqueue_scripts', array(  $this, 'load_custom_wp_admin_style_script') );       
            $this->load_dependencies();
        }         
    }

    public function tinyAIEX_plugin_api_call( $def, $action, $args ) {
        $api_url     = 'https://construct.pdk.hu/tiny-ai-assistant/update/';
        $plugin_slug = 'tiny-ai-assistant';
        $main_php = 'tinyAIEX' ;

        if ( $action !== 'plugin_information' ) {
            return false;
        }

        if ( (string) $args->slug !== (string) $plugin_slug ) {
            return $def;
        }

        // Get the current version
        $plugin_info     = get_site_transient( 'update_plugins' );
        $current_version = $plugin_info->checked[ $plugin_slug . '/' . $main_php . '.php' ];
        $args->version   = $current_version;

        $request_string = $this->tinyAIEX_prepare_request( $action, $args );

        $request = wp_remote_post( $api_url, $request_string );

        if ( is_wp_error( $request ) ) {
            $res = new WP_Error( 'plugins_api_failed', esc_html__( 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>' ), $request->get_error_message() );
        } else {
            $res = unserialize( $request['body'] );

            if ( $res === false ) {
                $res = new WP_Error( 'plugins_api_failed', esc_html__( 'An unknown error occurred' ), $request['body'] );
            }
        }

        return $res;
    }

    public function tinyAIEX_prepare_request( $action, $args ) {
        global $wp_version;

        return [
            'body'       => [
                'action'  => $action,
                'request' => serialize( $args ),
                'api-key' => md5( get_bloginfo( 'url' ) ),
            ],
            'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
        ];
    }

    public function tinyAIEX_custom_script() {
        $options = get_option( 'tinyaiex_settings' );
        $selected_options_detailed = array() ;
        $selected_options = array() ;
        if (isset($options['tiny_options'])) {
            $selected_options = $options['tiny_options'] ;
        }
        else
        {
            $selected_options = $this->tiny_options;
        }
        if (isset($options["available_options"])) {
            $available_options = $options["available_options"] ;        
        }
        else
        {
            $available_options = $this->available_options ;
        }
        if (count($selected_options) > 0) {
            foreach ($selected_options as $selected_option) {
                $l = false ;
                $i = 0 ;
                while (!$l && $i < count($available_options)) {
                    if ($available_options[$i]["name"] == $selected_option) {
                        $selected_options_detailed[] = array("name" => $selected_option, "title" => $available_options[$i]["title"]) ;
                        $l = true ;
                    }
                    $i++ ;
                }
            }
        }
        echo "<script>" ;
        echo "var tinyaiex_plugin_url = '" . esc_js(plugin_dir_url( __FILE__ )) . "';" ;
        echo "var tinyaiex_plugin_options = '" . wp_json_encode($selected_options_detailed) . "';" ;
        echo "var tinyaiex_ajax_nonce = '" . esc_js(wp_create_nonce('tinyaiex-ajax-nonce')) . "';" ;
        echo "var tinyaiex_mode = '" . esc_js($this->mode) . "';" ;
        echo 'let tinyaiex_plugin_texts = {tinyaiex_text_ai_tools:"' . esc_html__('AI Tools', 'tiny-ai-assistant') . '", tinyaiex_text_available_ai_tools:"' . esc_html__('Available AI tools - you can set these on settings page', 'tiny-ai-assistant') . '", tinyaiex_text_undo:"' . esc_html__('Undo last AI text modification', 'tiny-ai-assistant') . '"}';        
        echo "</script>";
    }

    private function load_dependencies() {
        require_once(dirname(__FILE__) . "/classes/" . $this->comm_class_dir . "/tinyaiex_openai.class.php");
        require_once(dirname(__FILE__) . "/classes/gpt3-encoder/gpt3-encoder.php");
    }

    public function get_licence_key() {
        return $this->licence_key ;
    }

    public function get_openai_api_key() {
        return $this->openai_api_key ;
    }

    public function get_temperature() {
        return $this->temperature ;
    }

    public function get_model() {
        return $this->model ;
    }
    
    /**
    * Check if the current user can edit Posts or Pages, and is using the Visual Editor
    * If so, add some filters so we can register our plugin
    */
    public function setup_tinyAIEX_plugin() {
    

        // Check if the logged in WordPress User can edit Posts or Pages
        // If not, don't register our TinyMCE plugin
            
        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
                    return;
        }
        
        // Check if the logged in WordPress User has the Visual Editor enabled
        // If not, don't register our TinyMCE plugin
        if ( get_user_option( 'rich_editing' ) !== 'true' ) {
        return;
        }    

        $options = get_option( 'tinyaiex_settings' );        
        
        if (isset($options['licence_key'])) {
            $this->licence_key = $options['licence_key'] ;
        }
        if (isset($options['openai_api_key'])) {
            $this->openai_api_key = $options['openai_api_key'] ;
        }

        $this->mode = "free" ;
        if (isset($this->licence_key)) {
            $account_data = $this->get_account_data() ;
            if (isset($account_data) && $account_data == "ok") {
                $this->mode = "premium" ;
            }
        }        

        
        if (!isset($options) || $options == null) {
            $this->available_options = array(
                array("name" => "command", "title" => esc_html__('Execute custom prompt', 'tiny-ai-assistant'), "cmd" => ""),
                array("name" => "rephrase", "title" => esc_html__('Rephrase content', 'tiny-ai-assistant'), "cmd" => esc_html__('Rephrase this text: ', 'tiny-ai-assistant')),
                array("name" => "excerpt", "title" => esc_html__('Make summary', 'tiny-ai-assistant'), "cmd" => esc_html__('Make summary: ', 'tiny-ai-assistant')),
            ) ;
            $this->tiny_options = array("command", "rephrase", "excerpt") ;

            $options = array("available_options" => $this->available_options, "tiny_options" => $this->tiny_options) ;
            update_option('tinyaiex_settings', $options) ;
        }        
        if (isset($options['openai_api_key'])) {
            $this->openai_api_key = $options['openai_api_key'] ;
        }
        if (isset($options['licence_key'])) {
            $this->licence_key = $options['licence_key'] ;
        }
        $models = array("gpt-3.5-turbo") ;
        $this->model = "gpt-3.5-turbo" ;
        
        if ($this->mode == "premium") {
            if (isset($options["temperature"])) {
                $this->temperature = $options["temperature"] ;
            }
            if (isset($options["model"])) {
                $this->model = $options["model"] ;
            }       
            $models[] = "gpt-4-1106-preview" ;
        }
        $this->models = $models ;

        // Setup some filters
        add_filter( 'mce_external_plugins', array( &$this, 'add_tinyAIEX_plugin' ), 1, 2 );
        add_filter( 'mce_buttons_2', array( &$this, 'add_tinyAIEX_toolbar_button' )  ) ;
        load_plugin_textdomain( 'tiny-ai-assistant', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );  
    }    
    
    public function tinyAIEX_add_plugin_page_settings_link( $links ) {
        $links[] = '<a href="' .
            admin_url( 'options-general.php?page=tinyAIEX-settings' ) .
            '">' . esc_html__('Settings', 'tiny-ai-assistant') . '</a>';
        return $links;
    }

    /**
    * Adds a TinyMCE plugin compatible JS file to the TinyMCE / Visual Editor instance
    *
    * @param array $plugin_array Array of registered TinyMCE Plugins
    * @return array Modified array of registered TinyMCE Plugins
    */
    public function add_tinyAIEX_plugin( $plugin_array ) {
    
        $plugin_array['ai_tools_menu'] = plugin_dir_url( __FILE__ ) . 'js/tinyAIEX.js';
        $plugin_array['loadingoverlay'] = plugin_dir_url( __FILE__ ) . 'js/loadingoverlay/loadingoverlay.min.js';
        return $plugin_array;        
    }     


    /**
    * Adds a button to the TinyMCE / Visual Editor which the user can click
    * to insert a link with a custom CSS class.
    *
    * @param array $buttons Array of registered TinyMCE Buttons
    * @return array Modified array of registered TinyMCE Buttons
    */
    public function add_tinyAIEX_toolbar_button( $buttons ) {
    
        array_push( $buttons, 'ai_tools_menu', 'ai_tools_undo' );
        return $buttons;
    }    
 

    public function get_account_data() {
        global $tinyaiex;
        $openai_obj = new tinyaiex_openai($tinyaiex->get_licence_key()) ;
        $answer_response = $openai_obj->getAccountData() ;        
        $answer = json_decode($answer_response) ;  
        $account_data = "" ;                
        if (isset($answer) && $answer->status == 1) {
            $account_data = $answer->message ;
        }
        else
        {
            $account_data = "free" ;
        }
        return $account_data ;
    }


    public function load_custom_wp_admin_style_script($hook) {
        
        if($hook != 'settings_page_tinyAIEX-settings') {
                return;
        }
        wp_enqueue_style( 'custom_wp_admin_css', plugins_url('tinyAIEx_admin.css', __FILE__) );
        wp_enqueue_script( 'tinyaiex-admin-js', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ));        
        wp_enqueue_style( 'selectize-css', plugins_url('js/selectize/selectize.css', __FILE__) );
        wp_enqueue_script( 'selectize-js', plugins_url('js/selectize/selectize.min.js', __FILE__ ), array( 'jquery' ));
        $script  = 'let js_szovegek = {tinyaiex_setting_tiny_options_placeholder:"' . esc_html__('Click here and choose from options...', 'tiny-ai-assistant') . '", tinyaiex_setting_remove_options_placeholder:"' . esc_html__('Click here and choose from options what do you like to remove...', 'tiny-ai-assistant') . '"}';        
        wp_add_inline_script('tinyaiex-admin-js', $script, 'before');        
    }
    

    public function add_tinyAIEX_settings_page() {
        add_options_page( esc_html__('Tiny AI Assistant settings page', 'tiny-ai-assistant'), esc_html__('Tiny AI Assistant settings', 'tiny-ai-assistant'), 'manage_options', 'tinyAIEX-settings', array(  $this, 'tinyAIEX_settings_render_settings_page') );
    }

    public function tinyAIEX_settings_render_settings_page() {
        ?>
        <h2><?php echo esc_html__('Tiny AI Assistant settings', 'tiny-ai-assistant');?></h2>
        <form action="options.php" method="post">
            <?php 
            settings_fields( 'tinyaiex_settings' );
            do_settings_sections( 'tinyAIEX' ); 
            ?>
            <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
        </form>
        <?php
    }    

    public function tinyAIEX_register_settings() {
        register_setting( 'tinyaiex_settings', 'tinyaiex_settings', [
            'type'              => 'array',
            'sanitize_callback' => array(  $this, 'tinyAIEX_settings_validate'),
        ] );
        add_settings_section( 
                                'plugin_settings', 
                                esc_html__('Plugin settings', 'tiny-ai-assistant'), 
                                array(  $this, 'tinyAIEX_section_text'), 
                                'tinyAIEX',
                                array(
                                    'before_section' => $this->tinyAIEX_settings_page_before(), //html for before the section
                                    'after_section' => $this->tinyAIEX_settings_page_after(), //html for after the section
                                ) 
                            );
        add_settings_field( 'tinyaiex_setting_tiny_options', esc_html__('Active commands', 'tiny-ai-assistant'), array(  $this, 'tinyAIEX_setting_tiny_options'), 'tinyAIEX', 'plugin_settings' );
        add_settings_field( 'tinyaiex_setting_new_command', esc_html__('Add new command', 'tiny-ai-assistant'), array(  $this, 'tinyaiex_setting_new_command'), 'tinyAIEX', 'plugin_settings' );
        add_settings_field( 'tinyaiex_setting_remove_commands', esc_html__('Remove commands', 'tiny-ai-assistant'), array(  $this, 'tinyAIEX_setting_remove_commands'), 'tinyAIEX', 'plugin_settings' );
        add_settings_field( 'tinyaiex_setting_openai_api_key', esc_html__('OpenAI API key', 'tiny-ai-assistant'), array(  $this, 'tinyAIEX_setting_openai_api_key'), 'tinyAIEX', 'plugin_settings' );
        add_settings_field( 'tinyaiex_setting_licence_key', esc_html__('Licence key (for premium subscriptions)', 'tiny-ai-assistant'), array(  $this, 'tinyAIEX_setting_licence_key'), 'tinyAIEX', 'plugin_settings' );
        add_settings_field( 'tinyaiex_setting_temperature', esc_html__('Answer creativity', 'tiny-ai-assistant'), array(  $this, 'tinyAIEX_setting_temperature'), 'tinyAIEX', 'plugin_settings' );
        add_settings_field( 'tinyaiex_setting_model', esc_html__('Model', 'tiny-ai-assistant'), array(  $this, 'tinyaiex_setting_model'), 'tinyAIEX', 'plugin_settings' );        
    }   
    
    public function tinyAIEX_settings_page_before() {
        $html = '<div class="tinyaiex_settings_page_wrapper">';
        return $html ;
    }

    public function tinyAIEX_settings_page_after() {
        $html = '' ;
        $account_data = $this->get_account_data() ;
        if (isset($account_data)) {     
            $account_package_name = esc_html__('Free', 'tiny-ai-assistant') ;     
            if ($account_data == "ok") {
                $account_package_name = esc_html__('Premium', 'tiny-ai-assistant') ;
            }
            if ($account_data != "") {
                $html .= '<div class="tinyaiex_settings_account_data_wrapper">' ;
                $html .= '<h3>' . esc_html__('Current subscription', 'tiny-ai-assistant') . ': ' . $account_package_name ;
                $html .= '</h3>' ;
                $html .= '<p>' . esc_html__('More information:', 'tiny-ai-assistant') . ' <a href="' . esc_url("https://tiny-ai-assistant.aichatbot.hu") . '" target="_blank">' . esc_url("https://tiny-ai-assistant.aichatbot.hu") . '</a></p>' ;
                $html .= '</div>' ;
            }
            $html .= '</div>';
        }
        return $html ;
    }
    
    public function tinyAIEX_settings_validate( $input ) {     
        if (!is_numeric($input["temperature"])) {
            $input["temperature"] = 0 ;
        }
        unset($input["selected_options"]) ;
        unset($input["new_command_name"]) ;
        unset($input["new_command"]) ;
        $newinput = $input ;
        if (is_numeric($input["temperature"]) && $input["temperature"] >= 0 && $input["temperature"] <= 1) {
            $newinput["temperature"] = $input["temperature"] ;
        }   
        if (isset($input["available_options"])) {
            if (is_array($input["available_options"])) {
                $available_temp = $input["available_options"] ;
            }
            else
            {
                $available_temp = json_decode($input["available_options"]) ;
            }
            foreach ($available_temp as $index => $option) {
                $available_temp[$index] = (array)$option ;
            }
            $newinput["available_options"] = $available_temp ;            
        }
        return $newinput;
    }

    public function tinyAIEX_section_text() {
        echo '<p>' . esc_html__('You can configure the Tiny AI Assistant here', 'tiny-ai-assistant') . '</p>';
        echo '<p class="kiemelt-figyelmeztetes">' . esc_html__('You have to click on \'Save\' button on the bottom of this form to save your settings!', 'tiny-ai-assistant') . '</p>';
    }
    
    public function tinyAIEX_setting_tiny_options() {        
        $options = get_option( 'tinyaiex_settings' );
        if (isset($options['available_options'])) {
//            print_r((array)json_decode($options['available_options'])) ;
            $this->available_options = (array)$options['available_options'] ;
        }
        $available_options_json = wp_json_encode($this->available_options) ;

        $this->tiny_options = $this->available_options ;
        if (isset($options['tiny_options'])) {
            $this->tiny_options = $options['tiny_options'] ;
        }
        $tiny_options_json = wp_json_encode($this->tiny_options) ;

        if (isset($options['new_commands'])) {
            $this->new_commands = $options['new_commands'] ;
        }
        $new_commands_json = wp_json_encode($this->new_commands) ;

        echo "<input type='hidden' name='tinyaiex_settings[available_options]' id='tinyaiex_setting_available_options' value='" . esc_html($available_options_json) . "'>" ;
        echo "<input type='hidden' name='tinyaiex_settings[selected_options]' id='tinyaiex_setting_selected_options' value='" . esc_html($tiny_options_json) . "'>" ;

        echo "<select id='tinyaiex_setting_tiny_options' name='tinyaiex_settings[tiny_options][]' multiple>";        
        echo "</select>" ;
        if ($this->mode == "free") {
            echo '<p><small class="ingyenes-parancsszam-figyelmeztetes">' . esc_html__('In the free version of this plugin you can only set 3 active commands maximum.', 'tiny-ai-assistant') . '</small></p>' ;            
        }
    }

    public function tinyaiex_setting_new_command() {
        echo esc_html__('Command title', 'tiny-ai-assistant') . ": <input type='text' class='tinyaiex_text_input' name='tinyaiex_settings[new_command_name]' id='new_command_name' value=''><br />
        " . esc_html__('Command', 'tiny-ai-assistant') . ": <input type='text' class='tinyaiex_text_input new_command_input' name='tinyaiex_settings[new_command]' id='new_command'><input type='button' name='add_command' id='add_command' value='+'>";
        echo '<p><small class="uj-parancs-pelda">' . esc_html__('Command title: Any title, what do you like, Command example: "Translate this text to hungarian: "', 'tiny-ai-assistant') . '</small></p>' ;
    }
    
    public function tinyaiex_setting_remove_commands() {        
        $options = get_option( 'tinyaiex_settings' );
        if (isset($options['available_options'])) {
            $this->available_options = (array)$options['available_options'] ;
        }
        $available_options_json = wp_json_encode($this->available_options) ;

        echo "<select id='tinyaiex_setting_remove_options' name='tinyaiex_settings[remove_options][]' multiple>";        
        echo "</select>" ;
        echo "<input type='button' name='remove_commands' id='remove_commands' value='" . esc_html__('Remove selected commands', 'tiny-ai-assistant') . "'>" ;
    }

    public function tinyAIEX_setting_licence_key() {
        $options = get_option( 'tinyaiex_settings' );
        $value = "" ;
        if (isset($options['licence_key'])) {
            $value = $options['licence_key'] ;
        }
        echo "<input id='tinyaiex_setting_licence_key' name='tinyaiex_settings[licence_key]' type='text' value='" . esc_attr( $value ) . "' />";
    }

    public function tinyAIEX_setting_openai_api_key() {
        $options = get_option( 'tinyaiex_settings' );
        $value = "" ;
        if (isset($options['openai_api_key'])) {
            $value = $options['openai_api_key'] ;
        }
        echo "<input id='tinyaiex_setting_openai_api_key' name='tinyaiex_settings[openai_api_key]' type='text' value='" . esc_attr( $value ) . "' />";
    }

    public function tinyAIEX_setting_temperature() {        
        $options = get_option( 'tinyaiex_settings' );
        $temperature = "0" ;
        if (isset($options['temperature'])) {
            $temperature = $options['temperature'] ;
        }
        if ($this->mode == "premium") {
            echo "<select id='tinyaiex_setting_temperature' name='tinyaiex_settings[temperature]'>";
            for ($i = 0; $i < 11; $i++) {
                $value = "0." . $i ;
                if ($i == 10)
                    $value = "1.0" ;
                echo "<option value='" . esc_attr($value) . "'" ;            
                if ($value == $temperature) {
                    echo " selected" ;
                }
                echo ">" . esc_attr($value) . "</option>" ;
            }
            echo "</select>" ;                
        }
        else
        {
            echo esc_html__("Available only for premium users.", 'tiny-ai-assistant') ;
        }
    }
    
    public function tinyAIEX_setting_model() {
        $options = get_option( 'tinyaiex_settings' );
        $model = "gpt-3.5-turbo" ;
        if (isset($options['model'])) {
            $model = $options['model'] ;
        }        
        if ($this->mode == "premium") {
            echo "<select id='tinyaiex_setting_model' name='tinyaiex_settings[model]'>";
            for ($i = 0; $i < count($this->models); $i++) {
                $value = $this->models[$i]; 
                echo "<option value='" . esc_attr($value) . "'" ;            
                if ($value == $model) {
                    echo " selected" ;
                }
                echo ">" . esc_attr($value) . "</option>" ;
            }
            echo "</select>" ; 
        }
        else
        {
            echo esc_html__("Available only for premium users.", 'tiny-ai-assistant') ;
        }                          
    }

    public function tinyAIEX_setting_licence_valid_until() {
        $options = get_option( 'tinyaiex_settings' );
        $value = "" ;
        if (isset($options['licence_valid_until'])) {
            $value = $options['licence_valid_until'] ;
        }
        echo "<input id='tinyaiex_setting_licence_valid_until' name='tinyaiex_settings[licence_valid_until]' type='text' value='" . esc_attr( $value ) . "' />";
    }   
    
}
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

$tinyaiex = new tinyAIEX;

function tinyAIEX_command() {
    global $wpdb; 
    global $tinyaiex;
    
    $options = get_option( 'tinyaiex_settings' );    
    if (isset($options["available_options"])) {
        $available_options = $options["available_options"] ; 
    }
    $cmd = "" ;   
    $posted_cmd = "" ;
    if (!wp_verify_nonce( $_POST['nonce'], 'tinyaiex-ajax-nonce')) {
        wp_die();
    }
    else
    {
        if ($_POST["cmd"] != "") {
            $posted_cmd = sanitize_text_field($_POST["cmd"]) ;
            $l = false ;
            $i = 0 ;
            while (!$l && $i < count($available_options)) {
                if ($available_options[$i]["name"] == $posted_cmd) {
                    $cmd = $available_options[$i]["cmd"] . " " ;
                    $l = true ;
                }
                $i++ ;
            }
        }
        if ($cmd != "" || $posted_cmd == "") {
            $content = sanitize_text_field($_POST['content']) ;
//            if ($content != "") {   
                $openai_obj = new tinyaiex_openai($tinyaiex->get_licence_key()) ;
                $openai_obj->set_temperature($tinyaiex->get_temperature()) ;
                $openai_obj->set_model($tinyaiex->get_model()) ;
                $openai_obj->set_api_key($tinyaiex->get_openai_api_key()) ;
                $answer = $openai_obj->getChatAnswer($cmd . $content) ;
//            }
        }
        else
        {
            $answer = array("code" => 0, "message" => esc_html__("Error happened.", 'tiny-ai-assistant')) ;
        }
        echo wp_json_encode($answer);
        wp_die(); 
    }
}  

function tinyAIEX_add_command() {
    global $wpdb; 
    global $tinyaiex;
       
    if (!wp_verify_nonce( $_POST['nonce'], 'tinyaiex-ajax-nonce')) {
        wp_die();
    }
    $options = get_option( 'tinyaiex_settings' );
    if (!is_array($options)) {
        $options = array() ;
    }
    $available_options = array() ;
    if (isset($options["available_options"])) {
        $available_options = (array)$options["available_options"] ;    
    }
    if (isset($_POST["command_name"]) && isset($_POST["command_title"]) && isset($_POST["command_cmd"]) ) {
        $command_name = sanitize_text_field($_POST["command_name"]) ;
        $command_title = sanitize_text_field($_POST["command_title"]) ;
        $command_cmd = sanitize_text_field($_POST["command_cmd"]) ;
        $available_options[] = array("name" => $command_name, "title" => $command_title, "cmd" => $command_cmd) ;
    }
    $options["available_options"] = $available_options ;
    if (update_option('tinyaiex_settings', $options)) {
        $return["code"] = 1 ;
        $return["message"] = esc_html__("Command added", 'tiny-ai-assistant') ;
    }
    else
    {
        $return["code"] = 0 ;
        $return["message"] = esc_html__("Error happened.", 'tiny-ai-assistant') ;
    }
    echo wp_json_encode($return);
    wp_die();
}  

function tinyAIEX_remove_commands() {
    global $wpdb; 
    global $tinyaiex;
    
    if (!wp_verify_nonce( $_POST['nonce'], 'tinyaiex-ajax-nonce')) {
        wp_die();
    }
    $options = get_option( 'tinyaiex_settings' );
    if (!is_array($options)) {
        $options = array() ;
    }
    $available_options = array() ;
    if (isset($options["available_options"])) {
        $available_options = (array)$options["available_options"] ;    
    }
    if (isset($_POST["commands"]) && is_array($_POST["commands"])) {
        $new_available_options = array() ;        
        foreach ($available_options as $available_option) {
            if (!in_array($available_option["name"], $_POST["commands"])) {
                $new_available_options[] = $available_option ;
            }
        }
    }
    $options["available_options"] = $new_available_options ;
    if (update_option('tinyaiex_settings', $options)) {
        $return["code"] = 1 ;
        $return["message"] = esc_html__("Commands removed", 'tiny-ai-assistant') ;
    }
    else
    {
        $return["code"] = 0 ;
        $return["message"] = esc_html__("Error happened.", 'tiny-ai-assistant') ;
    }
    echo wp_json_encode($return);
    wp_die(); 
}  