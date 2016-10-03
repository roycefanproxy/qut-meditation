<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use kartik\grid\GridView;
use yii\db\Query;

/* @var $this yii\web\View */
/* @var $model common\models\Student */
/* @var $form ActiveForm */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'All Enrollments';
$this->params['breadcrumbs'][] = 'Enrollments';
$this->params['fluid'] = true;
?>

<div class="col-md-2">
    <br>
    <br>
    <div class="list-group">
        <?= Html::a('<i class="glyphicon glyphicon-menu-right"></i>All Enrollments', ['user/all-enrollments'], ['class' => 'list-group-item active']) ?>
        <a href="#" class="list-group-item">
            <i class="glyphicon glyphicon-menu-right"></i>Ongoing Course
        </a>
        <?= Html::a('<i class="glyphicon glyphicon-menu-right"></i>Upcoming Courses', ['user/upcoming-enrollments'], ['class' => 'list-group-item']) ?>
        <?= Html::a('<i class="glyphicon glyphicon-menu-right"></i>Finished Courses', ['user/enrollment-history'], ['class' => 'list-group-item']) ?>
    </div>
</div>

<div class="col-md-5">
    <div class="container">
        <div class="user-allEnrollment">

            <br>
            <br>
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?= Alert::widget() ?>

            <div class="col-lg-10" id="body">
                <h1><?= Html::encode($this->title) ?></h1>
                <br>
                <br>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'showPageSummary' => false,
                    'striped' => true,
                    'hover' => true,
                    'export' => false,
                    'panel'=>[
                        'heading'=>'<i class="glyphicon glyphicon-calendar"></i> All Courses',
                        'type'=>'primary'
                    ],
                    'columns' => [
                        ['class' => 'kartik\grid\SerialColumn'],
                        [
                            'attribute' => 'courseID',
                            'vAlign' => 'middle',
                            'width' => '120px',
                        ],
                        [
                            'attribute' => 'course_start',
                            'width' => '250px',
                            'vAlign' => 'middle',
                            'hAlign' => 'right',
                            'value' => function($model, $key, $index, $widget) {
                                return $model['start'];
                            }
                        ],
                        [
                            'attribute' => 'duration_(days)',
                            'width' => '120px',
                            'hAlign' => 'center',
                            'vAlign' => 'middle',
                            'value' => function ($model, $key, $index, $widget) {
                                return $model['duration'];
                            }
                        ],
                        [
                            'attribute' => 'course_end',
                            'width' => '250px',
                            'vAlign' => 'middle',
                            'hAlign' => 'right',
                            'value' => function($model, $key, $index, $widget) {
                                return $model['end'];
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{enroll}',
                            'options' => ['style' => 'width:20%'],
                            'buttons' => [
                                'enroll' => function ($url, $model) {
                                    $result = (new Query())->select(['cls.courseID', 'c.start', 'c.duration', 'c.end'])->from('classtable cls')
                                        ->where(['studentID' => Yii::$app->user->identity->id, 'cls.courseID' => $model['courseID']])
                                        ->innerJoin('course c', 'cls.courseID=c.courseID AND CURDATE()>=DATE(c.end)')->all();
                                    return !empty($result) ? Html::a(
                                        'Create Report',
                                        ['/user/new-report', 'courseID' => $model['courseID']],
                                        ['class' => 'list-group-item list-group-item-success']
                                    ) : '<a href="#" class="list-group-item disabled">Create Report</a>';
                                }
                            ],
                        ]
                    ]
                ]) ?>
            </div>
        </div><!-- user-enrollment -->
    </div>
</div>