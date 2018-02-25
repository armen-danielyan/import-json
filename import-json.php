<?php
/*
Plugin Name: Import Json
Plugin URI: http://importjson.com/
Description: Parse the json feed and import the result as posts.
Version: 0.0.1
Author: Armen Danielyan
Author URI: http://linkedin.com/in/danielyanarmen
License: GPLv2 or later
Text Domain: importjson
*/
$PTsingular = get_option('json_importer_options')['post_type'];
$PTplural = $PTsingular . 's';

//Register Custom Post Type
add_action('init', 'Create_Games_CPT', 1);
function Create_Games_CPT(){

    global $PTplural, $PTsingular;

    $labels = array(
        'name'                  => _x( $PTplural, 'Post Type General Name', 'import-json' ),
        'singular_name'         => _x( 'Game', 'Post Type Singular Name', 'import-json' ),
        'menu_name'             => __( $PTplural, 'import-json' ),
        'parent_item_colon'     => __( 'Parent ' . $PTsingular, 'import-json' ),
        'all_items'             => __( 'All ' . $PTplural, 'import-json' ),
        'view_item'             => __( 'View ' . $PTsingular, 'import-json' ),
        'add_new_item'          => __( 'Add New ' . $PTsingular, 'import-json' ),
        'add_new'               => __( 'Add New', 'import-json' ),
        'edit_item'             => __( 'Edit ' . $PTsingular, 'import-json' ),
        'update_item'           => __( 'Update ' . $PTsingular, 'import-json' ),
        'search_items'          => __( 'Search ' . $PTsingular, 'import-json' ),
        'not_found'             => __( 'Not Found', 'import-json' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'import-json' ),
    );

    $args = array(
        'label'                 => __( $PTsingular, 'import-json' ),
        'description'           => __( $PTsingular . ' list', 'import-json' ),
        'menu_icon'             => 'dashicons-format-video',
        'menu_position'         => 29,
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields' ),
        'taxonomies'            => array( 'category' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'show_in_nav_menus'     => true,
        'show_in_admin_bar'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
        'register_meta_box_cb'  => 'Add_Games_Metaboxes'
    );

    register_post_type( $PTplural, $args );
}

//Add Metaboxes
function Add_Games_Metaboxes(){
    global $PTplural;

    add_meta_box('mb_package_id', 'Package ID', 'MB_Package_ID', $PTplural, 'normal', 'high');
    add_meta_box('mb_aspect_ratio', 'Aspect Ratio', 'MB_Aspect_Ratio', $PTplural, 'normal', 'high');
    add_meta_box('mb_url', 'URL', 'MB_URL', 'games', $PTplural, 'high');
    add_meta_box('mb_date', 'Date', 'MB_Date', $PTplural, 'normal', 'high');
    add_meta_box('mb_orientation', 'Orientation', 'MB_Orientation', $PTplural, 'normal', 'high');
}

function MB_Package_ID(){
    global $post;
    echo '<input type="hidden" name="packageidmeta_noncename" id="packageidmeta_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
    $packageID = get_post_meta($post->ID, '_package_id', true);
    echo '<input type="text" name="_package_id" value="' . $packageID  . '" class="widefat" />';
}
function MB_Aspect_Ratio(){
    global $post;
    echo '<input type="hidden" name="aspectratiometa_noncename" id="aspectratiometa_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
    $aspectRatio = get_post_meta($post->ID, '_aspect_ratio', true);
    echo '<input type="text" name="_aspect_ratio" value="' . $aspectRatio  . '" class="widefat" />';
}
function MB_URL(){
    global $post;
    echo '<input type="hidden" name="urlmeta_noncename" id="urlmeta_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
    $url = get_post_meta($post->ID, '_url', true);
    echo '<input type="text" name="_url" value="' . $url  . '" class="widefat" />';
}
function MB_Date(){
    global $post;
    echo '<input type="hidden" name="datemeta_noncename" id="datemeta_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
    $date = get_post_meta($post->ID, '_date', true);
    echo '<input type="text" name="_date" value="' . $date  . '" class="widefat" />';
}
function MB_Orientation(){
    global $post;
    echo '<input type="hidden" name="orientationmeta_noncename" id="orientationmeta_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
    $orientation = get_post_meta($post->ID, '_orientation', true);
    echo '<input type="text" name="_orientation" value="' . $orientation  . '" class="widefat" />';
}

add_action('save_post', 'Save_Games_Metaboxes', 1, 2);
function Save_Games_Metaboxes($post_id, $post) {
    if ( !wp_verify_nonce($_POST['packageidmeta_noncename'], plugin_basename(__FILE__)) ) {
        return $post->ID;
    }
    if ( !current_user_can( 'edit_post', $post->ID )) {
        return $post->ID;
    }

    $games_meta['_package_id'] = $_POST['_package_id'];
    $games_meta['_aspect_ratio'] = $_POST['_aspect_ratio'];
    $games_meta['_url'] = $_POST['_url'];
    $games_meta['_date'] = $_POST['_date'];
    $games_meta['_orientation'] = $_POST['_orientation'];

    // Add values of $events_meta as custom fields

    foreach ($games_meta as $key => $value) {
        if( $post->post_type == 'revision' ) return;
        $value = implode(',', (array)$value);
        if(get_post_meta($post->ID, $key, FALSE)) {
            update_post_meta($post->ID, $key, $value);
        } else {
            add_post_meta($post->ID, $key, $value);
        }
        if(!$value) delete_post_meta($post->ID, $key);
    }
}

//Create Options Admin Page
add_action('admin_menu' , 'Add_Settings_Page');
function Add_Settings_Page(){
    add_menu_page('ImportJson', 'Import Json', 'manage_options', 'import_json', 'Create_Page', 'dashicons-update', 30);
}

function Create_Page(){
    if(!get_option('json_importer_options')){
        add_option('json_importer_options', array(
                'json_feed_url' => 'http://html5games.com/feed',
                'post_type'     => 'game'
            )
        );
    }
    if(isset($_POST['save'])){
        $json_importer_options = array(
            'json_feed_url' => $_POST['json_url'],
            'post_type' => $_POST['post_type']
        );
        update_option('json_importer_options', $json_importer_options);
    } ?>

    <div class="wrap theme_options_panel">
        <h2>Import JSON</h2>
        <div>
            <form method="post" enctype="multipart/form-data">
                <table>
                    <tbody>
                        <tr>
                            <th><label for="post_type">Post Type: </label></th>
                            <td><input style="width:320px" id="post_type" type="text" name="post_type" value="<?php echo get_option('json_importer_options')['post_type']; ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="json_url">Json URL: </label></th>
                            <td><input style="width:320px" id="json_url" type="text" name="json_url" value="<?php echo get_option('json_importer_options')['json_feed_url']; ?>"></td>
                        </tr>
                        <tr>
                            <th></th>
                            <td><input style="float:right" id="import-json" class="button-primary" type="submit" name="save" value="Save"></td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        <hr>
        <form method="post" enctype="multipart/form-data">
            <table>
                <tbody>
                    <tr>
                        <td><input id="import-json" class="button-primary" type="submit" name="submit" value="Import"></td>
                    </tr>
                </tbody>
            </table>
        </form>
        <div>
            <?php
            //
            function setGameFeaturedImage($gamePostID, $thumbURL){
                $photoUrl = $thumbURL;
                preg_match('/^.+\/(.+)/', $photoUrl, $matches);
                $photoNameExt = $matches[1];
                preg_match('/^.+\/(.+)\./', $photoUrl, $matchesName);
                $photoName = $matchesName[1];
                $photo = new WP_Http();
                $photo = $photo->request($photoUrl);

                $attachment = wp_upload_bits($photoNameExt, null, $photo['body'], date("Y-m", strtotime($photo['headers']['last-modified'])));

                $fileType = wp_check_filetype(basename($attachment['file']), null);

                $postinfo = array(
                    'post_mime_type' => $fileType['type'],
                    'post_title' => $photoName,
                    'post_content' => '',
                    'post_status' => 'inherit',
                );
                $filename = $attachment['file'];
                $attach_id = wp_insert_attachment($postinfo, $filename, $gamePostID);
                $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
                wp_update_attachment_metadata($attach_id, $attach_data);
                set_post_thumbnail($gamePostID, $attach_id);
            }

            if(isset($_POST['submit'])) {
                $jsonUrl = get_option('json_importer_options')['json_feed_url'];
                $jsonContent = file_get_contents($jsonUrl);
                $jsonObj = json_decode($jsonContent, true);

                $gamesCats = $jsonObj['categories'];
                $games = $jsonObj['games'];

                foreach($gamesCats as $gamesCat){
                    $parent_term = term_exists( $gamesCat, 'category' );
                    if(!$parent_term){
                        wp_insert_term($gamesCat, 'category');
                    }
                }

                $currentUserId = get_current_user_id();
                ?>

                <table class="widefat">
                    <thead>
                        <tr>
                            <th>N</th>
                            <th>Package ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Thumbnail</th>
                            <th>URL</th>
                            <th>Date</th>
                            <th>Aspect Ratio</th>
                            <th>Categories</th>
                            <th>Orientation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        global $PTplural;
                        $k = 0;
                        foreach($games as $game) {
                            $k++;
                            $packageId = $game['package_id'];
                            $name = $game['name'];
                            $description = $game['description'];
                            $thumb = $game['thumb'];
                            $link = $game['link'];
                            $date = $game['date'];
                            $aspectRatio = $game['aspect_ratio'];
                            $categories = $game['categories'];
                            $orientation = $game['orientation'];

                            $posts = get_posts(array(
                                'numberposts'	=> -1,
                                'post_type'		=> $PTplural,
                                'meta_query'	=> array(
                                    array(
                                        'key'	  	=> '_package_id',
                                        'value'	  	=> $packageId,
                                        'compare' 	=> '='
                                    )
                                )
                            ));

                            if(!$posts) {
                                $catById = array();
                                foreach($categories as $cat){
                                    $termObj = get_term_by('name', $cat, 'category');
                                    array_push($catById, $termObj -> term_id);
                                }

                                $postArg = array(
                                    'post_title' => $name,
                                    'post_content' => $description,
                                    'post_status' => 'publish',
                                    'post_author' => $currentUserId,
                                    'post_type' => $PTplural,
                                    'tax_input' => array('category' => $catById)
                                );
                                $insertedPostID = wp_insert_post($postArg);
                                if ($insertedPostID) {
                                    add_post_meta($insertedPostID, '_package_id', $packageId, true);
                                    add_post_meta($insertedPostID, '_aspect_ratio', $aspectRatio, true);
                                    add_post_meta($insertedPostID, '_url', $link, true);
                                    add_post_meta($insertedPostID, '_date', $date, true);
                                    add_post_meta($insertedPostID, '_orientation', $orientation, true);

                                    setGameFeaturedImage($insertedPostID, $thumb);
                                }
                            } ?>

                            <tr>
                                <td><?php echo $k; ?></td>
                                <td><?php echo $packageId; ?></td>
                                <td><?php echo $name; ?></td>
                                <td><?php echo $description; ?></td>
                                <td><img src="<?php echo $thumb; ?>" height="64" alt=""></td>
                                <td><?php echo $link; ?></td>
                                <td><?php echo $date; ?></td>
                                <td><?php echo $aspectRatio; ?></td>
                                <td><?php echo implode(', ', $categories); ?></td>
                                <td><?php echo $orientation; ?></td>
                            </tr>
                        <?php } ?>
                    <tbody>
                </table>
            <?php } ?>
        </div>
    </div>
<?php }