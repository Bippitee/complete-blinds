<?php
//Register the custom taxonomy
function register_media_folders_taxonomy() {
    $labels = array(
        'name'                       => _x( 'Folders', 'taxonomy general name', 'media-folders' ),
        'singular_name'              => _x( 'Folder', 'taxonomy singular name', 'media-folders' ),
        'search_items'               => __( 'Search Folders', 'media-folders' ),
        'all_items'                  => __( 'All Folders', 'media-folders' ),
        'parent_item'                => __( 'Parent Folder', 'media-folders' ),
        'parent_item_colon'          => __( 'Parent Folder:', 'media-folders' ),
        'edit_item'                  => __( 'Edit Folder', 'media-folders' ),
        'update_item'                => __( 'Update Folder', 'media-folders' ),
        'add_new_item'               => __( 'Add New Folder', 'media-folders' ),
        'new_item_name'              => __( 'New Folder Name', 'media-folders' ),
        'menu_name'                  => __( 'Folders', 'media-folders' ),
    );

    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'folder' ),
    );

    register_taxonomy( 'media-folder', 'attachment', $args );
}
add_action( 'init', 'register_media_folders_taxonomy' );


// Modify media library queries: By default, the WordPress media library queries attachments without 
//considering the custom taxonomy. You'll need to modify the queries to include the "folders" taxonomy.

function modify_media_library_query( $query ) {
    if ( ! is_admin() && $query->is_main_query() && $query->get( 'post_type' ) == 'attachment' ) {
        $folder = isset( $_GET['folder'] ) ? sanitize_text_field( $_GET['folder'] ) : '';

        if ( $folder ) {
            $tax_query = array(
                array(
                    'taxonomy' => 'media-folder',
                    'field'    => 'slug',
                    'terms'    => $folder,
                ),
            );
            $query->set( 'tax_query', $tax_query );
        }
    }
}
add_action( 'pre_get_posts', 'modify_media_library_query' );

//META BOX
// Add folder management UI
function add_folder_meta_box() {
    add_meta_box(
        'media_folder_meta_box',
        __( 'Folder', 'media-folders' ),
        'render_folder_meta_box',
        'attachment',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes_attachment', 'add_folder_meta_box' );

// Render folder meta box
function render_folder_meta_box( $post ) {
    wp_nonce_field( 'media_folder_meta_box', 'media_folder_meta_box_nonce' );

    $terms = get_terms( array(
        'taxonomy'   => 'media-folder',
        'hide_empty' => false,
    ) );

    $current_folder = wp_get_object_terms( $post->ID, 'media-folder', array( 'fields' => 'ids' ) );

    if ( $terms ) {
        echo '<select name="media_folder" id="media-folder">';
        echo '<option value="">' . __( 'No Folder', 'media-folders' ) . '</option>';

        foreach ( $terms as $term ) {
            echo '<option value="' . $term->slug . '" ' . selected( $current_folder[0], $term->term_id, false ) . '>' . $term->name . '</option>';
        }

        echo '</select>';
    } else {
        echo '<p>' . __( 'No folders found.', 'media-folders' ) . '</p>';
    }
}

// Save folder meta box data
function save_folder_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['media_folder_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['media_folder_meta_box_nonce'], 'media_folder_meta_box' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['media_folder'] ) ) {
        $folder = sanitize_text_field( $_POST['media_folder'] );
        wp_set_object_terms( $post_id, $folder, 'media-folder' );
    }
}
add_action( 'save_post_attachment', 'save_folder_meta_box_data' );

// Filter media library by folder
function filter_media_library_by_folder( $query ) {
    global $pagenow, $current_screen;

    if ( $pagenow == 'upload.php' && $current_screen->post_type == 'attachment' && isset( $_GET['folder'] ) && $_GET['folder'] != '' ) {
        $query->set( 'tax_query', array(
            array(
                'taxonomy' => 'media-folder',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $_GET['folder'] ),
            ),
        ) );
    }
}
add_action( 'pre_get_posts', 'filter_media_library_by_folder' );

// Add folders to media library listing with folder icon
function add_folders_to_media_library_listing() {
    global $pagenow, $current_screen, $wp_query;

    if ( $pagenow == 'upload.php' && $current_screen->post_type == 'attachment' ) {
        $folders = get_terms( array(
            'taxonomy'   => 'media-folder',
            'hide_empty' => false,
        ) );

        if ( $folders && ! is_wp_error( $folders ) ) {
            $current_folder = isset( $_GET['folder'] ) ? sanitize_text_field( $_GET['folder'] ) : '';

            echo '<ul class="media-folders-list">';

            $all_class = ( $current_folder == '' ) ? ' class="current"' : '';
            echo '<li><a href="upload.php"' . $all_class . '>' . __( 'All', 'media-folders' ) . '</a></li>';

            foreach ( $folders as $folder ) {
                $folder_class = ( $current_folder == $folder->slug ) ? ' class="current"' : '';
                echo '<li><a href="upload.php?folder=' . $folder->slug . '"' . $folder_class . '>' . $folder->name . '</a></li>';
            }

            echo '</ul>';

            // Add CSS styles
            echo '
                <style>
                .actions {
                    display: inline-flex !important;
                    align-items: center;
                }
                .media-folders-list {
                    margin: 0px auto;
                    list-style: none;
                    padding: 0 5px;
                }
                .media-folders-list li {
                    display: inline-block;
                    margin: 0 5px 0px 0;
                }
                .media-folders-list a {
                    text-decoration: none;
                    padding: 6px 12px;
                    border-radius: 3px;
                }
                .media-folders-list a.current {
                    background-color: #0073aa;
                    color: #fff;
                }
                
                </style>';
        }
    }
}
add_action( 'restrict_manage_posts', 'add_folders_to_media_library_listing' );

