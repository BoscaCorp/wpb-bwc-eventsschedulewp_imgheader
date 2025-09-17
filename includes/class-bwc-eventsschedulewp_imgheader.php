<?php
if (!defined('ABSPATH')) exit;

class BWC_Eventsschedulewp_imgheader_Plugin {

  public function __construct() {
    // UI WPBakery
    add_action('vc_before_init', [$this, 'vc_map_element']);

    // Shortcode
    add_shortcode('bwc_simple_img_header', [$this, 'render_shortcode']);

    // Assets (on enregistre uniquement, on n’enfile pas ici)
    add_action('wp_enqueue_scripts', [$this, 'register_assets']);
  }

  public function register_assets() {
    wp_register_style(
      'bwc-eventsschedulewp_imgheader',
      BWC_SIH_URL . 'public/css/style.css',
      [],
      BWC_SIH_VERSION
    );
  }

  public function vc_map_element() {
    if (!defined('WPB_VC_VERSION')) return;

    vc_map([
      'name'        => __('BWC Simple Img Header', 'wpb-bwc-eventsschedulewp_imgheader'),
      'base'        => 'bwc_simple_img_header',
      'description' => __('Image plein écran + titres + CTA événement', 'wpb-bwc-eventsschedulewp_imgheader'),
      'category'    => __('BWC Modules', 'wpb-bwc-eventsschedulewp_imgheader'),
      'icon'        => 'dashicons-format-image',
      'params'      => [
        [
          'type'       => 'attach_image',
          'heading'    => __('Image', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name' => 'image_id',
        ],
        [
          'type'       => 'textfield',
          'heading'    => __('Titre image', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name' => 'title',
        ],
        [
          'type'       => 'textfield',
          'heading'    => __('Sous-titre image', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name' => 'subtitle',
        ],
        [
          'type'       => 'textfield',
          'heading'    => __('Sous-titre 2 image', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name' => 'subsubtitle',
        ],
        [
          'type'       => 'textfield',
          'heading'    => __('Lien (mode “Lien”)', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name' => 'linkimg',
        ],
        [
          'type'       => 'textfield',
          'heading'    => __('Requête événement (texte libre)', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name' => 'event_name',
        ],
        [
          'type'       => 'textfield',
          'heading'    => __('Element ID', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name' => 'element_id',
          'group'      => __('Extra', 'wpb-bwc-eventsschedulewp_imgheader'),
        ],
        [
          'type'       => 'textfield',
          'heading'    => __('Extra class name', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name' => 'extra_class',
          'group'      => __('Extra', 'wpb-bwc-eventsschedulewp_imgheader'),
        ],
      ],
    ]);
  }

  private function sanitize_classes($classes) {
    $classes = trim((string)$classes);
    if ($classes === '') return '';
    $parts = preg_split('/\s+/', $classes);
    $safe  = array_map('sanitize_html_class', $parts);
    return implode(' ', array_filter($safe));
  }

  private function format_event_date_html(int $ts): string {
    if ($ts <= 0) return '';

    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    $tz     = wp_timezone();

    if (class_exists('IntlDateFormatter')) {
      $dayFmt  = new IntlDateFormatter($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE, $tz, null, 'EEEE');
      $dateFmt = new IntlDateFormatter($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE, $tz, null, 'd MMMM y');
      return sprintf(
        '<span class="prod-events_day">%s</span> <span class="prod-events_dates_contents">%s</span>',
        esc_html($dayFmt->format($ts)),
        esc_html($dateFmt->format($ts))
      );
    }

    if (function_exists('switch_to_locale')) switch_to_locale($locale);
    $day  = wp_date('l', $ts, $tz);
    $date = wp_date('d F Y', $ts, $tz);
    if (function_exists('restore_previous_locale')) restore_previous_locale();

    return sprintf(
      '<span class="prod-events_day">%s</span> <span class="prod-events_dates_contents">%s</span>',
      esc_html($day),
      esc_html($date)
    );
  }

  public function render_shortcode($atts, $content = null, $tag = '') {
    // Charge le CSS uniquement ici
    wp_enqueue_style('bwc-eventsschedulewp_imgheader');

    $atts = shortcode_atts([
      'image_id'     => '',
      'title'        => '',
      'subtitle'     => '',
      'subsubtitle'  => '',
      'linkimg'      => '',
      'event_name'   => 'none',
      'element_id'   => '',
      'extra_class'  => '',
    ], $atts, 'bwc_simple_img_header');

    $image_id    = intval($atts['image_id']);
    $img_url     = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
    $title       = wp_kses_post($atts['title']);
    $subtitle    = wp_kses_post($atts['subtitle']);
    $subsubtitle = wp_kses_post($atts['subsubtitle']);
    $linkimg     = esc_url_raw($atts['linkimg']);
    $eventName   = sanitize_text_field($atts['event_name']);
    $element_id  = sanitize_title($atts['element_id']);
    $extra_class = $this->sanitize_classes($atts['extra_class']);

    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    $is_en  = (stripos($locale, 'en') === 0);

    if ($eventName === 'link') {
      $reservationBtn = $is_en ? 'Discover the website' : 'Découvrir le site';
      $soonBtn        = $is_en ? 'Online soon' : 'Site prochainement en ligne';
    } else {
      $reservationBtn = $is_en ? 'Book' : 'Réserver';
      $soonBtn        = $is_en ? 'On sale soon' : 'En vente prochainement';
    }

    $events = [];
    if ($eventName !== 'none') {
      $now  = new DateTime('now', wp_timezone());
      $args = [
        'post_type'      => 'class',
        'posts_per_page' => -1,
        'order'          => 'ASC',
        'post_status'    => 'publish',
        's'              => $eventName,
      ];
      $query = new WP_Query($args);

      if ($query->have_posts()) {
        while ($query->have_posts()) {
          $query->the_post();
          $meta = get_post_meta(get_the_ID());
          $ts   = isset($meta['_wcs_timestamp'][0]) ? intval($meta['_wcs_timestamp'][0]) : 0;
          if ($ts > 0 && $ts >= $now->getTimestamp()) {
            $events[] = [
              'date' => $this->format_event_date_html($ts),
              'link' => !empty($meta['_wcs_reservation_link'][0]) ? esc_url($meta['_wcs_reservation_link'][0]) : '',
            ];
          }
        }
        wp_reset_postdata();
      }
    }

    $output  = '<div class="simple-img-header-container ' . esc_attr($extra_class) . '" ' . ($element_id ? 'id="'.esc_attr($element_id).'"' : '') . '>';
    if ($img_url) {
      $output .= '<img src="' . esc_url($img_url) . '" alt="' . esc_attr(wp_strip_all_tags($title ?: '')) . '"/>';
    }

    $output .= '<div class="simple-img-header-centered">';
    if ($title)       $output .= '<h2 class="simple-img-header-title">' . $title . '</h2>';
    if ($subtitle)    $output .= '<h3 class="simple-img-header-subtitle">' . $subtitle . '</h3>';
    if ($subsubtitle) $output .= '<h4 class="simple-img-header-subsubtitle">' . $subsubtitle . '</h4>';

    if (!empty($events)) {
      $first = $events[0];
      $output .= '<div class="prod-events_container">';
      if (empty($first['link'])) {
        $output .= '<div class="prod-events_link_empty"><span>' . esc_html($soonBtn) . '</span></div>';
      } else {
        $output .= '<div class="prod-events_link"><a href="' . $first['link'] . '">' . esc_html($reservationBtn) . '</a></div>';
      }
      $output .= '<div class="prod-events_date">' . $first['date'] . '</div>';
      $output .= '</div>';
    } elseif ($eventName === 'link') {
      $output .= '<div class="prod-events_container">';
      if (!empty($linkimg)) {
        $output .= '<div class="prod-events_link"><a href="' . esc_url($linkimg) . '">' . esc_html($reservationBtn) . '</a></div>';
      } else {
        $output .= '<div class="prod-events_link_empty"><span>' . esc_html($soonBtn) . '</span></div>';
      }
      $output .= '</div>';
    }

    $output .= '</div></div>';
    return $output;
  }
}

if (class_exists('WPBakeryShortCode') && !class_exists('WPBakeryShortCode_Bwc_Simple_Img_Header')) {
  class WPBakeryShortCode_Bwc_Simple_Img_Header extends WPBakeryShortCode {}
}
