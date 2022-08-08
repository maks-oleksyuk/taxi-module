<?php

namespace Drupal\taxi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Our Form Class.
 */
class TaxiForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'taxi_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#prefix'] = '<div id="form-wrapper" class="taxi-form col-md-6 col-xs-12 ml-auto mr-auto">';
    $form['#suffix'] = '</div>';
    // Set Name.
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#placeholder' => $this->t("Enter Your Name"),
      '#required' => TRUE,
      '#maxlength' => 100,
      '#attributes' => [
        'data-disable-refocus' => 'true',
        'autocomplete' => 'off',
        'class' => ['taxi-email'],
      ],
    ];
    // Set Email.
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#placeholder' => $this->t("Enter Your Email"),
      '#maxlength' => 100,
      '#required' => TRUE,
      '#attributes' => [
        'data-disable-refocus' => 'true',
        'autocomplete' => 'off',
        'class' => ['taxi-email'],
      ],
    ];
    // Set Date and Time.
    $form['time'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Date and Time'),
      '#size' => 20,
      '#required' => TRUE,
      '#date_date_format' => 'd/m/Y',
      '#date_time_format' => 'H:m',
      '#attributes' => [
        'class' => ['taxi-time'],
      ],
    ];
    // Set Amount of Adults.
    $form['adults'] = [
      '#title' => $this->t("Amount of Adults"),
      '#type' => 'number',
      '#required' => TRUE,
      '#min' => 0,
      '#placeholder' => $this->t("Enter Amount of Adults"),
      '#attributes' => [
        'class' => ['taxi-adults'],
      ],
    ];
    // Set Amount of Children.
    $form['children'] = [
      '#title' => $this->t("Amount of Children"),
      '#type' => 'number',
      '#required' => FALSE,
      '#min' => 0,
      '#default_value' => 0,
      '#placeholder' => $this->t("Enter Amount of Children"),
      '#attributes' => [
        'class' => ['taxi-children'],
      ],
    ];
    // Set Road Choose: To/From Airport.
    $form['road'] = [
      '#type' => 'select',
      '#title' => $this->t("To/From Airport"),
      '#required' => TRUE,
      '#options' => [
        'To' => $this->t('To'),
        'From' => $this->t('From'),
      ],
      '#attributes' => [
        'class' => ['taxi-road'],
      ],
    ];
    // Set Type of Tariff.
    $form['tariff'] = [
      '#type' => 'select',
      '#title' => $this->t("Your Tariff"),
      '#required' => FALSE,
      '#options' => [
        'Eco' => $this->t('Eco'),
        'Fast' => $this->t('Fast'),
        'Super-Fast' => $this->t('Super Fast'),
      ],
      '#attributes' => [
        'class' => ['taxi-road'],
      ],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Order Now'),
      '#attributes' => [
        'class' => ['btn', 'btn-more', 'taxi-submit'],
      ],
      '#ajax' => [
        'event' => 'click',
        'effect' => 'fade',
        'wrapper' => 'form-wrapper',
        'callback' => '::submitAjax',
      ],
    ];
    $form['#attached']['library'][] = 'taxi/taxi_library';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $name = $form_state->getValue('name');
    $email = $form_state->getValue('email');
    $adults = $form_state->getValue('adults');
    $children = $form_state->getValue('children');
    $road = $form_state->getValue('road');
    $requires_name = "/[-'A-Za-z\d ]/";
    $requires_email = '/\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,6}/';
    $length_name = strlen($name);
    $t = $form_state->getValue('time');
    $time = (gettype($t) == 'object') ? strtotime($t) : FALSE;
    $timestamp = time();
    // Get Previous Data.
    // We Don't Need an Empty Taxi.
    if ($adults == 0 && $children == 0) {
      $form_state->setErrorByName(
        'adults',
        $this->t(
          "Taxi: You Can't Book an Empty Taxi(."
        )
      );
    }
    // Child Can't Ride in Taxi Alone.
    if ($adults == 0 && $children != 0) {
      $form_state->setErrorByName(
        'children',
        $this->t(
          "Taxi: You Can't Let a Child Go Alone(."
        )
      );
    }
    // Invalid Time(Requested Time is in the Past).
    if ($time < $timestamp) {
      $form_state->setErrorByName(
        'time',
        $this->t(
          "Time: You Cannot Book a Taxi in the Past(."
        )
      );
    }
    // Invalid Time
    // (Difference Between Requested Time and Present is Less than 30 min).
    // PS: Only if You Going To the Airport.
    if ($road == 'To' && ($time - $timestamp < 30 * 60)) {
      $form_state->setErrorByName(
        'time',
        $this->t(
          'Time: The Difference Should Be at Least 30 Minutes if You Going To the Airport.
           Please, Give Our Driver Time to Get to You (.'
        )
      );
    }
    // Invalid Time
    // (Difference Between Requested Time and Present
    // Should be Less than 30 days).
    if ($time - $timestamp > 60 * 60 * 24 * 30) {
      $form_state->setErrorByName(
        'time',
        $this->t(
          'Time: The Limit for Ordering a Taxi in Advance is 30 days, No More(.'
        )
      );
    }
    // Invalid Name(Short Symbols).
    if ($length_name < 2) {
      $form_state->setErrorByName(
        'name',
        $this->t(
          "Name: Oh No! Your Name is Shorter Than 2 Symbols(. Don't be Shy, it's Alright."
        )
      );
    }
    // Invalid Name(Too Long).
    elseif ($length_name > 100) {
      $form_state->setErrorByName(
        'name',
        $this->t(
          'Name: Oh No! Your Name is Longer Than 100 Symbols(. Can You Cut it a Bit?'
        )
      );
    }
    // Invalid Name(False Symbols).
    if (!preg_match($requires_name, $name)) {
      $form_state->setErrorByName('name',
        $this->t(
          "Name: Oh No! In Your Name %title You False Symbols(. Acceptable is: A-Z, 0-9 _ and '.", [
            '%title' => $name,
          ]
        )
      );
    }
    // Invalid Email.
    if (!preg_match($requires_email, $email)) {
      $form_state->setErrorByName('email',
        $this->t(
          'Mail: Oh No! Your Email %title is Invalid(', ['%title' => $email]
        )
      );
    }
  }

  /**
   * AJAX validation and confirmation of the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array|\Drupal\core\Ajax\AjaxResponse
   *   Return form or Ajax response.
   */
  public function submitAjax(array $form, FormStateInterface $form_state): AjaxResponse|array {
    if ($form_state->hasAnyErrors()) {
      return $form;
    }
    $response = new AjaxResponse();
    // Save the data for the timer in cookie user.
    $t = strtotime($form_state->getValue('time'));
    $timer = new Cookie('timer', $t, $t, '/', NULL, 0, 0);
    $response->headers->setCookie($timer);
    $response->addCommand(new RedirectCommand('taxi'));
    return $response;
  }

  /**
   * Submits Form.
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Results from Form.
    // $request['name'] = $form_state->getValue('name');
    // $request['email'] = $form_state->getValue('email');.
    // $request['time'] = strtotime($form_state->getValue('time'));.
    // $request['adults'] = $form_state->getValue('adults');.
    // $request['children'] = $form_state->getValue('children');
    // $request['road'] = $form_state->getValue('road');
    // $request['tariff'] = $form_state->getValue('tariff');
    // $data = [.
    // 'name' => $request['name'],
    // 'email' => $request['email'],
    // 'time' => $request['time'],
    // 'adults' => $request['adults'],
    // 'children' => $request['children'],
    // 'road' => $request['road'],
    // 'tariff' => $request['tariff'],
    // 'timestamp' => time(),
    // ];
    // Pushes into DB.
    // Drupal::database()->insert('taxi')->fields($data)->execute();
    // Sends an Email.
    // $newMail = \Drupal::service('plugin.manager.mail');.
    // $mail = $newMail->mail('taxi', 'ordered', $request['email'], 'en', $request, NULL, TRUE);
    // Submits Message.
    $this->messenger()
      ->addStatus($this->t('You Booked a Taxi on %time. Wait Until the Taxi Driver Contacts You', [
        '%time' => $form_state->getValue('time'),
      ]));
  }

}
