<?php

namespace Drupal\facetapi_tabs\Plugin\facets\widget;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\Annotation\FacetsWidget;
use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\LinksWidget;
use Drupal\Core\Url;

/**
 * The links widget.
 *
 * @FacetsWidget(
 *   id = "tabs",
 *   label = @Translation("List of Tabs"),
 *   description = @Translation("A simple widget that shows a list of facet tabs"),
 * )
 */
class TabWidget extends LinksWidget {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'show_all' => 1,
      'show_count' => 1,
      'show_all_label' => 'All',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {

    // Inherit link facet items build.
    $build = parent::build($facet);

    kint($facet->getUrlAlias());
    $facet_key = $facet->getUrlAlias();

    // Identifier to check if this tab facet filter has been set(TRUE) or not(FALSE) the current page/search.
    $current_facet_set = FALSE;

    // Get all the query parameters of current page.
    $query = \Drupal::request()->query->all();

    // Filter only facets query parameters.
    $category_filter = \Drupal::request()->query->get('f');

    // Get the current path without key value pairs i.e arg 1, 2 etc.
    $current_path = \Drupal::service('path.current')->getPath();

    // If facet filter is set, remove the filter of facet with type tabs only.
    if (isset($category_filter)) {

      $category_filter = \Drupal::request()->query->all()['f'];
      foreach ($category_filter as $filter_key => $filter_value) {
        if (strpos($filter_value, $facet_key) === 0) {
          unset($category_filter[$filter_key]);
          $current_facet_set = TRUE;
        }
      }
      $query['f'] = $category_filter;
    }

    // Generate url with all the results of current search without this tab facet filter.
    $url = Url::fromUri("internal:" . $current_path, ['query' => $query]);

    // Set the custom theme for tab facets.
    $build['#theme'] = 'facetapi_tabs';

    $all = [
      '#type' => 'link',
      '#title' => [
        '#value' => $this->defaultConfiguration()['show_all_label'],
        '#facet' => $facet,
        '#is_active' => $current_facet_set ? FALSE : TRUE,
      ],
      '#url' => $url,
    ];

    array_unshift($build['#items'], $all);

    foreach ($build['#items'] as $key => $item) {
      $build['#items'][$key]['#title']['#theme'] = 'facetapi_result_item';
      if ($build['#items'][$key]['#title']['#is_active']) {
        $build['#items'][$key]['#url']->setOption('attributes', ['class' => 'is-active']);
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $form = parent::buildConfigurationForm($form, $form_state, $facet);
    $form['show_all_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Show All label'),
      '#default_value' => $this->getConfiguration()['show_all_label'],
    ];
    return $form;
  }

}
