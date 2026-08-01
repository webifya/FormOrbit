<?php

defined('ABSPATH') || exit; // FormOrbit email delivery.

class Webform_Mailer {
    private $option = 'webform_mail_settings';

    public function __construct() {
        add_action('admin_menu', array($this, 'menu'), 20);
        add_action('admin_post_webform_save_mail_settings', array($this, 'save'));
        add_action('admin_post_webform_send_test_email', array($this, 'test'));
        add_action('admin_post_formorbit_save_mail_settings', array($this, 'save'));
        add_action('admin_post_formorbit_send_test_email', array($this, 'test'));
        add_action('phpmailer_init', array($this, 'configure'), 20);
        add_filter('wp_mail_from', array($this, 'from_email'));
        add_filter('wp_mail_from_name', array($this, 'from_name'));
    }

    public function menu() {
        add_submenu_page('formorbit', __('Email Delivery', 'formorbit'), __('Email Delivery', 'formorbit'), 'manage_options', 'formorbit-email-delivery', array($this, 'page'), 5);
    }

    public function configure($phpmailer) {
        $settings = $this->settings();
        if (empty($settings['enabled']) || empty($settings['host'])) return;
        $phpmailer->isSMTP();
        $phpmailer->Host = $settings['host'];
        $phpmailer->Port = absint($settings['port']);
        $phpmailer->SMTPAuth = !empty($settings['authentication']);
        $phpmailer->Username = $settings['username'];
        $phpmailer->Password = $this->password($settings['password']);
        $phpmailer->Timeout = 15;
        $phpmailer->SMTPAutoTLS = false;
        if ($settings['encryption'] === 'ssl') $phpmailer->SMTPSecure = 'ssl';
        elseif ($settings['encryption'] === 'tls') $phpmailer->SMTPSecure = 'tls';
        else $phpmailer->SMTPSecure = '';
    }

    public function from_email($email) {
        $settings = $this->settings();
        return !empty($settings['enabled']) && !empty($settings['force_from']) && is_email($settings['from_email']) ? $settings['from_email'] : $email;
    }

    public function from_name($name) {
        $settings = $this->settings();
        return !empty($settings['enabled']) && !empty($settings['force_from']) && $settings['from_name'] !== '' ? $settings['from_name'] : $name;
    }

