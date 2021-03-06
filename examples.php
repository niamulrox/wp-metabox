<?php

/**
 * Example usage of a custom metabox with several postmeta types included
 */
class Example_Metabox extends WP_Metabox {
    public function __construct( $key, PostMetaFactory $post_meta_factory, $args = array() ) {
        parent::__construct( $key, $post_meta_factory, $args );

        # A basic text box called 'test'
        $this->metadata['test'] = $post_meta_factory->create( 'test' );

        # A select menu with custom labels
        $this->metadata['label-select'] = $post_meta_factory->create(
            'label-select',
            array(
                'label' => __('Select Menu with Custom Labels', 'wp-metabox' ),
                'placeholder' => 'Pick one',
                'type' => 'select',
                'choices' => array( 
                    'one' => 'ONE',
                    'two' => 'Tw0',
                    'three' => '3rEEE'
                ),
            )
        );

        # checkbox and radio examples
        $this->metadata['radio'] = $post_meta_factory->create(
            'radio',
            array(
                'label' => __('Basic Radio Button', 'wp-metabox'),
                'type' => 'radio',
                'choices' => array( 'one', 'two', 'three' ),
            )
        );

        # an image upload using the media uploader
        $this->metadata['image_upload'] = $post_meta_factory->create( 'image_upload', array( 'type' => 'media' ) );

        # a second image upload
        $this->metadata['second_upload'] = $post_meta_factory->create( 'second_upload', array( 'type' => 'media', 'label' => __( 'Second Upload', 'wp-metabox' ) ) );

        add_filter( 'the_content' , array($this, 'display') );
    }

    # displays some of the metadata automatically on the content
    public function display( $content ) {
        global $post;

        if (get_post_meta($post->ID, 'test', true) != '') {
            $content = get_post_meta($post->ID, 'test', true) . $content;
        }
        return $content;
    }
}

/**
 * Example Custom Content Type
 * Creates a sample custom content type using WP Metabox to create custom postmeta boxes
 */
class Example_Content_Type extends WP_ContentType {

    function __construct( $key = 'example-content-type', $args = array( 'singular' => 'Content Type') ) {

        # registers the post type with provided arguments
        parent::__construct( $key, $args );

        $postmeta_factory = WP_PostMetaFactory::get_instance();

        # creates a simple metabox with one url input using WP_SimpleMetabox
        $this->metaboxes['project-url'] = new WP_SimpleMetabox( 'project-url', $postmeta_factory, array (
                'label' => __('Project URL', 'wp-metabox' ),
                'type' => 'url',
                'posttype' => $this->key,
                'placeholder' => 'Enter your URL here.'
            )
        );

        # creates another simple metabox with one text input using WP_SimpleMetabox
        $this->metaboxes['project-date'] = new WP_SimpleMetabox( 'project-date', $postmeta_factory, array (
                'label' => __( 'Project Date', 'wp-metabox' ),
                'posttype' => $this->key,
                'type' => 'date'
            )
        );

        # creates a simple metabox with an ordered list
        $this->metaboxes['ordered-list'] = new WP_SimpleMetabox( 'ordered-list', $postmeta_factory, array (
                'label' => __( 'Ordered List', 'wp-metabox' ),
                'posttype' => $this->key,
                'type' => 'ordered-list'
            )
        );

        $postmeta_factory->register_postmeta_type( 'custom-ordered', 'OrderedGroup' );

        # creates a simple metabox with an ordered list
        $this->metaboxes['custom-ordered'] = new WP_SimpleMetabox( 'custom-ordered', $postmeta_factory, array (
                'label' => __( 'Ordered Multi Text Inputs', 'wp-metabox' ),
                'posttype' => $this->key,
                'type' => 'custom-ordered'
            )
        );

    }

}

# Example customized ordered list with two text areas
class OrderedGroup extends WP_OrderedListMeta {
    public function display_postmeta( $post_id ) {
        if ( ! $data ) $data = get_post_meta( $post_id, $this->key, true );

        if ( ! is_array( $data ) ) $data = array();

        $first = $data[ 'first' ];
        $next = $data[ 'next' ];

        $first[] = '';
        
        echo "<p>";

        $this->display_label();

        echo "</p><ul class=\"wp-metabox-ordered-list\">";

        foreach ( $first as $key => $value ) {
            $data[ $this->key ][ 'first' ] = $value;
            $data[ $this->key ][ 'next' ] = $next[ $key ];
            $this->display_item( $data );
        }

        echo "</ul><p>";

        echo "<button class=\"button button-large wp-metabox-add-new\">Add New</button>";

        $this->display_description();

        echo "</p>";

    }

    protected function display_input( $data ) {

        if ( ! is_array( $data ) ) $data = array();

        echo "<input type=\"{$this->input_type}\" class=\"wp-metabox-input\" name=\"{$this->key}[first][]\" value=\"{$data[$this->key]['first']}\" maxlength=\"{$this->max_length}\">";

        echo "<input type=\"{$this->input_type}\" class=\"wp-metabox-input\" name=\"{$this->key}[next][]\" value=\"{$data[$this->key]['next']}\" maxlength=\"{$this->max_length}\">";

    }

    public function update( $post_id, $data ) {

        if ( ! is_array( $data ) ) $data = array();

        foreach ( $data[ 'first' ] as $key => $value ) {
            if ( $value == '' && $data[ 'next' ][$key] == '' ) {
                unset( $data[ 'first' ][$key] );
                unset( $data[ 'next' ][$key] );
            }
        }
        
        parent::update( $post_id, $data );
    }
}

function init_example_content_type() {
    global $example_content_type;
    $example_content_type = new Example_Content_Type();

    # add the Example_Metabox to the example-content-type content type
    $example = new Example_Metabox(
        'test',
        WP_PostMetaFactory::get_instance(),
        array(
            'label' => __('Example Metabox', 'wp-metabox' ),
            'posttype' => 'example-content-type',
        )
    );
}

add_action( 'init', 'init_example_content_type');