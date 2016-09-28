<?php
namespace frontend\controllers;

use Yii;
use yii\db\Query;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use common\models\Course;
use common\models\Student;
use common\models\Volunteer;


/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays donation page.
     *
     * @return maxed
     */
    public function actionDonation()
    {
        return $this->render('donation');
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Display user page.
     *
     * @return mixed
     */
    public function actionSetting()
    {
        return $this->render('user/view');
    }

    public function actionCourse()
    {

        $dataProvider = new ActiveDataProvider([
            'query' => Course::find()->where('DATE(start) > \''.date('Y-m-d').'\'')->orderBy('start'),
            'pagination' => ['pageSize' => 10]
        ]);

        return $this->render('course', ['dataProvider' => $dataProvider]);
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    public function actionEnroll($id, $startDate, $endDate) {

        if (Yii::$app->user->isGuest) {
            return $this->actionLogin();
        }

        $subQuery = (new Query())->select(['cls.studentID', 'cls.courseID', 'c.start', 'c.end'])->from('classtable cls')->where(['studentID' => Yii::$app->user->identity->id])->innerJoin('course c', 'cls.courseID=c.courseID');
        $studentTable = (new Query())->from(['t' => $subQuery])->where('\''.$startDate.'\' <= DATE(t.end) AND \''.$endDate.'\' >= DATE(t.start)')->one();
        if (empty($studentTable)) {
            $student = new Student();
            $student->studentID = Yii::$app->user->identity->id;
            $student->courseID = $id;

            if ($student->save()) {
                Yii::$app->session->setFlash('success', 'You have successfully enrolled the class starting on '.$startDate);
            }
            else {
                Yii::$app->session->setFlash('warning', 'Sorry, Error caused.\nEnrollment failed.');
            }
        } else {
            if ($startDate == $studentTable['start']){
                Yii::$app->session->setFlash('danger', 'Sorry, you have already enrolled the class');
            } else {
                Yii::$app->session->setFlash('danger', 'Sorry, you have already enrolled a class which will start on ' . $studentTable['start'] . ' that conflicts with the class you are intending to enroll.');
            }
        }
        return $this->actionCourse();
    }

    public function actionRoster()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Course::find()->where('DATE(start) > \''.date('Y-m-d').'\'')->orderBy('start'),
            'pagination' => ['pageSize' => 10]
        ]);

        return $this->render('roster', ['dataProvider' => $dataProvider]);

    }

    public function actionVolunteer($id, $startDate, $endDate) {

        $subQuery = (new Query())->select(['v.studentID', 'v.courseID', 'c.start', 'c.end'])->from('volunteer v')->where(['studentID' => Yii::$app->user->identity->id])->innerJoin('course c', 'v.courseID=c.courseID');
        $volunteerTable = (new Query())->from(['t' => $subQuery])->where('\''.$startDate.'\' <= DATE(t.end) AND \''.$endDate.'\' >= DATE(t.start)')->one();
        if (empty($volunteerTable)) {
            $volunteer = new Volunteer();
            $volunteer->studentID = Yii::$app->user->identity->id;
            $volunteer->courseID = $id;

            if ($volunteer->save()) {
                Yii::$app->session->setFlash('success', 'You have successfully volunteered the class starting on '.$startDate);
            }
            else {
                Yii::$app->session->setFlash('warning', 'Sorry, Error caused.\nVolunteer request failed.');
            }
        } else {
            if ($startDate == $volunteerTable['start']){
                Yii::$app->session->setFlash('danger', 'Sorry, you have already volunteered the class');
            } else {
                Yii::$app->session->setFlash('danger', 'Sorry, you have already volunteered a class which will start on ' . $volunteerTable['start'] . ' that conflicts with the class you are intending to volunteered.');
            }
        }
        return $this->actionRoster();
    }

}
