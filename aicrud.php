<?php
/*
Plugin Name: AI CRUD
Description: A CRUD app plugin for WordPress
Version: 1.0
*/




add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script('ai_crud_script', plugin_dir_url(__FILE__) . 'aicrud.js', ['jquery'], '1.0', true);
});


/**
 * The function displays a welcome notice for the AI Crud app with instructions on how to add user
 * data.
 */
function aicrud_admine_notice() {
    if (!isset($_COOKIE['notice_closed'])) {
        global $pagenow;
        if (in_array($pagenow, ['index.php', 'admin.php'])) {
?>
            <div id="ai-curd-notice" class="notice notice-success is-dismissible">
                <h2>Welcome to AI Crud app :)</h2>
                <!-- <h4>page now : <?php echo $pagenow; ?></h4> -->
                <p>You can add users name and email in this crud app and see the data in ai crud admin menu page</p>

            </div>


    <?php
        };
    };
}

add_action('admin_notices', 'aicrud_admine_notice');


/**
 * This function redirects to a specific admin menu page when a specific plugin is activated.
 * 
 * @param plugin The parameter "plugin" is a string that represents the name of the plugin that was
 * just activated. It is passed to the "redirect_to_admin_menu_page" function when the
 * "activated_plugin" action is triggered.
 */
function redirect_to_admin_menu_page($plugin) {
    if (plugin_basename(__FILE__) == $plugin) {
        wp_redirect(admin_url('admin.php?page=wpdbmenu'));
        die();
    }
};
add_action('activated_plugin', 'redirect_to_admin_menu_page');



/**
 * The function adds a new link to the WordPress admin menu.
 * 
 * @param links An array of existing links to be displayed in the plugin's settings page.
 * 
 * @return an array of links with an additional link to the settings page for the WPDB Menu plugin.
 */
function rs_new_action_link($links) {
    $link = sprintf('<a href="%s" style="color:red" >%s</a>', admin_url('admin.php?page=wpdbmenu'), __('Settings', 'text_domain'));
    array_push($links, $link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'rs_new_action_link');



/* This code adds a link to the plugin's row meta in the WordPress plugin page. */
add_filter('plugin_row_meta', function ($links, $plugin) {
    if (plugin_basename(__FILE__) == $plugin) {
        $link = sprintf("<a href='%s' style='color:#ff3c41;'>%s</a>", esc_url('https://github.com/ridwan-shakil'), __('Fork on Github', 'plac'));
        array_push($links, $link);
    }

    return $links;
}, 10, 2);





// Create the table on plugin activation
function ai_crud_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ai_crud';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);


    /* `setcookie('catagory', 'Books', time() + 30, "/");` is setting a cookie named "catagory" with a
value of "Books" that will expire in 30 seconds and will be available to all paths on the website. */
    // setcookie('catagory', 'Books', time() + 30, "/");
}
register_activation_hook(__FILE__, 'ai_crud_create_table');

// Add the admin menu page
function ai_crud_add_menu_page() {
    add_menu_page(
        'AI CRUD',
        'AI CRUD',
        'manage_options',
        'ai-crud',
        'ai_crud_render_page',
        'dashicons-welcome-write-blog',
        20
    );
}
add_action('admin_menu', 'ai_crud_add_menu_page');

