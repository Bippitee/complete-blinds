<?php
/* ----------------------------------------------------------------------------------- /
/ Step 1: Create a function to register the plugin settings
/ ----------------------------------------------------------------------------------- */
function complete_blinds_register_settings() {
  // Register the main settings section
  add_settings_section(
    'complete_blinds_settings_section',
    'Complete Blinds Settings',
    'complete_blinds_settings_section_callback',
    'complete_blinds',
  );

  // Register the Blinds settings page
  add_settings_section(
    'complete_blinds_blinds_section',
    '',
    'complete_blinds_blinds_section_callback',
    'complete_blinds_blinds'
  );

  // Register the plugin settings fields
  //Google Maps API Key
  add_settings_field(
    'complete_blinds_google_maps_api_key',
    'Google Maps API Key',
    'complete_blinds_google_maps_api_key_callback',
    'complete_blinds',
    'complete_blinds_settings_section'
  );

  // Add more fields as needed

  // Register the settings
  // register_setting('complete_blinds', 'complete_blinds_field1');
  register_setting('complete_blinds', 'complete_blinds_google_maps_api_key');
  //->Blinds
  register_setting('complete_blinds_blinds', 'complete_blinds_blinds_types');
  

  // Add more settings as needed
}
add_action('admin_init', 'complete_blinds_register_settings');


/* ----------------------------------------------------------------------------------- /
/ Step 2: Create callback functions for the settings sections
/ ----------------------------------------------------------------------------------- */
function complete_blinds_settings_section_callback() {
  echo 'Miscellaneous settings for the plugin.';
}


function complete_blinds_blinds_section_callback() {
  // update_option('complete_blinds_blinds_types', ''); //RESET
  $blinds_types = get_option('complete_blinds_blinds_types');
  $typeIndex = $blinds_types ? count($blinds_types) : 0;
  echo '<table id="complete_blinds_blinds_types_table" class="wp-list-table widefat striped">';
  echo '<thead><tr>';
  echo '<th>Name</th>';
  // echo '<th>Value/Slug</th>';
  echo '<th>Has Subtypes</th>';
  echo '</tr></thead>';
  echo '<tbody>';

  if ($blinds_types) {
    foreach ($blinds_types as $index => $type) {

      $value = isset($type['value']) ? esc_attr($type['value']) : '';
      $label = isset($type['label']) ? esc_attr($type['label']) : '';
      $has_subtypes = isset($type['has_subtypes']) && $type['has_subtypes'] ? 1 : 0;
      echo '<tr>';
      echo '<td><input type="text" name="complete_blinds_blinds_types[' . $value . '][label]" value="' . $label . '" /><input type="hidden" name="complete_blinds_blinds_types[' . $value . '][value]" value="' . $value . '" /></td>';
      // echo '<td><input type="hidden" name="complete_blinds_blinds_types[' . $value . '][value]" value="' . $value . '" /></td>';
      echo '<td><input type="checkbox" name="complete_blinds_blinds_types[' . $value . '][has_subtypes]" ' . checked($has_subtypes, 1, false) . ' /></td>';
      echo '</tr>';

      if ($has_subtypes) {
          $subtypes = isset($type['subtypes']) && is_array($type['subtypes']) ? $type['subtypes'] : array();
          echo '<tr id="subtypeTable'.$value.'">';
          echo '<td colspan="3">';
          
          
          echo '<table class="wp-list-table striped complete-blinds-subtypes-table" data-value="'.$value.'" aria-hidden="'.(sizeof($subtypes) < 1 ? 'true' : 'false' ).'">';
          echo '<thead><tr>';
          echo '<th>Name</th>';
          // echo '<th>Value/Slug</th>';
          echo '</tr></thead>';
          echo '<tbody>';

          foreach ($subtypes as $subtypeIndex => $subtype) {
            
              $subtypeValue = isset($subtype['value']) ? esc_attr($subtype['value']) : '';
              $subtypeLabel = isset($subtype['label']) ? esc_attr($subtype['label']) : '';

              echo '<tr>';
              echo '<td><input type="text" name="complete_blinds_blinds_types[' . $value . '][subtypes][' . $subtypeIndex . '][label]" value="' . $subtypeLabel . '" /><input type="hidden"  name="complete_blinds_blinds_types[' . $value . '][subtypes][' . $subtypeIndex . '][value]" value="' . $subtypeValue . '" /></td>';
              // echo '<td><input type="hidden"  name="complete_blinds_blinds_types[' . $value . '][subtypes][' . $subtypeIndex . '][value]" value="' . $subtypeValue . '" /></td>';
              echo '</tr>';
          }

          echo '</tbody>';
          echo '</table>';
        

          echo '<button type="button" class="complete-blinds-add-subtype">Add Subtype</button>';
          
          
          
          echo '</td>';
          echo '</tr>';
      }
    }
  } 

  echo '</tbody>';
  echo '</table>';

  echo '<button type="button" id="complete_blinds_blinds_add_type">Add Type</button>';
 
}


