<?php
/**
 * Image XML Sitemap
 *
 * @author Cor van Noorloos
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link https://github.com/corvannoorloos/google-sitemap-image
 *
 * @wordpress
 * Plugin Name: Image XML Sitemap
 * Plugin URI: https://github.com/corvannoorloos/google-sitemap-image
 * Description: With image search, just as with web search, Google's goal is to provide the best and most relevant search results to our users. Following Google's <a href="http://www.google.com/support/webmasters/bin/answer.py?answer=35769">Webmaster Guidelines</a> and <a href="http://www.google.com/support/webmasters/bin/answer.py?answer=114016">best practices for publishing images</a> can increase the likelihood that your images will be returned in those search results. In addition, you can also use Google's image extensions for Sitemaps to give Google additional information about the images on your site's URLs.
 * Author: Cor van Noorloos
 * Version: 0.1.1
 * Author URI: http://corvannoorloos.com/
 */

add_action( 'template_redirect', 'google_sitemap_image' );
/**
 * Image XML Sitemap
 *
 * @since 0.1.1
 *
 * @global type $wpdb
 *
 * @return type
 */
function google_sitemap_image() {
  if ( ! preg_match( '/sitemap\-image\.xml$/', $_SERVER['REQUEST_URI'] ) ) {
    return;
  }
  global $wpdb;
  $posts = $wpdb->get_results( "SELECT ID, post_title, post_excerpt, post_content, post_parent
    FROM $wpdb->posts
    WHERE post_status = 'publish'
    AND post_password = ''
    AND ( post_type = 'post' OR post_type = 'page' OR post_type = 'post_type' )
    ORDER BY post_type DESC, post_modified DESC
    LIMIT 1000" );
  header( "HTTP/1.1 200 OK" );
  header( 'X-Robots-Tag: noindex, follow', true );
  header( 'Content-Type: text/xml' );
  echo '<?xml version="1.0" encoding="' . get_bloginfo( 'charset' ) . '"?>' . "\n";
  $xml = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";
  foreach ( $posts as $post ) {
    preg_match( '/<img [^>]*src=[\'"]([^\'"]+)[\'"][^>]*>/', $post->post_content, $img );
    if ( ! array_key_exists( 1, $img ) )
      continue;
    $image_url = trim( $img[1] );
    if ( $image_url ) {
      $xml .= "<url>\n";
      $xml .= "\t<loc>" . get_permalink( $post->ID ) . "</loc>\n";
      $xml .= "\t<image:image>\n";
      $xml .= "\t\t<image:loc>" . $img[1] . "</image:loc>\n";
      if ( $post->post_title ) {
        $xml .= "\t\t<image:title>" . htmlentities( $post->post_title ) . "</image:title>\n";
      }
      if ( $post->post_excerpt ) {
        $xml .= "\t\t<image:caption>" . htmlentities( $post->post_excerpt ) . "</image:caption>\n";
      }
      $xml .= "\t</image:image>\n";
      $xml .= '</url>' . "\n";
    }
  }
  $xml .= '</urlset>';
  echo ( "$xml" );
  exit();
}