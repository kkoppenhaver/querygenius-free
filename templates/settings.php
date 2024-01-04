<div class="wrap"></div>
    <h2>Settings</h2>
    <?php

      // Check if status is 1 which means a successful options save just happened
      if(isset($_GET['status']) && $_GET['status'] == 1): ?>
        
        <div class="notice notice-success inline">
          <p>API Keys Saved!</p>
        </div>

      <?php endif;

    ?>
    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">

        <h3>API Keys</h3>

        <!-- The nonce field is a security feature to avoid submissions from outside WP admin -->
        <?php wp_nonce_field( 'querygenius_api_keys_verify'); ?>

        <table class="form-table">
          <tbody>
            <tr>
              <th scope="row" colspan="1">
                <label for="openai_api_key">OpenAI API Key</label>
              </th>
              <td colspan="1">
                <input type="password" name="openai_api_key" placeholder="Enter OpenAI API Key" value="<?php echo $openai_api_key ? esc_attr( $openai_api_key ) : '' ; ?>">
              </td>
            </tr>
            <tr>
              <th scope="row" colspan="1">
                <label for="openai_api_key">querygenius API Key</label>
              </th>
              <td colspan="1">
                <input type="password" name="querygenius_api_key" placeholder="Enter querygenius API Key" value="<?php echo $querygenius_api_key ? esc_attr( $querygenius_api_key ) : '' ; ?>">
              </td>
            </tr>
          </tbody>
        </table>

        <input type="hidden" name="action" value="querygenius_save_settings">			 
        <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Save Settings"  />
    </form> 
</div>