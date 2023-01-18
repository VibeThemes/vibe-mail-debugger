<?php

if (!defined('ABSPATH')) { exit; }

if (!class_exists('Vibe_Mail_Debugger')){

    class Vibe_Mail_Debugger{

        public static $instance;
        
        var $schedule;

        public static function init(){

            if ( is_null( self::$instance ) )
                self::$instance = new Vibe_Mail_Debugger();

            return self::$instance;
        }

         

        public function __construct(){ 
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            add_action( 'wp_mail_failed', [$this,'capture_failed_mails']);
        }


        function capture_failed_mails( $error ) {
            $mail_lod = get_option('mail_log');
            if(empty($mail_log)){$mail_log=[];}
            $mail_log[]=$error;
            update_option('mail_log',$mail_log);
        }

        function admin_menu() {
            add_options_page(
                __( 'Vibe Mail Debugger', 'vibe-mail-debugger' ),
                __( 'Mail Debugger', 'vibe-mail-debugger' ),
                'manage_options',
                'vibe-mail-debugger',
                array(
                    $this,
                    'settings_page'
                )
            );
        }

       
        function settings_page() {
            echo '<h2>'.__( 'Vibe Mail Debugger', 'vibe-mail-debugger' ).'</h2>';

            if(!empty($_POST['submit_clear_logs']) && wp_verify_nonce($_POST['clear_logs'],'clear_logs')){
                delete_option('mail_log');
            }

            $mail_log = get_option('mail_log');
            if(!empty($mail_log)){
                echo '<div class="mail_logs">';
                foreach($mail_log as $log){
                    echo '<div class="mail_log">';
                    echo '<div class="log_header">'.implode(',',$log->errors['wp_mail_failed']).'</div>';
                    echo '<div class="log_content">
                            <div class="mail_to">'.implode(',',$log->error_data['wp_mail_failed']['to']).'</div>
                            <div class="mail_subject">'.$log->error_data['wp_mail_failed']['subject'].'</div>
                            <div class="mail_content">'.$log->error_data['wp_mail_failed']['message'].'</div>';

                    echo '</div>';
                }
                echo '</div>';
            }else{
                echo '<div class="notice notice-success settings-error "><p>'.__('No logs found','vibe-mail-debugger').'</p></div>';
            }

            if(!empty($mail_log)){
                echo '<form method="post">
                <input type="submit" class="button-primary" name="submit_clear_logs" value="Clear Logs" />';
                wp_nonce_field('clear_logs','clear_logs');
                echo '</form>';
            }
            echo '<style>.mail_log,.mail_log >.log_content { display: flex; gap: 1rem; flex-direction: column; } .mail_logs { display: flex; flex-direction: column; gap: 1rem; } .mail_log { background: #fff; padding: 1rem; } .log_header { font-size: 1.2rem; font-weight: bold; } .mail_to { background: #fafafa; padding: 0.5rem; display: inline; border-radius: 10px; }</style>';
        }
    }

    Vibe_Mail_Debugger::init();

}

