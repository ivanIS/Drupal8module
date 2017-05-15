<?php

namespace Drupal\messenger\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Messenger module.
 */
class MessengerController extends ControllerBase {

    /**
     * Returns a simple page.
     *
     * @return array
     *   A simple renderable array.
     */
    public function allUser() {

        $out = '';
        $out1 = '';
        $query = \Drupal::database()->select('users_field_data', 'ufd');
        $query->fields('ufd', ['name', 'uid']);
        $result = $query->execute();
        while ($row = $result->fetchAssoc()) {
            if ($row['name'] !== '') {
                $out .=
                $row['name'] .= '^';
                $out1 .=
                $row['uid'] .= '^';
            }
        }
        $out2 = rtrim($out, "^");
        $nam = explode('^', $out2);
        $out3 = rtrim($out1, "^");
        $nam3 = explode('^', $out3);
        $name5 = array_combine($nam3, $nam);

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
        $current_uid = \Drupal::currentUser()->id();

        $d = '';
        $v = [];
        $k = [];
        for ($e = 0; $e < $i; $e++) {

            $d = $q[$e];

            for($y = 0; $y < $z; $y++) {
                if (($name[$y*5] == $d) || ($name[($y*5)+3] == $d)) {
                    if ($name[($y*5)+2] > $v[($e*2)+1]) {
                        $v[$e*2] = $name[($y*5)+1];
                        $v[($e*2)+1] = $name[($y*5)+2];
                    }
                    if (($name[($y*5)] == $d) && ($name[($y*5)+4] == '0')) {
                        $k[$d][$y] = $name[($y*5)+4];
                    }
                }
            }
        }
        $test = '<script></script><div id="all" ><table class="tab1"><tr>
    <th>From</th>
    <th>Message</th> 
    <th>Time</th>
    <th>Unread</th>
  </tr>';
        for ($j = 0; $j<$i; $j++) {

            $sender = \Drupal\user\Entity\User::load($q[$j]);
            if (!$sender->user_picture->isEmpty()) {
                $picture = $sender->get('user_picture')->entity->url();
                $keywords = preg_split("/sites/", $picture);
                $src = '/sites' . $keywords[1];
            } else {
                $src='';
            }
            
            $test .=
                '<tr class="row-h"><td><img src="' . $src . '" alt="">'
                . $name5[$q[$j]] . '</td><td id="hand" class="/user/'
                . $current_uid . '/messages/'
                . $q[$j] .'">'
                . $v[$j*2] . '</td><td> ' . $v[($j*2)+1]
                . '</td><td>'. count($k[$q[$j]]) .'</td></tr></div>'
            ;
        }
        $test .= '</table></div>';
        $element = [
            '#markup' => $test,
            '#attached' => array(
                'library' => array(
                    'messenger/messenger.base'
                ),
            )
        ];
      
        return [
            'form' => \Drupal::formBuilder()->getForm('Drupal\messenger\Form\MessengerForm'),
            $element
        ];
    }
}