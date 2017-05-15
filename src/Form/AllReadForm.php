<?php

/**
 * @file
 * Contains \Drupal\messenger\Form\AllReadForm.
 */

namespace Drupal\messenger\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AllReadForm extends FormBase {
    
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'allRead_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['allRead'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Read all'),
            '#button_type' => 'primary',
            '#ajax' => [
                'callback' => '\Drupal\messenger\Controller\AjaxAllReadController::allReadResponse',
                'event' => 'click',
                'wrapper' => 'all',
                'progress' => [
                    'type' => 'throbber',
                    'message' => 'Sending the message',
                ]
            ],
        );
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {        
    }   
}
