<?php
/**
 * @file
 * Contains \Drupal\messenger\Plugin\Block\MessengerBlock.
 */

namespace Drupal\messenger\Plugin\Block;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Messenger' Block
 *
 * @Block(
 *   id = "messenger_block",
 *   admin_label = @Translation("Messenger block"),
 * )
 */
class MessengerBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {

        $output = '';
        $i = 0;
        $z = 0;
        $senderIdArray = [];
        $uid = \Drupal::currentUser()->id();
        $query = \Drupal::database()->select('messenger_msg', 'mm');
        $query->fields('mm', ['message', 'time', 'sender_id', 'receiver_id', 'status']);
        $result = $query->execute();
        while ($row = $result->fetchAssoc()) {
            if (($row['sender_id'] == $uid) || ($row['receiver_id'] == $uid) ) {
                $z++;
                if ((array_search(($row['sender_id']), $senderIdArray) === false) && ($row['sender_id'] !== $uid)) {
                    $i++;
                    $senderIdArray[] = $row['sender_id'];
                }
                if ((array_search(($row['receiver_id']), $senderIdArray) === false) && ($row['receiver_id'] !== $uid)) {
                    $i++;
                    $senderIdArray[] = $row['receiver_id'];
                }
                $output .= $row['sender_id'] . '^';
                $output .= $row['message'] . '^';
                $output .= $row['time'] . '^';
                $output .= $row['receiver_id'] . '^';
                $output .= $row['status'] . '^';
            }
        }
        $trueOutput = rtrim($output, "^");
        $name = explode('^', $trueOutput);

        $d = '';
        $v = [];
        $unread = 0;
        for ($e = 0; $e < $i; $e++) {
            $d = $senderIdArray[$e];
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

        if ($unread == 0) {
            $property = 'none';
        } else {
            $property = 'block';
        }

        $str = '<p>You have unread messages</p>'.
            '<div id="mesConteiner">'.
            '<a href="/user/'.$uid.'/messages"></a>'.
            '<div id="countMessage" style="display:'.$property.';">'.
            '<p>'.$unread.'</p>'.
            ' </div>'
        ;

        return array(
            '#type' => 'markup',
            '#cache' => [
                'max-age' => 0],
            '#attached' => array(
                'library' => array(
                    'messenger/messenger.base'
                ),
            ),
            '#markup' => $str,
        );
    }
}
