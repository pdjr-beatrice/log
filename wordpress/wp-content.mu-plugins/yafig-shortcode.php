<?php
# Adds a [yafig] shortcode useful for displaying category galleries.
#
# [yafig category='category' tag='tags' size='size' rows=n columns=n]
# [yafig images='' ]

function yafig_shortcode_handler($atts) {
  $retval = '';
  $args = shortcode_atts(array('size' => 'medium', 'rows' => 0, 'columns' => 4, 'category' => '', 'tag' => '', 'names' => '', 'type' => ''), $atts);

  $query_args = array('posts_per_page' => -1);

  if (in_array($args['type'], array('attachment', 'image'))) {
    $query_args['post_type'] = 'attachment';
    $query_args['post_status'] = 'any';
    $query_tags['post_mime_type'] = array('image/jpeg');
  }
  if ($args['category'] != '') $query_args['category_name'] = $args['category'];
  if ($args['tag'] != '') $query_args['tag'] = $args['tag'];
  if ($args['names'] != '') $query_args['post_name__in'] = explode(',', $args['names']); 
  
  $query = new WP_query($query_args);
  $posts = $query->posts;

  $ids = array();
  global $yafig_shortcode_metadata;
  $yafig_shortcode_metadata = array();

  foreach($posts as $post) {
    if (in_array($args['type'], array('attachment', 'image'))) {
      $ids[] = $post->ID;
    } else {  
      if (has_post_thumbnail($post)) {
        $image_id = get_post_thumbnail_id($post);
        if ($image_id) {
          $ids[] = $image_id;
          $yafig_shortcode_metadata[$image_id] = array('title' => get_the_title($post), 'link' => get_the_permalink($post));
        }
      }
    }
    if (($args['rows'] != 0) && (count($ids) >= ($args['rows'] * $args['columns']))) break;
  }

  if (count($ids)) {
    add_filter('attachment_link', 'yafig_shortcode_rewrite_gallery_item_attachment_link', 10, 2); // Make images link to posts instead of attachments.
    add_filter('wp_get_attachment_image_attributes', 'yafig_shortcode_add_title_to_gallery_item', 10, 3);
    $args = array_merge(array('ids' => $ids, 'order' => 'DESC'), $args);
    $retval = gallery_shortcode($args);
    remove_filter('wp_get_attachment_image_attributes', 'yafig_shortcode_add_title_to_gallery_item');
    remove_filter('attachment_link', 'yafig_shortcode_rewrite_gallery_item_attachment_link'); // Make images link to posts instead of attachments.
  }

  return($retval);
}

// Filter the HTML attributes of each img tag in the gallery, adding a
// title attribute which points to the title of the linked post.
function yafig_shortcode_add_title_to_gallery_item($attr, $attachment, $size) {
  global $yafig_shortcode_metadata;
  $attr['title'] = $yafig_shortcode_metadata[$attachment->ID]['title'];
  return($attr);
}

// Filter the link wrapper of each img tag in the gallery so that it
// points to the containing the attachment's parent post.
function yafig_shortcode_rewrite_gallery_item_attachment_link( $link, $post_id ) {
  global $yafig_shortcode_metadata;
  return($yafig_shortcode_metadata[$post_id]['link']);
}

add_shortcode('yafig', 'yafig_shortcode_handler');

?>