/* ----------------------------------------------------------------------------------- /
/ Step 3: Create callback functions for the settings fields
/ ----------------------------------------------------------------------------------- */

function complete_blinds_google_maps_api_key_callback() {
  $google_maps_api_key = get_option('complete_blinds_google_maps_api_key');
  echo '<input type="text" style="width:300px;" name="complete_blinds_google_maps_api_key" value="' . esc_attr($google_maps_api_key) . '" />';
}

// Add more field callback functions as needed

/* ----------------------------------------------------------------------------------- /
/ Step 4: Create the main plugin settings page
/ ----------------------------------------------------------------------------------- */
function complete_blinds_settings_page() {
  echo '<div class="wrap">';
  echo '<h1>Complete Blinds Settings</h1>';
  echo '<form method="post" action="options.php">';
  settings_fields('complete_blinds');
  do_settings_sections('complete_blinds');
  submit_button();
  echo '</form>';
  echo '</div>';
}

/* ----------------------------------------------------------------------------------- /
/ Step 5: Create the sub-pages for Blinds
/ ----------------------------------------------------------------------------------- */
function complete_blinds_blinds_page() {
    echo '<div class="wrap">';
  echo '<h1>Blinds Settings</h1>';
  ?>
  <p>Blinds settings page description goes here.</p>
  <?php
  // Add content for Blinds settings page
  echo '<form method="post" action="options.php">';
  settings_fields('complete_blinds_blinds');
  do_settings_sections('complete_blinds_blinds');
  submit_button('Save Changes');
  echo '</form>';
  echo '</div>';
}