// Render the admin menu page
function ai_crud_render_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ai_crud';

    // Handle form submissions for updating and deleting data
    if (isset($_POST['ai_crud_submit'])) {
        $action = $_POST['ai_crud_submit'];
        $id = $_POST['ai_crud_id'];

        if ($action === 'update') {
            $name = sanitize_text_field($_POST['ai_crud_name']);
            $email = sanitize_text_field($_POST['ai_crud_email']);

            $wpdb->update(
                $table_name,
                array('name' => $name, 'email' => $email),
                array('id' => $id)
            );
        } elseif ($action === 'delete') {
            $wpdb->delete(
                $table_name,
                array('id' => $id)
            );
        }
    }

    // Insert new data into the table
    if (isset($_POST['ai_crud_insert'])) {
        $name = sanitize_text_field($_POST['ai_crud_name']);
        $email = sanitize_text_field($_POST['ai_crud_email']);

        $wpdb->insert(
            $table_name,
            array('name' => $name, 'email' => $email)
        );
    }

    // Retrieve all data from the table
    $data = $wpdb->get_results("SELECT * FROM $table_name");

    ?>
    <div class="wrap">
        <h1>AI CRUD</h1>

        <!-- Form for inserting new data -->
        <form method="POST" class="ai-crud-form">
            <h2>Add New Data</h2>
            <div class="ai-crud-form-group">
                <label for="ai_crud_name">Name:</label>
                <input type="text" name="ai_crud_name" id="ai_crud_name" required>
            </div>
            <div class="ai-crud-form-group">
                <label for="ai_crud_email">Email:</label>
                <input type="email" name="ai_crud_email" id="ai_crud_email" required>
            </div>
            <button type="submit" name="ai_crud_insert" class="button-primary">Add Data</button>
        </form>

        <h2>Existing Data</h2>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row) : ?>
                    <tr>
                        <td><?php echo $row->id; ?></td>
                        <td><?php echo $row->name; ?></td>
                        <td><?php echo $row->email; ?></td>
                        <td>
                            <button class="button-primary ai-crud-update-btn" data-id="<?php echo $row->id; ?>" data-name="<?php echo $row->name; ?>" data-email="<?php echo $row->email; ?>">Update</button>
                            <form method="POST" class="ai-crud-delete-form" style="display:inline;">
                                <input type="hidden" name="ai_crud_id" value="<?php echo $row->id; ?>">
                                <input type="hidden" name="ai_crud_name" value="<?php echo $row->name; ?>">
                                <input type="hidden" name="ai_crud_email" value="<?php echo $row->email; ?>">
                                <button type="submit" name="ai_crud_submit" value="delete" class="button-secondary">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <!-- Popup form for updating data -->
    <div id="ai-crud-popup" class="ai-crud-popup">
        <div class="ai-crud-popup-content">
            <h2>Update Data</h2>
            <form id="ai-crud-popup-form" method="POST">
                <input type="hidden" name="ai_crud_id" id="ai-crud-popup-id">
                <div class="ai-crud-form-group">
                    <label for="ai-crud-popup-name">Name:</label>
                    <input type="text" name="ai_crud_name" id="ai-crud-popup-name" required>
                </div>
                <div class="ai-crud-form-group">
                    <label for="ai-crud-popup-email">Email:</label>
                    <input type="email" name="ai_crud_email" id="ai-crud-popup-email" required>
                </div>
                <button type="submit" name="ai_crud_submit" value="update" class="button-primary">Update</button>
                <button id="ai-crud-popup-cancel" class="button-secondary">Cancel</button>
            </form>
        </div>
    </div>

    <style>
        .ai-crud-form {
            max-width: 400px;
            margin-bottom: 20px;
        }

        .ai-crud-form-group {
            margin-bottom: 10px;
        }

        .ai-crud-form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .ai-crud-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 9999;
        }

        .ai-crud-popup-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            width: 300px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .ai-crud-popup-content h2 {
            margin-top: 0;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Update button click event
        $(document).on('click', '.ai-crud-update-btn', function(e) {
            e.preventDefault();

            var id = $(this).data('id');
            var name = $(this).data('name');
            var email = $(this).data('email');

            // Set the values in the popup form
            $('#ai-crud-popup-id').val(id);
            $('#ai-crud-popup-name').val(name);
            $('#ai-crud-popup-email').val(email);

            // Show the popup
            $('#ai-crud-popup').show();
        });

        // Cancel button click event
        $('#ai-crud-popup-cancel').on('click', function(e) {
            e.preventDefault();

            // Hide the popup
            $('#ai-crud-popup').hide();
        });
    </script>

<?php
}
