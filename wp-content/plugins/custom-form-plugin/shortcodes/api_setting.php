<?php
// Add admin menu
function custom_plugin_menu()
{
    add_menu_page(
        'API Settings',
        'API Settings',
        'manage_options',
        'custom-api-settings',
        'custom_api_settings_page'
    );
}
add_action('admin_menu', 'custom_plugin_menu');



function custom_api_settings_page()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $api_key = sanitize_text_field($_POST['api_key']);
        $api_url = sanitize_text_field($_POST['api_url']);

        update_option('api_key', $api_key);
        update_option('api_url', $api_url);

        echo '<div class="notice notice-success"><p>Settings updated successfully!</p></div>';
    }

    $saved_api_key = get_option('api_key', '');
    $saved_api_url = get_option('api_url', '');

?>
    <div class="wrap">
        <h1 style="font-size:30px;font-weight:600;">API Settings</h1>
        <p style="margin-top:5px">Enter your API Key and API URL to connect your website with external services. Ensure the details are accurate and click "Save Settings" to apply the changes.</p>
        <form method="post" style="margin-top:25px">
            <div style="display:flex; align-items:center; gap:20px">
                <label for="api_key" style="width:100px">API Key:</label>
                <div style="position: relative; border: 1px solid black; width: 350px; padding: 5px; display: flex; align-items: center; border-radius:5px;">
                    <input type="password" id="api_key" name="api_key" value="<?php echo esc_attr($saved_api_key); ?>" required style="flex: 1; border: none; outline: none;">
                    <span id="toggle_api_key" style="cursor: pointer;">👁️</span>
                </div>
            </div>

            <br><br>
            <div style="display:flex; align-items:center;gap:20px">
                <label for="api_url" style="width:100px">API url:</label>
                <div style="position: relative; border: 1px solid black; width: 350px; padding: 5px; display: flex; align-items: center; border-radius:5px;">
                    <input type="password" id="api_url" name="api_url" value="<?php echo esc_attr($saved_api_url); ?>" required style="flex: 1; border: none; outline: none;">
                    <span id="toggle_api_url" style="cursor: pointer;">👁️</span>
                </div>
            </div>
            <br>

            <input type="submit" value="Save Settings" class="button button-primary">
        </form>
    </div>

    <script>
        function toggleVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            icon.addEventListener('click', () => {
                if (input.getAttribute('type') === 'password') {
                    input.setAttribute('type', 'text');
                    icon.textContent = '🙈';
                } else {
                    input.setAttribute('type', 'password');
                    icon.textContent = '👁️';
                }
            });
        }

        toggleVisibility('api_key', 'toggle_api_key');
        toggleVisibility('api_url', 'toggle_api_url');
    </script>

<?php
}