/* ----------------------------------------------------------------------------------- /
/ Step 6: Add the plugin settings page and sub-pages to the admin menu
/ ----------------------------------------------------------------------------------- */
function complete_blinds_add_menu_pages() {
  add_menu_page(
    'Complete Blinds',
    'Complete Blinds',
    'manage_options',
    'complete_blinds',
    'complete_blinds_settings_page',
    'data:image/svg+xml;base64,PHN2ZyBmaWxsPSIjZmZmIiBoZWlnaHQ9IjIwcHgiIHdpZHRoPSIyMHB4IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDMwMiAzMDIiIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAzMDIgMzAyIj4KICA8cGF0aCBkPSJtMjk4Ljk5NSwxOTcuMzA0bC0xNy4xMDMtMTcuMTA0IDE3LjE4LTE3LjE3OWMxLjg3NS0xLjg3NSAyLjkyOS00LjQxOSAyLjkyOS03LjA3MSAwLTIuNjUyLTEuMDU0LTUuMTk1LTIuOTI5LTcuMDcxbC0xNy4xOC0xNy4xOCAxNi4yMTEtMTYuMjExYzMuOTA1LTMuOTA1IDMuOTA1LTEwLjIzNyAwLTE0LjE0M2wtMzYuMzc2LTM2LjM3NWMtMy45MDYtMy45MDQtMTAuMjM2LTMuOTA0LTE0LjE0MywwbC0xNi4yMTEsMTYuMjExLTEwLjEyNi0xMC4xMjYgMTMuNTUzLTEzLjU1NGMzLjkwNS0zLjkwNSAzLjkwNS0xMC4yMzcgMC0xNC4xNDNsLTM2LjM3Ni0zNi4zNzVjLTEuODc2LTEuODc1LTQuNDE5LTIuOTI5LTcuMDcxLTIuOTI5LTIuNjUyLDAtNS4xOTYsMS4wNTQtNy4wNzEsMi45MjlsLTE2LjIxLDE2LjIxMS0xNy4xOC0xNy4xNzljLTMuOTA2LTMuOTA0LTEwLjIzNi0zLjkwNC0xNC4xNDMsMGwtMTcuMTc5LDE3LjE3OS0xNy41OTEtMTcuNTkyYy0xLjg3Ni0xLjg3NS00LjQxOS0yLjkyOS03LjA3MS0yLjkyOS0yLjY1MiwwLTUuMTk2LDEuMDU0LTcuMDcxLDIuOTI5bC0xNy4xNzksMTcuMTgtMTUuNzk5LTE1Ljc5OWMtMy45MDYtMy45MDQtMTAuMjM2LTMuOTA0LTE0LjE0My04Ljg4MTc4ZS0xNmwtMzYuMTA2LDM2LjEwNmMtMy45MDUsMy45MDUtMy45MDUsMTAuMjM3IDguODgxNzhlLTE2LDE0LjE0M2wxNS43OTgsMTUuNzk4LTE3LjQ3OSwxNy40OGMtMy45MDUsMy45MDUtMy45MDUsMTAuMjM3IDEuMzMyMjdlLTE1LDE0LjE0M2wxNy41OTEsMTcuNTkxLTE3LjE0OSwxNy4xNDhjLTMuOTA1LDMuOTA1LTMuOTA1LDEwLjIzNyAxLjMzMjI3ZS0xNSwxNC4xNDNsMTcuMTgsMTcuMTgtMTYuMjExLDE2LjIxYy0zLjkwNSwzLjkwNS0zLjkwNSwxMC4yMzcgMCwxNC4xNDNsMzYuMzc2LDM2LjM3NmMzLjkwNiwzLjkwNCAxMC4yMzYsMy45MDQgMTQuMTQzLDBsMTYuMjExLTE2LjIxMSAxMC4xMjcsMTAuMTI2LTEzLjU1MywxMy41NTNjLTMuOTA1LDMuOTA1LTMuOTA1LDEwLjIzNyAwLDE0LjE0M2wzNi4zNzYsMzYuMzc2YzMuOTA2LDMuOTA0IDEwLjIzNiwzLjkwNCAxNC4xNDMsMGwxNi4yMTEtMTYuMjExIDE3LjE3OSwxNy4xNzljMS45NTMsMS45NTIgNC41MTIsMi45MjkgNy4wNzEsMi45MjkgMi41NTksMCA1LjExOC0wLjk3NyA3LjA3MS0yLjkyOWwxNy4xNDgtMTcuMTQ4IDE3LjEwNCwxNy4xMDRjMy45MDYsMy45MDQgMTAuMjM2LDMuOTA0IDE0LjE0MywwbDE3LjQ3OS0xNy40OCAxNi41ODUsMTYuNTg1YzMuOTA2LDMuOTA0IDEwLjIzNiwzLjkwNSAxNC4xNDMsMGwzNi4xMDctMzYuMTA1YzEuODc1LTEuODc1IDIuOTI5LTQuNDE5IDIuOTI5LTcuMDcxIDAtMi42NTItMS4wNTQtNS4xOTUtMi45MjktNy4wNzFsLTE2LjU4Ni0xNi41ODUgMTcuMTgtMTcuMThjMy45MDMtMy45MDcgMy45MDMtMTAuMjM5LTAuMDAzLTE0LjE0NHptLTQ0LjMzOS0xMTEuMTJsMjIuMjMzLDIyLjIzMy0xMy4wNzYsMTMuMDc2aC0yMi42NnYtMjEuODA2bDEzLjUwMy0xMy41MDN6bS0xNTIuNzIzLDEzMi4zMTFsLTE0LjA0NS0xNC4wNDUgMTQuNDU2LTE0LjQ1NmgyMS44MDd2MjguNTAxaC0yMi4yMTh6bTcwLjcxOS05Ny4wMDJoLTI4LjUwMXYtMjIuNjMxbDE0LjA2LTE0LjA2IDE0LjQ0MSwxNC40NDJ2MjIuMjQ5em0wLDIwdjI4LjUwMWgtMjguNTAxdi0yOC41MDFoMjguNTAxem0tNDguNTAxLTIwaC0yMS42NzlsLTEzLjM0Ni0xMy4zNDYgMjEuOTY0LTIxLjk2NCAxMy4wNjEsMTMuMDYxdjIyLjI0OXptLTIyLjYzLDIwaDIyLjYzdjI4LjUwMWgtMjIuMjhsLTE0LjQyNy0xNC40MjYgMTQuMDc3LTE0LjA3NXptNDIuNjMsNDguNTAxaDI4LjUwMXYyOC41MDFoLTI4LjUwMXYtMjguNTAxem00OC41MDEsMGgyOC41MDF2MjguNTAxaC0yOC41MDF2LTI4LjUwMXptMC0yMHYtMjguNTAxaDI4LjUwMXYyOC41MDFoLTI4LjUwMXptMC00OC41MDF2LTIxLjgwNmwxNC40NzItMTQuNDcyIDE0LjAyOSwxNC4wM3YyMi4yNDhoLTI4LjUwMXptLTE0NC44NjUsOTQuNzM3bC0yMi4yMzMtMjIuMjMzIDE2LjIxMS0xNi4yMTFjMy45MDUtMy45MDUgMy45MDUtMTAuMjM3IDAtMTQuMTQzbC0xNy4xOC0xNy4xOCAxNy4xNDgtMTcuMTQ4YzMuOTA1LTMuOTA1IDMuOTA1LTEwLjIzNyAwLTE0LjE0M2wtMTcuNTktMTcuNTkxIDE3LjQ3OS0xNy40NzljMy45MDUtMy45MDUgMy45MDUtMTAuMjM3IDAtMTQuMTQybC0xNS43OTktMTUuOCAyMS45NjQtMjEuOTY0IDE1Ljc5OSwxNS43OTljMS44NzYsMS44NzUgNC40MTksMi45MjkgNy4wNzEsMi45MjkgMi42NTIsMCA1LjE5Ni0xLjA1NCA3LjA3MS0yLjkyOWwxNy4xNzktMTcuMTggMTcuNTkyLDE3LjU5MmMzLjkwNiwzLjkwNCAxMC4yMzYsMy45MDQgMTQuMTQzLDBsMTcuMTc5LTE3LjE4IDE3LjE3OSwxNy4xOGMxLjg3NiwxLjg3NSA0LjQxOSwyLjkyOSA3LjA3MSwyLjkyOSAyLjY1MiwwIDUuMTk2LTEuMDU0IDcuMDcxLTIuOTI5bDE2LjIxLTE2LjIxMSAyMi4yMzMsMjIuMjMzLTMwLjcxNCwzMC43NTEtMTcuNTkxLTE3LjU5MmMtMS44NzUtMS44NzUtNC40MTktMi45MjktNy4wNzEtMi45MjktMi42NTIsMC01LjE5NSwxLjA1NC03LjA3MSwyLjkyOWwtMTcuMTgsMTcuMTgtMTUuNzk3LTE1Ljc5OGMtMS44NzUtMS44NzUtNC40MTktMi45MjktNy4wNzEtMi45MjktMi42NTIsMC01LjE5NiwxLjA1NC03LjA3MSwyLjkyOWwtMzYuMTA2LDM2LjEwNmMtMy45MDUsMy45MDUtMy45MDUsMTAuMjM3IDAsMTQuMTQzbDE1Ljc5OSwxNS43OTktMTcuNDgsMTcuNDc5Yy0xLjg3NSwxLjg3NS0yLjkyOSw0LjQxOS0yLjkyOSw3LjA3MSAwLDIuNjUyIDEuMDU0LDUuMTk2IDIuOTI5LDcuMDcxbDE3LjU5MiwxNy41OTEtMzYuMDM3LDM2em02My4zMDMsNTcuOTg3bC0yMi4yMzMtMjIuMjMzIDEzLjQ4OC0xMy40ODhoMjEuODA2djIyLjY2MWwtMTMuMDYxLDEzLjA2em00Ny41MzIsLjk2OGwtMTQuNDcyLTE0LjQ3MnYtMjIuMjE4aDI4LjUwMXYyMi42NmwtMTQuMDI5LDE0LjAzem00OC4zOTUtLjA0NWwtMTQuMzY1LTE0LjM2NXYtMjIuMjc5aDI4LjUwMXYyMi41MDlsLTE0LjEzNiwxNC4xMzV6bTQ4LjIwNy0uODk0bC0xNC4wNzEtMTQuMDcydi0yMS42NzloMjIuMjQ4bDEzLjc4NywxMy43ODctMjEuOTY0LDIxLjk2NHptOC40MzgtNTUuNzUxaC0yMi41MXYtMjguNTAxaDIyLjI0OGwxNC4zODIsMTQuMzgxLTE0LjEyLDE0LjEyem0uMTUxLTQ4LjUwMWgtMjIuNjZ2LTI4LjUwMWgyMi4yNDhsMTQuNDU3LDE0LjQ1Ny0xNC4wNDUsMTQuMDQ0eiIvPgo8L3N2Zz4=', // The URL to the icon to be used for this menu.
  );

  //change the first submenu name by pointing to the parent menu
  add_submenu_page(
    'complete_blinds',
    'Settings',
    'Settings',
    'manage_options',
    'complete_blinds',
    'complete_blinds_settings_page'
  );
  
  // Add sub-pages for Blinds and Address Book
  add_submenu_page(
    'complete_blinds',
    'Blinds',
    'Blinds',
    'manage_options',
    'complete_blinds_blinds',
    'complete_blinds_blinds_page'
  );

  add_submenu_page(
    'complete_blinds',
    'Address Book',
    'Address Book',
    'manage_options',
    'edit.php?post_type=address_book',
  );
  add_submenu_page(
    'complete_blinds',
    'Windows',
    'Windows',
    'manage_options',
    'edit.php?post_type=cb_window',
  );
  add_submenu_page(
    'complete_blinds',
    'Doors',
    'Doors',
    'manage_options',
    'edit.php?post_type=cb_door',
  );
}