    public function page() {
        if (!current_user_can('manage_options')) return;
        $settings = $this->settings();
        $notice = get_transient('webform_mail_notice_' . get_current_user_id());
        delete_transient('webform_mail_notice_' . get_current_user_id());
        ?>
        <div class="wrap webform-wrap"><div class="webform-page-head"><div><h1><?php esc_html_e('Email Delivery', 'formorbit'); ?></h1><p><?php esc_html_e('Send FormOrbit and WordPress email through your SMTP provider.', 'formorbit'); ?></p></div></div>
        <?php if ($notice) : ?><div class="notice <?php echo !empty($notice['success']) ? 'notice-success' : 'notice-error'; ?> inline"><p><?php echo esc_html($notice['message']); ?></p></div><?php endif; ?>
        <div class="webform-card webform-mail-card">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="formorbit_save_mail_settings"><?php wp_nonce_field('webform_save_mail_settings'); ?>
                <label class="webform-check"><input type="checkbox" name="enabled" value="1" <?php checked(!empty($settings['enabled'])); ?>> <?php esc_html_e('Enable SMTP email delivery', 'formorbit'); ?></label>
                <p class="description"><?php esc_html_e('Leave this disabled if another SMTP or transactional-email plugin already controls WordPress email.', 'formorbit'); ?></p>
                <label><?php esc_html_e('Email provider', 'formorbit'); ?><select name="provider" id="formorbit-mail-provider">
                    <?php foreach ($this->providers() as $provider_id => $provider) : ?><option value="<?php echo esc_attr($provider_id); ?>" <?php selected($settings['provider'], $provider_id); ?>><?php echo esc_html($provider['label']); ?></option><?php endforeach; ?>
                </select></label>
                <p class="description" id="formorbit-mail-provider-help"><?php esc_html_e('Choose a provider to fill its recommended SMTP server, port, and encryption. Enter the credentials issued by that provider.', 'formorbit'); ?></p>
                <div class="webform-mail-grid">
                    <label><?php esc_html_e('SMTP host', 'formorbit'); ?><input type="text" name="host" value="<?php echo esc_attr($settings['host']); ?>" placeholder="smtp.example.com"></label>
                    <label><?php esc_html_e('Port', 'formorbit'); ?><input type="number" name="port" min="1" max="65535" value="<?php echo esc_attr($settings['port']); ?>"></label>
                    <label><?php esc_html_e('Encryption', 'formorbit'); ?><select name="encryption"><option value="tls" <?php selected($settings['encryption'], 'tls'); ?>>TLS / STARTTLS</option><option value="ssl" <?php selected($settings['encryption'], 'ssl'); ?>>SSL</option><option value="none" <?php selected($settings['encryption'], 'none'); ?>><?php esc_html_e('None', 'formorbit'); ?></option></select></label>
                    <label class="webform-check"><?php esc_html_e('Authentication', 'formorbit'); ?><span><input type="checkbox" name="authentication" value="1" <?php checked(!empty($settings['authentication'])); ?>> <?php esc_html_e('Use SMTP authentication', 'formorbit'); ?></span></label>
                    <label><?php esc_html_e('Username', 'formorbit'); ?><input type="text" name="username" value="<?php echo esc_attr($settings['username']); ?>" autocomplete="username"></label>
                    <label><?php esc_html_e('Password', 'formorbit'); ?><input type="password" name="password" value="" autocomplete="new-password" placeholder="<?php echo $settings['password'] ? esc_attr__('Saved — leave blank to keep', 'formorbit') : ''; ?>"></label>
                    <label><?php esc_html_e('From email', 'formorbit'); ?><input type="email" name="from_email" value="<?php echo esc_attr($settings['from_email']); ?>"></label>
                    <label><?php esc_html_e('From name', 'formorbit'); ?><input type="text" name="from_name" value="<?php echo esc_attr($settings['from_name']); ?>"></label>
                </div>
                <label class="webform-check"><input type="checkbox" name="force_from" value="1" <?php checked(!empty($settings['force_from'])); ?>> <?php esc_html_e('Use this From address for WordPress email', 'formorbit'); ?></label>
                <button class="button button-primary"><?php esc_html_e('Save email settings', 'formorbit'); ?></button>
            </form>
            <script>
            (function(){var select=document.getElementById('formorbit-mail-provider');if(!select)return;var presets=<?php echo wp_json_encode($this->providers()); ?>;var form=select.form;function apply(force){var item=presets[select.value]||presets.custom;if(!item)return;var host=form.elements.host,port=form.elements.port,encryption=form.elements.encryption,username=form.elements.username;if(force||!host.value)host.value=item.host||'';if(force||!port.value)port.value=item.port||587;if(force||!encryption.value)encryption.value=item.encryption||'tls';if(item.username&&!username.value)username.value=item.username;}select.addEventListener('change',function(){apply(true);});apply(false);}());
            </script>
        </div>
        <div class="webform-card webform-mail-card"><h2><?php esc_html_e('Send a test email', 'formorbit'); ?></h2><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="formorbit_send_test_email"><?php wp_nonce_field('webform_send_test_email'); ?><label><?php esc_html_e('Recipient', 'formorbit'); ?><input type="email" name="recipient" required value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>"></label><button class="button"><?php esc_html_e('Send test email', 'formorbit'); ?></button></form><p class="description"><?php esc_html_e('A successful test means WordPress handed the message to the configured mail server; final inbox delivery still depends on the provider and DNS authentication.', 'formorbit'); ?></p></div>
        </div>
        <?php
    }

