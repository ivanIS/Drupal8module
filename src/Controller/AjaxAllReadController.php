<?php

namespace Drupal\messenger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * An example controller.
 */
class AjaxAllReadController extends ControllerBase {

    /*
     * {@inheritdoc}
     */
    public function allReadResponse () {

        $path = $current_path = \Drupal::service('path.current')->getPath();
        $path_trim = ltrim($path, "/");
        $path_explode = explode('/', $path_trim);
        $path_end = $path_explode[3];

        $other_uid = $path_end;
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
        $name6 = array_combine($nam, $nam3);
        
        $z = 0;
        $output = '';
        $f = 0;
        $uid = \Drupal::currentUser()->id();
        $query = \Drupal::database()->select('messenger_msg', 'mm');
        $query->fields('mm', ['message', 'time', 'sender_id', 'receiver_id', 'status', 'message_id']);
        $result = $query->execute();
        while ($row = $result->fetchAssoc()) {
            if (($row['sender_id'] == $uid) || ($row['receiver_id'] == $uid) ) {
                $z++;
                if (($row['sender_id'] == $other_uid) || ($row['receiver_id'] == $other_uid) ) {
                    $f++;
                    $output .= $row['sender_id'] . '^';
                    $output .= $row['message'] . '^';
                    $output .= $row['time'] . '^';
                    $output .= $row['receiver_id'] . '^';
                    $output .= $row['status'] . '^';
                    $output .= $row['message_id'] . '^';
                }
            }
        }
        $output2 = rtrim($output, "^");
        $name = explode('^', $output2);
        
        $current_uid = \Drupal::currentUser()->id();

        for ($e = 0; $e < $f; $e++) {
            if (($name[($e * 6) +3] == $current_uid ) && ($name[($e * 6) +4] == '0')) {
                $query_update = \Drupal::database()->update('messenger_msg');
                $query_update->fields([
                    'status' => '1'
                ]);
                $query_update->condition('message_id', $name[($e * 6) + 5]);
                $query_update->execute();
                $name[($e * 6) +4] = '1';
            }
        }

        $test = '<div id="one-user"><table class="tab"><tr>
    <th>From</th>
    <th>Message</th> 
    <th>Date</th>
    <th>To</th>
  </tr>';

        $key = 5;
        for ($j = $f-1; $j>=0; $j--) {
            if ($current_uid == $name6[$name5[$name[($j*6)+3]]]) {
                $key = 1;
            }
            else $key = 0;

            $sender = \Drupal\user\Entity\User::load($name6[$name5[$name[$j*6]]]);
            if (!$sender->user_picture->isEmpty()) {
                $picture = $sender->get('user_picture')->entity->url();
                $keywords = preg_split("/sites/", $picture);
                $src = '/sites' . $keywords[1];
            } else {
                $src='';
            }           

            $reciever = \Drupal\user\Entity\User::load($name6[$name5[$name[($j*6)+3]]]);
            if (!$reciever->user_picture->isEmpty()) {
                $pictureR=$reciever->get('user_picture')->entity->url();
                $keywordsR = preg_split("/sites/", $pictureR);
                $srcR = '/sites'.$keywordsR[1];
            } else {
                $srcR='';
            }
            $test .=
                '<tr><td><img src="' . $src . '" alt="">'
                . $name5[$name[$j*6]] . '</td><td  class="s' . $name[($j*6)+4] . ' d' . $key . '"  id="' . $name[($j*6)+5] . '">'
                . ' ' . $name[($j*6)+1]
                . '</td><td> ' . $name[($j*6)+2]
                . '</td><td><img src="' . $srcR . '" alt=""> ' . $name5[$name[($j*6)+3]]
                . '</td></tr>'
            ;
        }
        $test .= '</table></div>';

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
                    $q[] = $row['uid2'];
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

        $response = new AjaxResponse();
        $response->addCommand(new ReplaceCommand
            (
                '#one-user', $test
            )
        );
        $response->addCommand(new ReplaceCommand
            (
                '#countMessage', "<div id='countMessage'><p>" . $unread ."</p></div>"
            )
        );
        return $response;
    }
}