add_action('admin_menu', 'complete_blinds_add_menu_pages');

/* ----------------------------------------------------------------------------------- /
/Add screen specific styling 
/ ----------------------------------------------------------------------------------- */

add_action( 'admin_head', 'cblinds_custom_style_blinds_settings' );
function cblinds_custom_style_blinds_settings() {
  ?>
  <style>
    .complete-blinds-subtypes-table[aria-hidden='true'] {
      display: none;
    }

    .complete-blinds-subtypes-table[aria-hidden='false'] {
      display: in;
    }
  </style>
  <?php
}


/* ----------------------------------------------------------------------------------- /
/ USER ROLES  
/ ----------------------------------------------------------------------------------- */

function complete_blinds_new_roles() {
  //check if roles exist  (if one exists, they all exist)
  if (get_role('complete_blinds_cutting_table')) {
    return;
  }
  //Cutting tables
  add_role(
    'complete_blinds_cutting_table',
    __('Cutting Table', 'complete-blinds'),
    // array(
    //   'read' => true,
    //   'edit_posts' => true,
    //   'upload_files' => true,
    // )
    get_role('editor')->capabilities
  );

  //Tube cutting and roller table
  add_role(
    'complete_blinds_tube_cutting_roller_table',
    __('Tube Cutting and Roller Table', 'complete-blinds'),
    get_role('editor')->capabilities
  );

  //Roman Table
  add_role(
    'complete_blinds_roman_table',
    __('Roman Table', 'complete-blinds'),
    get_role('editor')->capabilities
  );

  //Honeycomb Table
  add_role(
    'complete_blinds_honeycomb_table',
    __('Honeycomb Table', 'complete-blinds'),
    get_role('editor')->capabilities
  );

  //Venetian Table
  add_role(
    'complete_blinds_venetian_table',
    __('Venetian Table', 'complete-blinds'),
    get_role('editor')->capabilities
  );

  //Vertical Table
  add_role(
    'complete_blinds_vertical_table',
    __('Vertical Table', 'complete-blinds'),
    get_role('editor')->capabilities
  );

  //Roller Despatch
  add_role(
    'complete_blinds_roller_despatch',
    __('Roller Despatch', 'complete-blinds'),
    get_role('editor')->capabilities
  );

  //CBC Office
  add_role(
    'complete_blinds_cbc_office',
    __('CBC Office', 'complete-blinds'),
    get_role('administrator')->capabilities
  );
}

add_action('init', 'complete_blinds_new_roles');

/* ----------------------------------------------------------------------------------- /
/ MISCELLANEOUS FUNCTIONS
/ ----------------------------------------------------------------------------------- */

//Debugging
function prettyPrint($a, $t='pre') {echo "<$t>".print_r($a,1)."</$t>";}