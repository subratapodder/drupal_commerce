<?php

namespace Drupal\custom_commerce\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a custom message pane.
 *
 * @CommerceCheckoutPane(
 *   id = "custom_commerce_custom_mail",
 *   label = @Translation("Custom Mail"),
 * )
 */
class CustomMailPane extends CheckoutPaneBase {
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'mail_subject' => 'Mail Subject',
      'mail_body' => 'Mail Body',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
    return $this->t('Thank you mail on successful order.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['mail_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mail Subject'),
      '#default_value' => $this->configuration['mail_subject'],
    ];
    $form['mail_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail Body'),
      '#default_value' => $this->configuration['mail_body'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['mail_subject'] = $values['mail_subject'];
      $this->configuration['mail_body'] = $values['mail_body'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $mail_to = $this->order->getEmail();
    if ($mail_to) {
      // Sending mail
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'custom_commerce';
      $key = 'thank_you_commerce';
      $to = $mail_to;
      $params['subject'] = $this->configuration['mail_subject'];
      $params['message'] = $this->configuration['mail_body'];
      $langcode = 'en';
      $send = true;
      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      if ($result['result'] !== true) {
        $msg = "A thank you mail sent to you for this order.";
      }
      if ($result['result'] !== true) {
        $msg = "Unable to send mail.Contact the site administrator.";
      }
      else {
        $msg = "A thank you mail sent to you for this order.";
      }
    }
    else {
      $msg = "Something went wrong with your order.";
    }
    $pane_form['message'] = [
      '#markup' => $msg,
    ];
    return $pane_form;
  }
}