<?php
/**
 * Plugin Name: String Replacer
 * Plugin URI: https://github.com/baiatulutata/string-replacer
 * Description: Replace visible and email strings via admin.
 * Version: 1.2
 * Author: Ionut Baldazar
 * Author URI: https://github.com/baiatulutata
 * Author URI: https://woomag.ro/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

add_action('admin_menu', function () {
    add_menu_page('String Replacer', 'String Replacer', 'manage_options', 'string-replacer', 'sr_settings_page');
    add_submenu_page('string-replacer', 'License Key', 'License Key', 'manage_options', 'string-replacer-license', 'sr_license_page');
});

add_action('admin_init', function () {
    register_setting('sr_settings_group', 'sr_replacements_array', [
        'type' => 'array',
        'sanitize_callback' => 'sr_sanitize_replacements',
    ]);
    register_setting('sr_license_group', 'sr_license_key', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
});

function sr_settings_page() {
    $replacements = get_option('sr_replacements_array', []);
    ?>
    <div class="wrap">
        <h1>String Replacer Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('sr_settings_group'); ?>
            <table class="wp-list-table widefat fixed striped" id="replacements-table">
                <thead>
                <tr>
                    <th>Original String</th>
                    <th>Replacement String</th>
                    <th>Case Sensitive</th>
                    <th>Regex</th>
                    <th>Conditions (comma-separated URLs, categories, post types)</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($replacements)): ?>
                    <?php foreach ($replacements as $index => $pair): ?>
                        <tr>
                            <td><input type="text" name="sr_replacements_array[<?php echo esc_attr($index); ?>][from]" value="<?php echo esc_attr($pair['from'] ?? ''); ?>" /></td>
                            <td><input type="text" name="sr_replacements_array[<?php echo esc_attr($index); ?>][to]" value="<?php echo esc_attr($pair['to'] ?? ''); ?>" /></td>
                            <td><input type="checkbox" name="sr_replacements_array[<?php echo esc_attr($index); ?>][case]" value="1" <?php checked(!empty($pair['case'])); ?> /></td>
                            <td><input type="checkbox" name="sr_replacements_array[<?php echo esc_attr($index); ?>][regex]" value="1" <?php checked(!empty($pair['regex'])); ?> /></td>
                            <td><input type="text" name="sr_replacements_array[<?php echo esc_attr($index); ?>][conditions]" value="<?php echo esc_attr($pair['conditions'] ?? ''); ?>" /></td>
                            <td><button type="button" class="button remove-row">Remove</button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            <p><button type="button" class="button" id="add-row">Add Row</button></p>
            <?php submit_button(); ?>
        </form>
    </div>

    <script>
        jQuery(document).ready(function($) {
            const table = $('#replacements-table').DataTable({
                paging: true,
                searching: true,
                ordering: false,
                columnDefs: [{
                    targets: [0, 1],
                    render: function (data, type, row, meta) {
                        if (type === 'filter' || type === 'sort') {
                            const el = $('<div>').html(data).find('input');
                            return el.length ? el.val() : '';
                        }
                        return data;
                    }
                }]
            });

            $('#add-row').on('click', function() {
                const uniqueKey = Date.now();
                const rowNode = table.row.add([
                    `<input type="text" name="sr_replacements_array[${uniqueKey}][from]" />`,
                    `<input type="text" name="sr_replacements_array[${uniqueKey}][to]" />`,
                    `<input type="checkbox" name="sr_replacements_array[${uniqueKey}][case]" value="1" />`,
                    `<input type="checkbox" name="sr_replacements_array[${uniqueKey}][regex]" value="1" />`,
                    `<input type="text" name="sr_replacements_array[${uniqueKey}][conditions]" />`,
                    `<button type="button" class="button remove-row">Remove</button>`
                ]).draw().node();

                $(rowNode).addClass('dynamic-row');
            });

            $('#replacements-table tbody').on('click', '.remove-row', function() {
                table.row($(this).closest('tr')).remove().draw();
            });
        });
    </script>
    <?php
}

function sr_license_page() {
    ?>
    <div class="wrap">
        <h1>Enter License Key</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('sr_license_group');
            $key = esc_attr(get_option('sr_license_key', ''));

            ?>
            <input type="text" name="sr_license_key" value="<?php echo esc_attr($key); ?>" size="50" />
            <?php submit_button('Save License'); ?>
        </form>
    </div>
    <?php
}

function sr_is_license_valid() {
    return true;
    $key = get_option('sr_license_key', '');
    if (empty($key)) return false;

    $cached = get_transient('sr_license_valid_cache_' . md5($key));
    if ($cached !== false) {
        return $cached === 'valid';
    }

    $response = wp_remote_get("https://www.google.com?license_key=" . urlencode($key), ['timeout' => 5]);
    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $is_valid = stripos($body, 'valid') !== false;

    set_transient('sr_license_valid_cache_' . md5($key), $is_valid ? 'valid' : 'invalid', 12 * HOUR_IN_SECONDS);
    return $is_valid;
}

add_action('admin_notices', function () {
    if (!current_user_can('manage_options')) return;
    if (sr_is_license_valid()) return;

    echo '<div class="notice notice-error"><p><strong>String Replacer:</strong> Invalid or missing license key. Please enter a valid key <a href="' . esc_attr(admin_url('admin.php?page=string-replacer-license')) . '">here</a>.</p></div>';
});

function sr_parse_replacements() {
    $raw = get_option('sr_replacements_array', []);
    return is_array($raw) ? $raw : [];
}

function sr_match_conditions($conditions) {
    if (empty($conditions)) return true;

    $items = array_map('trim', explode(',', $conditions));
    $post = get_post();
    if (!$post) return false;

    $current_url = isset($_SERVER['REQUEST_URI'])
        ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']))
        : '';
    $current_post_type = get_post_type($post);
    $post_categories = wp_get_post_categories($post->ID, ['fields' => 'slugs']);
    $post_tags = wp_get_post_tags($post->ID, ['fields' => 'slugs']);
    $post_author = get_the_author_meta('user_login', $post->post_author);

    foreach ($items as $item) {
        if (!str_contains($item, ':')) continue;
        [$key, $value] = array_map('trim', explode(':', $item, 2));

        switch ($key) {
            case 'url':
                if (stripos($current_url, $value) !== false) return true;
                break;

            case 'post_type':
                if ($value === $current_post_type) return true;
                break;

            case 'category':
                if (in_array($value, $post_categories)) return true;
                break;

            case 'tag':
                if (in_array($value, $post_tags)) return true;
                break;

            case 'author':
                if ($value === $post_author) return true;
                break;

            default:
                // Assume custom taxonomy
                if (taxonomy_exists($key)) {
                    $terms = wp_get_post_terms($post->ID, $key, ['fields' => 'slugs']);
                    if (in_array($value, $terms)) return true;
                }
                break;
        }
    }

    return false;
}

function sr_replace_strings($text) {
    if (!sr_is_license_valid()) return $text;
    $replacements = sr_parse_replacements();
    foreach ($replacements as $rule) {
        if (!sr_match_conditions($rule['conditions'] ?? '')) {
            continue;
        }

        $from = $rule['from'] ?? '';
        $to = $rule['to'] ?? '';
        $case = !empty($rule['case']);
        $regex = !empty($rule['regex']);

        if ($from === '') continue;

        if ($regex) {
            $pattern = $case ? "/{$from}/" : "/{$from}/i";
            $text = preg_replace($pattern, $to, $text);
        } else {
            $text = $case ? str_replace($from, $to, $text) : str_ireplace($from, $to, $text);
        }
    }
    return $text;
}

add_filter('the_title', 'sr_replace_strings', 20);
add_filter('the_content', 'sr_replace_strings', 20);

add_action('template_redirect', function () {
    ob_start(function ($buffer) {
        return sr_replace_strings($buffer);
    });
});

add_filter('wp_mail', function($mail_args) {
    $mail_args['subject'] = sr_replace_strings($mail_args['subject']);
    $mail_args['message'] = sr_replace_strings($mail_args['message']);

    if (!empty($mail_args['headers'])) {
        if (is_array($mail_args['headers'])) {
            foreach ($mail_args['headers'] as &$header) {
                $header = sr_replace_strings($header);
            }
        } else {
            $mail_args['headers'] = sr_replace_strings($mail_args['headers']);
        }
    }

    return $mail_args;
});

function sr_enqueue_admin_assets($hook) {
    if (!in_array($hook, ['toplevel_page_string-replacer', 'string-replacer_page_string-replacer-license'])) {
        return;
    }

    wp_enqueue_style('datatables-css', plugins_url('assets/css/jquery.dataTables.min.css', __FILE__), [], '1.13.6');
    wp_enqueue_script('jquery');
    wp_enqueue_script('datatables-js', plugins_url('assets/js/jquery.dataTables.min.js', __FILE__), ['jquery'], '1.13.6', true);
}
add_action('admin_enqueue_scripts', 'sr_enqueue_admin_assets');

function sr_sanitize_replacements($input) {
    $sanitized = [];

    if (is_array($input)) {
        foreach ($input as $row) {
            $from = isset($row['from']) ? sanitize_text_field($row['from']) : '';
            $to = isset($row['to']) ? sanitize_text_field($row['to']) : '';
            $case = !empty($row['case']) ? 1 : 0;
            $regex = !empty($row['regex']) ? 1 : 0;
            $conditions = isset($row['conditions']) ? sanitize_text_field($row['conditions']) : '';

            if ($from !== '') {
                $sanitized[] = [
                    'from' => $from,
                    'to' => $to,
                    'case' => $case,
                    'regex' => $regex,
                    'conditions' => $conditions
                ];
            }
        }
    }

    return $sanitized;
}