    public function save() {
        $this->authorize('webform_save_mail_settings');
        $current = $this->settings();
        $password = sanitize_text_field(wp_unslash($_POST['password'] ?? ''));
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? 'custom'));
        $settings = array(
            'enabled' => !empty($_POST['enabled']),
            'provider' => array_key_exists($provider, $this->providers()) ? $provider : 'custom',
            'host' => strtolower(sanitize_text_field(wp_unslash($_POST['host'] ?? ''))),
            'port' => min(65535, max(1, absint($_POST['port'] ?? 587))),
            'encryption' => in_array($_POST['encryption'] ?? '', array('tls', 'ssl', 'none'), true) ? sanitize_key($_POST['encryption']) : 'tls',
            'authentication' => !empty($_POST['authentication']),
            'username' => sanitize_text_field(wp_unslash($_POST['username'] ?? '')),
            'password' => $password !== '' ? $this->protect($password) : $current['password'],
            'from_email' => sanitize_email(wp_unslash($_POST['from_email'] ?? '')),
            'from_name' => sanitize_text_field(wp_unslash($_POST['from_name'] ?? '')),
            'force_from' => !empty($_POST['force_from']),
        );
        update_option($this->option, $settings, false);
        wp_safe_redirect(admin_url('admin.php?page=formorbit-email-delivery&updated=1'));
        exit;
    }

    public function test() {
        $this->authorize('webform_send_test_email');
        $recipient = sanitize_email(wp_unslash($_POST['recipient'] ?? ''));
        $success = $recipient && wp_mail($recipient, __('FormOrbit email delivery test', 'formorbit'), sprintf(__("This test email was sent from %s at %s.", 'formorbit'), home_url('/'), current_time('mysql')));
        set_transient('webform_mail_notice_' . get_current_user_id(), array('success' => $success, 'message' => $success ? __('Test email was accepted by WordPress for delivery.', 'formorbit') : __('The test email failed. Verify the server, port, encryption, username, password, and From address.', 'formorbit')), MINUTE_IN_SECONDS);
        wp_safe_redirect(admin_url('admin.php?page=formorbit-email-delivery'));
        exit;
    }

    private function authorize($nonce) {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'formorbit'));
        check_admin_referer($nonce);
    }

    private function settings() {
        return wp_parse_args((array) get_option($this->option, array()), array('enabled' => false, 'provider' => 'custom', 'host' => '', 'port' => 587, 'encryption' => 'tls', 'authentication' => true, 'username' => '', 'password' => '', 'from_email' => get_option('admin_email'), 'from_name' => get_bloginfo('name'), 'force_from' => true));
    }

    private function providers() {
        return array(
            'custom' => array('label' => __('Custom SMTP', 'formorbit'), 'host' => '', 'port' => 587, 'encryption' => 'tls'),
            'mailgun' => array('label' => 'Mailgun', 'host' => 'smtp.mailgun.org', 'port' => 587, 'encryption' => 'tls'),
            'brevo' => array('label' => 'Brevo', 'host' => 'smtp-relay.brevo.com', 'port' => 587, 'encryption' => 'tls'),
            'sendgrid' => array('label' => 'SendGrid', 'host' => 'smtp.sendgrid.net', 'port' => 587, 'encryption' => 'tls', 'username' => 'apikey'),
            'amazon_ses' => array('label' => 'Amazon SES (US East 1)', 'host' => 'email-smtp.us-east-1.amazonaws.com', 'port' => 587, 'encryption' => 'tls'),
            'postmark' => array('label' => 'Postmark', 'host' => 'smtp.postmarkapp.com', 'port' => 587, 'encryption' => 'tls'),
            'mailjet' => array('label' => 'Mailjet', 'host' => 'in-v3.mailjet.com', 'port' => 587, 'encryption' => 'tls'),
            'mailersend' => array('label' => 'MailerSend', 'host' => 'smtp.mailersend.net', 'port' => 587, 'encryption' => 'tls'),
            'gmail' => array('label' => 'Gmail / Google Workspace', 'host' => 'smtp.gmail.com', 'port' => 587, 'encryption' => 'tls'),
            'microsoft_365' => array('label' => 'Microsoft 365 / Outlook', 'host' => 'smtp.office365.com', 'port' => 587, 'encryption' => 'tls'),
            'zoho' => array('label' => 'Zoho Mail', 'host' => 'smtp.zoho.com', 'port' => 587, 'encryption' => 'tls'),
        );
    }

    private function protect($password) {
        if (!function_exists('openssl_encrypt')) return 'plain:' . base64_encode($password);
        $iv = random_bytes(12);
        $tag = '';
        $encrypted = openssl_encrypt($password, 'aes-256-gcm', hash('sha256', wp_salt('auth'), true), OPENSSL_RAW_DATA, $iv, $tag);
        return $encrypted === false ? 'plain:' . base64_encode($password) : 'gcm:' . base64_encode($iv . $tag . $encrypted);
    }

    private function password($stored) {
        if (strpos($stored, 'plain:') === 0) return base64_decode(substr($stored, 6), true) ?: '';
        if (strpos($stored, 'gcm:') !== 0 || !function_exists('openssl_decrypt')) return '';
        $raw = base64_decode(substr($stored, 4), true);
        if (!$raw || strlen($raw) < 29) return '';
        return (string) openssl_decrypt(substr($raw, 28), 'aes-256-gcm', hash('sha256', wp_salt('auth'), true), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
    }
}
