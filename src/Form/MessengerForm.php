<?php

/**
 * @file
 * Contains \Drupal\messenger\Form\MessengerForm.
 */

namespace Drupal\messenger\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class MessengerForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'messenger_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $output = '';
        $uid = \Drupal::currentUser()->id();
        $query = \Drupal::database()->select('users_field_data', 'ufd');
        $query->fields('ufd', ['name', 'uid']);
        $result = $query->execute();
        while ($row = $result->fetchAssoc()) {
            if (($row['name'] !== '') && ($row['uid'] !== $uid) ) {
                $output .=
                $row['name'] .= '^';
            }
        }
        $output1 = rtrim($output, "^");
        $name = explode('^', $output1);

        $form['message'] = array(
            '#type' => 'textfield',
            '#required' => TRUE
        );
        $form['receiver'] = array(
            '#type' => 'select',
            '#options' =>
                $name
            ,
            '#required' => TRUE,
        );
        $form['allUserSubmit'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Send'),
            '#button_type' => 'primary',           
            '#ajax' => [
                'callback' => '\Drupal\messenger\Controller\AjaxController::messagesResponse',
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
    /*public function validateForm(array &$form, FormStateInterface $form_state) {
        if (strlen($form_state->getValue('candidate_number')) < 10) {
            $form_state->setErrorByName('candidate_number', $this->t('Mobile number is too short.'));
        }
    }*/

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $output = '';
        $uid = \Drupal::currentUser()->id();
        $output1 = '';
        $query = \Drupal::database()->select('users_field_data', 'ufd');
        $query->fields('ufd', ['name', 'uid']);
        $result = $query->execute();
        while ($row = $result->fetchAssoc()) {
            if (($row['name'] !== '') && ($row['uid'] !== $uid) ) {
                $output .=
                $row['name'] .= '^';
                $output1 .=
                $row['uid'] .= '^';
            }
        }
        $output2 = rtrim($output, "^");
        $name = explode('^', $output2);
        $output3 = rtrim($output1, "^");
        $name3 = explode('^', $output3);
        $name5 = array_combine($name, $name3);
        $select_value = $form_state->getValue(['receiver']);
        $message_text = $form_state->getValue(['message']);

        $sender_id = \Drupal::currentUser()->id();

        $name2 = $name[$select_value];
        $receiver_id = $name5[$name2];

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
            $receiver_id,
            $message_text,
            $message_time,

        ]);
        $query->execute();

        $fromUser = \Drupal\user\Entity\User::load($sender_id);
        $fromName = $fromUser->get('name')->value;
        $toUser = \Drupal\user\Entity\User::load($receiver_id);
        $toMail = $toUser->get('mail')->value;
        $toName = $toUser->get('name')->value;
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

        //print_r($fromName);
        //kint($result);
    }
}