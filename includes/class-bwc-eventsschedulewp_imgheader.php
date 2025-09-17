<?php
if (!defined('ABSPATH')) exit;

class BWC_Eventsschedulewp_ImgHeader_Plugin {

  public function __construct() {
    // UI WPBakery
    add_action('vc_before_init', [$this, 'vc_map_element']);

    // Shortcode
    add_shortcode('bwc_simple_img_header', [$this, 'render_shortcode']);

    // Assets
    add_action('wp_enqueue_scripts', [$this, 'register_assets']);

    // Autocomplete (IMPORTANT: base = bwc_simple_img_header, param_name = event_id)
    add_filter('vc_autocomplete_bwc_simple_img_header_event_id_callback', [$this, 'autocomplete_event_search'], 10, 1);
    add_filter('vc_autocomplete_bwc_simple_img_header_event_id_render',   [$this, 'autocomplete_event_render'], 10, 1);
  }

  public function register_assets() {
    wp_register_style('bwc-eventsschedulewp_imgheader', BWC_ESH_URL . 'public/css/style.css', [], BWC_ESH_VERSION);
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
          'type'        => 'attach_image',
          'heading'     => __('Image', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name'  => 'image_id',
          'description' => __('Sélectionne une image (attach_image retourne un ID).', 'wpb-bwc-eventsschedulewp_imgheader'),
        ],
        [
          'type'        => 'textfield',
          'heading'     => __('Titre image', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name'  => 'title',
        ],
        [
          'type'        => 'textfield',
          'heading'     => __('Sous-titre image', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name'  => 'subtitle',
        ],
        [
          'type'        => 'textfield',
          'heading'     => __('Sous-titre 2 image', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name'  => 'subsubtitle',
        ],
        [
          'type'        => 'textfield',
          'heading'     => __('Lien direct (mode “lien”)', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name'  => 'linkimg',
          'description' => __('URL utilisée si aucun événement n’est sélectionné et que tu veux un CTA “Découvrir le site”.', 'wpb-bwc-eventsschedulewp_imgheader'),
        ],

        // === Sélection DYNAMIQUE d'un événement (CPT "class") ===
        [
          'type'        => 'autocomplete',
          'heading'     => __('Spectacle (CPT "class")', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name'  => 'event_id', // clé des hooks autocomplete
          'settings'    => [
            'multiple'       => false,
            'min_length'     => 1,
            'delay'          => 200,
            'unique_values'  => true,
            'display_inline' => true,
          ],
          'description' => __('Tape pour rechercher un post du type "class", puis sélectionne-le.', 'wpb-bwc-eventsschedulewp_imgheader'),
        ],

        // === Fallback “champ libre” si tu ne veux pas utiliser l’autocomplete ===
        [
          'type'        => 'textfield',
          'heading'     => __('Requête événement (texte libre - fallback)', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name'  => 'event_query',
          'description' => __('Ex: "gala", "cendrillon"… Utilisé uniquement si aucun Spectacle n’est sélectionné.', 'wpb-bwc-eventsschedulewp_imgheader'),
        ],

        // Extras
        [
          'type'        => 'textfield',
          'heading'     => __('Element ID', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name'  => 'element_id',
          'group'       => __('Extra', 'wpb-bwc-eventsschedulewp_imgheader'),
        ],
        [
          'type'        => 'textfield',
          'heading'     => __('Extra class name', 'wpb-bwc-eventsschedulewp_imgheader'),
          'param_name'  => 'extra_class',
          'group'       => __('Extra', 'wpb-bwc-eventsschedulewp_imgheader'),
        ],
      ],
    ]);
  }

  // ==== Autocomplete: recherche ====
  public function autocomplete_event_search($query) {
    $term = isset($query['value']) ? sanitize_text_field($query['value']) : '';
    if ($term === '') return [];

    $q = new WP_Query([
      'post_type'      => 'class',
      's'              => $term,
      'posts_per_page' => 20,
      'post_status'    => 'publish',
      'orderby'        => 'date',
      'order'          => 'DESC',
    ]);

    $out = [];
    while ($q->have_posts()) {
      $q->the_post();
      $id    = get_the_ID();
      $title = get_the_title($id);
      $ts    = (int) get_post_meta($id, '_wcs_timestamp', true);
      $date  = $ts ? ' — '. wp_date('d M Y', $ts) : '';
      $out[] = [
        'value' => (string) $id,
        'label' => $title . $date . ' (#'.$id.')',
      ];
    }
    wp_reset_postdata();
    return $out;
  }

  // ==== Autocomplete: rendu de la sélection ====
  public function autocomplete_event_render($query) {
    $id = isset($query['value']) ? (int) $query['value'] : 0;
    if (!$id) return false;
    $post = get_post($id);
    if (!$post || $post->post_type !== 'class') return false;

    $ts   = (int) get_post_meta($id, '_wcs_timestamp', true);
    $date = $ts ? ' — '. wp_date('d M Y', $ts) : '';
    return [
      'value' => (string) $id,
      'label' => get_the_title($id) . $date . ' (#'.$id.')',
    ];
  }

  private function sanitize_classes($classes) {
    $classes = trim((string)$classes);
    if ($classes === '') return '';
    $parts = preg_split('/\s+/', $classes);
    $safe  = array_map('sanitize_html_class', $parts);
    return implode(' ', array_filter($safe));
  }

  public function render_shortcode($atts, $content = null, $tag = '') {
    $atts = shortcode_atts([
      'image_id'    => '',
      'title'       => '',
      'subtitle'    => '',
      'subsubtitle' => '',
      'linkimg'     => '',
      'event_id'    => '',   // NEW (autocomplete)
      'event_query' => '',   // fallback libre
      'element_id'  => '',
      'extra_class' => '',
    ], $atts, 'bwc_simple_img_header');

    // Données
    $image_id    = intval($atts['image_id']);
    $img_url     = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
    $title       = wp_kses_post($atts['title']);
    $subtitle    = wp_kses_post($atts['subtitle']);
    $subsubtitle = wp_kses_post($atts['subsubtitle']);
    $linkimg     = esc_url_raw($atts['linkimg']);
    $event_id    = (int) $atts['event_id'];
    $event_query = sanitize_text_field($atts['event_query']);
    $element_id  = sanitize_title($atts['element_id']);
    $extra_class = $this->sanitize_classes($atts['extra_class']);

    // Libellés CTA
    $is_en          = (get_locale() === 'en_US');
    $reservationBtn = $is_en ? 'Book' : 'Réserver';
    $soonBtn        = $is_en ? 'On sale soon' : 'En vente prochainement';
    $discoverBtn    = $is_en ? 'Discover the website' : 'Découvrir le site';
    $onlineSoon     = $is_en ? 'Online soon' : 'Site prochainement en ligne';

    // Récupération de l'événement
    $events = [];

    // 1) Priorité à event_id (sélection via autocomplete)
    if ($event_id) {
      $post = get_post($event_id);
      if ($post && $post->post_type === 'class' && $post->post_status === 'publish') {
        $meta = get_post_meta($event_id);
        $ts   = isset($meta['_wcs_timestamp'][0]) ? (int) $meta['_wcs_timestamp'][0] : 0;

        $dateEvent = $ts ? sprintf(
          '<span class="prod-events_day">%s</span> <span class="prod-events_dates_contents">%s</span>',
          esc_html( wp_date('l', $ts) ),
          esc_html( wp_date('d F Y', $ts) )
        ) : '';

        $reservationLink = !empty($meta['_wcs_reservation_link'][0]) ? $meta['_wcs_reservation_link'][0] : '';
        $events[] = [
          'name' => get_the_title($event_id),
          'date' => $dateEvent,
          'link' => esc_url($reservationLink),
        ];
      }
    }
    // 2) Sinon fallback event_query (texte libre)
    elseif ($event_query !== '') {
      $now = new DateTime('now', wp_timezone());
      $q   = new WP_Query([
        'post_type'      => 'class',
        'posts_per_page' => -1,
        'order'          => 'ASC',
        'post_status'    => 'publish',
        's'              => $event_query,
      ]);
      if ($q->have_posts()) {
        while ($q->have_posts()) {
          $q->the_post();
          $id   = get_the_ID();
          $meta = get_post_meta($id);
          $ts   = isset($meta['_wcs_timestamp'][0]) ? (int) $meta['_wcs_timestamp'][0] : 0;

          if ($ts > 0 && $ts >= $now->getTimestamp()) {
            $dateEvent = sprintf(
              '<span class="prod-events_day">%s</span> <span class="prod-events_dates_contents">%s</span>',
              esc_html( wp_date('l', $ts) ),
              esc_html( wp_date('d F Y', $ts) )
            );
            $reservationLink = !empty($meta['_wcs_reservation_link'][0]) ? $meta['_wcs_reservation_link'][0] : '';
            $events[] = [
              'name' => get_the_title($id),
              'date' => $dateEvent,
              'link' => esc_url($reservationLink),
            ];
          }
        }
        wp_reset_postdata();
      }
    }

    // Enqueue CSS seulement si shortcode présent
    wp_enqueue_style('bwc-eventsschedulewp_imgheader');

    // HTML
    $output  = '';
    $output .= '<div class="simple-img-header-container ' . esc_attr($extra_class) . '" ' . ($element_id ? 'id="'.esc_attr($element_id).'"' : '') . '>';

      if ($img_url) {
        $output .= '<img src="' . esc_url($img_url) . '" alt="' . esc_attr(wp_strip_all_tags($title ?: '')) . '"/>';
      }

      $output .= '<div class="simple-img-header-centered">';
        if ($title)       $output .= '<h2 class="simple-img-header-title">' . $title . '</h2>';
        if ($subtitle)    $output .= '<h3 class="simple-img-header-subtitle">' . $subtitle . '</h3>';
        if ($subsubtitle) $output .= '<h4 class="simple-img-header-subsubtitle">' . $subsubtitle . '</h4>';

        // Bloc CTA + date
        if (!empty($events)) {
          $first = $events[0];
          $output .= '<div class="prod-events_container">';
            if (empty($first['link'])) {
              $output .= '<div class="prod-events_link_empty"><span>' . esc_html($soonBtn) . '</span></div>';
            } else {
              $output .= '<div class="prod-events_link"><a href="' . $first['link'] . '">' . esc_html($reservationBtn) . '</a></div>';
            }
            if (!empty($first['date'])) {
              $output .= '<div class="prod-events_date">' . $first['date'] . '</div>';
            }
          $output .= '</div>';
        } else {
          // Mode “lien” si linkimg fourni, sinon “bientôt”
          $output .= '<div class="prod-events_container">';
            if (!empty($linkimg)) {
              $output .= '<div class="prod-events_link"><a href="' . esc_url($linkimg) . '">' . esc_html($discoverBtn) . '</a></div>';
            } else {
              $output .= '<div class="prod-events_link_empty"><span>' . esc_html($onlineSoon) . '</span></div>';
            }
          $output .= '</div>';
        }

      $output .= '</div>'; // centered
    $output .= '</div>';   // container

    return $output;
  }
}

// Stub WPBakery aligné sur base = bwc_simple_img_header
if (class_exists('WPBakeryShortCode') && !class_exists('WPBakeryShortCode_Bwc_Simple_Img_Header')) {
  class WPBakeryShortCode_Bwc_Simple_Img_Header extends WPBakeryShortCode {}
}
