<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 10/19/2016
 * Time: 6:08 PM
 */

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\db\Query;

class MailSenderController extends Controller
{
    public function actionIndex() {
        echo "cron service runnning";
    }

    public function actionSendMailTenDaysPrior() {
        $subQuery = (new Query())->select(['cls.studentID', 'cls.courseID', 'c.start', 'c.duration'])->from('classtable cls')
            ->innerJoin('course c', 'c.courseID=cls.courseID')
            ->where('DATE_ADD(CURDATE(), INTERVAL 10 DAY)<c.start');
        $query = (new Query())->select(['u.firstName', 'u.lastName', 'u.email', 'upcomingCourses.*'])->from(['upcomingCourses' => $subQuery])
            ->innerJoin('user u', 'u.id=upcomingCourses.studentID');
        $recipients = $query->all();

        $total = 0;
        foreach ($recipients as $index => $recipient) {
            $content = "Hi " . $recipient["firstName"];
            $content .= "\n\n";
            $content .= "  We would just like to tell you that you have participated a " . $recipient["duration"] .
                " days meditation course on " . $recipient["start"] . ".\n" .
                "To confirm your attendance, please contact our manager. Mr. xxxxx with contact number blablabla\n".
                "See you on that day!\n" .

                $content .= "\n\n";
            $content .= "Sincerely," .
                "Om meditation team" .
                "\n\n\n\n\n\n\n";

            $content .= "(This email is automatically generated by system. Please do not reply." .
                "If you have any question, please contact the staff.\n".
                " Thank you.";


            $mail = YII::$app->mailer->compose()
                ->setFrom('reminder@omedi.org.au')
                ->setTo($recipient['email'])
                ->setSubject('Reminder of Upcoming Meditation Course')
                ->setTextBody($content);

            $name = $recipient['firstName'] . ' ' . $recipient['lastName'];
            if ($mail->send()) {
                echo 'Email to '.$recipient['email'].', owned by '.$name.' (student ID : '.$recipient['studentID'].') has been successfully sent.'.PHP_EOL;
                $total++;
            } else {
                echo 'Email to '.$recipient['email'].', owned by '.$name.' (student ID : '.$recipient['studentID'].') is failed to send. Please check the system.'.PHP_EOL;
            }
        }
        echo 'Sending email job done. Sent total of '.$total.' mail(s).'.PHP_EOL;
	    echo PHP_EOL;
    }
}
