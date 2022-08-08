<?php

namespace Drupal\taxi\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for a taxi module.
 */
class TaxiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'taxi_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $settings = $this->config('taxi.settings');
    $form['timer'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Timer Notify Settings'),
    ];
    $form['timer']['enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable timer notification'),
      '#description' => $this->t('Send a message with timer after submit form'),
      '#default_value' => $settings->get('timer.enable'),
    ];
    $form['timer']['mes_head'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Title'),
      '#rows' => 2,
      '#resizable' => 'none',
      '#description' => $this->t('Title notification text'),
      '#default_value' => $settings->get('timer.mes_head'),
    ];
    $form['timer']['mes_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Text'),
      '#rows' => 3,
      '#resizable' => 'none',
      '#description' => $this->t('Notification text'),
      '#default_value' => $settings->get('timer.mes_text'),
    ];
    $form['timer']['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Position'),
      '#options' => [
        'bottom-left' => 'bottom-left',
        'bottom-right' => 'bottom-right',
        'bottom-center' => 'bottom-center',
        'top-left' => 'top-left',
        'top-right' => 'top-right',
        'top-center' => 'top-center',
        'mid-center' => 'mid-center',
      ],
      '#description' => $this->t('property can be used to specify the position'),
      '#default_value' => $settings->get('timer.position'),
    ];
    $form['timer']['bg_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Background color'),
      '#description' => $this->t('Set background color in HEX format'),
      // '#default_value' => $settings->get('timer.bg_color'),
      '#default_value' => '#E5BE01',
    ];
    $form['timer']['text_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Text color'),
      '#description' => $this->t('Set text color in HEX format'),
      '#default_value' => '#000000',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('taxi.settings')
      ->set('timer.enable', (bool) $form_state->getValue('enable'))
      ->set('timer.mes_head', $form_state->getValue('mes_head'))
      ->set('timer.mes_text', $form_state->getValue('mes_text'))
      ->set('timer.position', $form_state->getValue('position'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['taxi.settings'];
  }

}
