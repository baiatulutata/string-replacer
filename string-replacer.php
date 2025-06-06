<?php
/**
 * Plugin Name: String Replacer
 * Plugin URI: https://github.com/baiatulutata/string-replacer
 * Description: Replace visible and email strings via admin.
 * Version: 1.3
 * Author: Ionut Baldazar
 * Author URI: https://github.com/baiatulutata
 * Author URI: https://woomag.ro/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 7.2
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * Tested up to: 6.5
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('admin_menu', function () {
    add_options_page('String Replacer', 'String Replacer', 'manage_options', 'string-replacer', 'sr_settings_page');
});

add_action('admin_init', function () {
    register_setting('sr_settings_group', 'sr_replacements_array', [
        'type' => 'array',
        'sanitize_callback' => 'sr_sanitize_replacements',
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
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($replacements)): ?>
                    <?php foreach ($replacements as $index => $pair): ?>
                        <tr>
                            <td><input type="text" name="sr_replacements_array[<?php echo esc_attr($index); ?>][from]" value="<?php echo esc_attr($pair['from'] ?? ''); ?>" /></td>
                            <td><input type="text" name="sr_replacements_array[<?php echo esc_attr($index); ?>][to]" value="<?php echo esc_attr($pair['to'] ?? ''); ?>" /></td>
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
                paging: false,
                searching: true,
                ordering: false,
                columnDefs: [{
                    targets: [0, 1],
                    render: function (data, type, row, meta) {
                        if (type === 'filter' || type === 'sort') {
                            // Return value from input field
                            const el = $('<div>').html(data).find('input');
                            return el.length ? el.val() : '';
                        }
                        return data;
                    }
                }]
            });


            $('#add-row').on('click', function() {
                const uniqueKey = Date.now(); // ensures unique index for new row
                const rowNode = table.row.add([
                    `<input type="text" name="sr_replacements_array[${uniqueKey}][from]" />`,
                    `<input type="text" name="sr_replacements_array[${uniqueKey}][to]" />`,
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

function sr_parse_replacements() {
    $raw = get_option('sr_replacements_array', []);
    $rules = [];
    foreach ($raw as $item) {
        $from = $item['from'] ?? '';
        $to = $item['to'] ?? '';
        if (trim($from) !== '') {
            $rules[$from] = $to;
        }
    }
    return $rules;
}

function sr_replace_strings($text) {
    $replacements = sr_parse_replacements();
    foreach ($replacements as $from => $to) {
        $text = str_replace($from, $to, $text);
    }
    return $text;
}

// Replace in post title and content
add_filter('the_title', 'sr_replace_strings', 20);
add_filter('the_content', 'sr_replace_strings', 20);

// Replace globally by buffering full HTML output
add_action('template_redirect', function () {
    ob_start(function ($buffer) {
        return sr_replace_strings($buffer);
    });
});
add_filter('wp_mail', function($mail_args) {
    // $mail_args is an array with keys: to, subject, message, headers, attachments

    $mail_args['subject'] = sr_replace_strings($mail_args['subject']);
    $mail_args['message'] = sr_replace_strings($mail_args['message']);

    // Optionally, replace in headers if needed (e.g., From name/email)
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
    if ($hook !== 'settings_page_sr-settings') {
        return;
    }

    // Local paths to DataTables assets
    wp_enqueue_style(
        'datatables-css',
        plugins_url('assets/css/jquery.dataTables.min.css', __FILE__),
        [],
        '2.3.1'
    );

    wp_enqueue_script('jquery');

    wp_enqueue_script(
        'datatables-js',
        plugins_url('assets/js/jquery.dataTables.min.js', __FILE__),
        ['jquery'],
        '2.3.1',
        true
    );
}
add_action('admin_enqueue_scripts', 'sr_enqueue_admin_assets');

function sr_sanitize_replacements($input) {
    $sanitized = [];

    if (is_array($input)) {
        foreach ($input as $row) {
            $from = isset($row['from']) ? sanitize_text_field($row['from']) : '';
            $to   = isset($row['to'])   ? sanitize_text_field($row['to'])   : '';

            // Only include rows with non-empty 'from'
            if ($from !== '') {
                $sanitized[] = ['from' => $from, 'to' => $to];
            }
        }
    }

    return $sanitized;
}



?>
