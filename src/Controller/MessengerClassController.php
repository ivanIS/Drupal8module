<?php

namespace Drupal\messenger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;


/**
 * Controller for JS Ajax.
 */
class MessengerClassController extends ControllerBase {

    /*
     * {@inheritdoc}
     */
    public function jsResponse () {
        $idMessage = $_POST['id'];
        $textMessage = '';
        $query_update = \Drupal::database()->update('messenger_msg');
        $query_update->fields([
            'status' => '1'
        ]);
        $query_update->condition('message_id', $idMessage);
        $query_update->execute();

        $query = \Drupal::database()->select('messenger_msg', 'mm');
        $query->fields('mm', ['message', 'message_id']);
        $result = $query->execute();
        while ($row = $result->fetchAssoc()) {
            if ($row['message_id'] == $idMessage) {
                $textMessage = $row['message'];
            }
        }

        $output = '';
        $i = 0;
        $z = 0;
        $q = [];
        $uid = \Drupal::currentUser()->id();
        $query = \Drupal::database()->select('messenger_msg', 'mm');
        $query->fields('mm', ['message', 'time', 'sender_id', 'receiver_id', 'status']);
        $result = $query->execute();
        while ($row = $result->fetchAssoc()) {
            if (($row['sender_id'] == $uid) || ($row['receiver_id'] == $uid) ) {
                $z++;
                if ((array_search(($row['sender_id']), $q) === false) && ($row['sender_id'] !== $uid)) {
                    $i++;
                    $q[] = $row['sender_id'];
                }
                if ((array_search(($row['receiver_id']), $q) === false) && ($row['receiver_id'] !== $uid)) {
                    $i++;
                    $q[] = $row['receiver_id'];
                }
                $output .= $row['sender_id'] . '^';
                $output .= $row['message'] . '^';
                $output .= $row['time'] . '^';
                $output .= $row['receiver_id'] . '^';
                $output .= $row['status'] . '^';
            }
        }
        $output2 = rtrim($output, "^");
        $name = explode('^', $output2);

        $d = '';
        $v = [];
        $unread = 0;
        for ($e = 0; $e < $i; $e++) {
            $d = $q[$e];
            for($y = 0; $y < $z; $y++) {
                if (($name[$y*5] == $d) || ($name[($y*5)+3] == $d)) {
                    if ($name[($y*5)+2] > $v[($e*2)+1]) {
                        $v[$e*2] = $name[($y*5)+1];
                        $v[($e*2)+1] = $name[($y*5)+2];
                    }
                    if (($name[($y*5)] == $d) && ($name[($y*5)+4] == '0')) {
                        $unread++;
                    }
                }
            }
        }
        $response = new AjaxResponse([$idMessage, $textMessage, $unread]);
        return $response;
    }
}