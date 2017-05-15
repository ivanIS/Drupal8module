<?php

namespace Drupal\messenger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * An example controller.
 */
class AjaxUserController extends ControllerBase {

    /*
     * {@inheritdoc}
     */
    public function userResponse () {

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

        $output = '';
        $i = 0;
        $uid = \Drupal::currentUser()->id();
        $query = \Drupal::database()->select('messenger_msg', 'mm');
        $query->fields('mm', ['message', 'time', 'sender_id', 'receiver_id', 'status', 'message_id']);
        $result = $query->execute();
        while ($row = $result->fetchAssoc()) {
            if (($row['sender_id'] == $uid) || ($row['receiver_id'] == $uid) ) {
                if (($row['sender_id'] == $other_uid) || ($row['receiver_id'] == $other_uid) ) {
                    $i++;
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
        $test = '<div id="one-user"><table class="tab"><tr>
    <th>From</th>
    <th>Message</th> 
    <th>Date</th>
    <th>To</th>
  </tr>';
        $key = 5;
        for ($j = $i-1; $j>=0; $j--) {
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

        $response = new AjaxResponse();
        $response->addCommand(new ReplaceCommand
            (
                '#one-user', $test
            )
        );

        return $response;

    }
}