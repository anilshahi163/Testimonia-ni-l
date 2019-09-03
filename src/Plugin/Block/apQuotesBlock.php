<?php

namespace Drupal\testimonianil\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 *
 * @Block(
 *  id = "Testimonianil_block",
 *  admin_label = @Translation("Testimonianil"),
 * )
 */
class apQuotesBlock extends BlockBase {

  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'ap_quotes_items' => [],
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $items = [];
    if ($config['ap_quotes_items']) {
      $items = Node::loadMultiple($config['ap_quotes_items']);
    }

    $form['#tree'] = TRUE;

    $form['items_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('List of quotes items'),
      '#prefix' => '<div id="items-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    if (!$form_state->has('num_quotes_items')) {
      $form_state->set('num_quotes_items', count($config['ap_quotes_items']));
    }
    $count_slider_items = $form_state->get('num_quotes_items');
    for ($i = 0; $i < $count_slider_items; $i++) {
      $items = array_values($items);
      $form['items_fieldset']['items'][$i] = [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'node',
        '#title' => t('Quote Author'),
        '#description' => t('Use autocomplete to find it'),
        '#selection_handler' => 'default',
        '#selection_settings' => array(
          'target_bundles' => array('testimonials'),
        ),
        '#default_value' => $items[$i],
        '#draggable' => TRUE,

      ];
    }

    // $form['items_fieldset']['select_layout'] = [
    //   '#type' => 'select',
    //   '#title' => $this->t('Select layout'),
    //   '#options' => [
    //     '1' => $this->t('Layout 1'),
    //     '2' => $this->t('Layout 2'),
    //     '3' => $this->t('Layout 3'),
    //   ],
    //   '#default_value' => $config['select_layout'],
    // ];

    $form['items_fieldset']['actions'] = [
      '#type' => 'actions',
    ];

    $form['items_fieldset']['actions']['add_slider_item'] = [
      '#type' => 'submit',
      '#value' => t('Add Quote Item'),
      '#submit' => [[$this, 'addOne']],
      '#ajax' => [
        'callback' => [$this, 'addSliderItemCallback'], 
        'wrapper' => 'items-fieldset-wrapper',
      ],
    ];

    if ($count_slider_items > 1) {
      $form['items_fieldset']['actions']['remove_slider_item'] = [
        '#type' => 'submit',
        '#value' => t('Remove Quote Item'),
        '#submit' => [[$this, 'removeSliderCallback']],
        '#ajax' => [
          'callback' => [$this, 'addSliderItemCallback'], 
          'wrapper' => 'items-fieldset-wrapper',
        ]
      ];
    }

    return $form;
  }

  public function addOne(array &$form, FormStateInterface $form_state) {
    $count_slider_items = $form_state->get('num_quotes_items');
    $add_button = $count_slider_items + 1;
    $form_state->set('num_quotes_items', $add_button);
    $form_state->setRebuild();
  }

  public function addSliderItemCallback(array &$form, FormStateInterface $form_state) {
    return $form['settings']['items_fieldset'];
  }

  public function removeSliderCallback(array &$form, FormStateInterface $form_state) {
    $count_slider_items = $form_state->get('num_quotes_items');
    if ($count_slider_items > 1) {
      $remove_button = $count_slider_items - 1;
      $form_state->set('num_quotes_items', $remove_button);
    }
    $form_state->setRebuild();
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      if ($key === 'items_fieldset') {
        if (isset($value['items'])) {
          $items = $value['items'];
          foreach ($items as $key => $item) {
            if ($item === '' || !$item) {
              unset($items[$key]);
            }
          }
          $this->configuration['ap_quotes_items'] = $items;
        }

        // if (isset($value['select_layout'])) {
        //   $this->configuration['select_layout'] = $value['select_layout'];
        // }
      }
    }
  }

  public function build() {
    $nodes = [];

    if (isset($this->configuration['ap_quotes_items'])) {
      if (count($this->configuration['ap_quotes_items']) > 0) {
        $nids = $this->configuration['ap_quotes_items'];
        $nodes = Node::loadMultiple($nids);
      }
    }

    return array(
      '#theme' => 'ap_quotes_block',
      '#nodes' => $nodes,
      '#attached' => array(
        'library' => array(
          'testimonianil/testimonianil_libraries',
        ),   
        'drupalSettings' => array(
         'myVar' => 'test2313'
       ), 
      ),
    );
  }
}