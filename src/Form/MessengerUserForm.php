<?php

/**
 * @file
 * Contains \Drupal\messenger\Form\MessengerUserForm.
 */

namespace Drupal\messenger\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;



class MessengerUserForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'karakum_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['message'] = array(
            '#type' => 'textfield',
            '#title' => t('Write your message:'),
            '#required' => TRUE,
        );

        $form['userSubmit'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Send'),
            '#button_type' => 'primary',
            '#ajax' => [
                'callback' => '\Drupal\messenger\Controller\AjaxUserController::userResponse',
                'event' => 'click',
                'wrapper' => 'one-user',
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
    /*public function validateForm(array &$form, FormStateInterface $form_state) {
        if (strlen($form_state->getValue('candidate_number')) < 10) {
            $form_state->setErrorByName('candidate_number', $this->t('Mobile number is too short.'));
        }
    }*/

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $sender_id = \Drupal::currentUser()->id();

        $path = $current_path = \Drupal::service('path.current')->getPath();
        $path_trim = ltrim($path, "/");
        $path_explode = explode('/', $path_trim);
        $path_end = $path_explode[3];
        
        $message_text = $form_state->getValue(['message']);
        $message_time = date("Y.m.d H:i:s");

        $query = \Drupal::database()->insert('messenger_msg');
        $query->fields([
            'sender_id',
            'receiver_id',
            'message',
            'time'
        ]);

        $query->values([
            $sender_id,
            $path_end,
            $message_text,
            $message_time,

        ]);
        $query->execute();

        $toUser = \Drupal\user\Entity\User::load($path_end);
        $toMail = $toUser->get('mail')->value;
        $toName = $toUser->get('name')->value;
        $fromUser = \Drupal\user\Entity\User::load($sender_id);
        $fromName = $fromUser->get('name')->value;

        $siteName = \Drupal::request()->getHost();;

        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'messenger';
        $key = 'notice';
        $to = "" . $toMail . "";

        $params['body'] = 'testTeTestText';
        $params['subject'] = "Сообщение с сайта";
        $params['message'] = "" . $fromName ." has written to you: '" . $message_text . "'";
        $params['title'] = 'Dear, ' . $toName .'! You have new message on ' . $siteName . '!';
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;
        $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
        $form_state->setRebuild(FALSE);
        //print_r($path_end);
        //kint($path_end);
    }
}