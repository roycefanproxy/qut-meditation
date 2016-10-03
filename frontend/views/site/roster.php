<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\Volunteer */
/* @var $form ActiveForm */
/* @var $dataprovider Courses */

$this->title = 'Course List';
$this->params['breadcrumbs'][] = ['label' => 'Roster', 'url' => ['roster']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-roster" id="body">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="col-lg-10">

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'showPageSummary' => false,
            'striped' => true,
            'hover' => true,
            'export' => false,
            'panel'=>[
                'heading'=>'<i class="glyphicon glyphicon-calender"></i> Courses Available',
                'type'=>'primary'
            ],
            'columns' => [
                ['class' => 'kartik\grid\SerialColumn'],
                [
                    'attribute' => 'courseID',
                    'width' => '120px',
                ],
                [
                    'attribute' => 'course_start',
                    'width' => '250px',
                    'hAlign' => 'right',
                    'value' => function($model, $key, $index, $widget) {
                        return $model->start;
                    }
                ],
                [
                    'attribute' => 'duration_(days)',
                    'width' => '120px',
                    'hAlign' => 'right',
                    'value' => function ($model, $key, $index, $widget) {
                        return $model->duration;
                    }
                ],
                [
                    'attribute' => 'course_end',
                    'width' => '250px',
                    'hAlign' => 'right',
                    'value' => function($model, $key, $index, $widget) {
                        return $model->end;
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{enroll}',
                    'options' => ['style' => 'width:20%'],
                    'buttons' => [
                        'enroll' => function ($url, $model) {
                            $result = common\models\Volunteer::find()->where(['courseID' => $model->courseID, 'studentID' => Yii::$app->user->identity->id])->all();
                            return empty($result) ? Html::a(
                                'Volunteer Now',
                                ['/site/volunteer', 'id' => $model->courseID, 'startDate' => $model->start, 'endDate' => $model->end],
                                ['class' => 'list-group-item list-group-item-info']
                            ) : '<a href="#" class="list-group-item disabled">Volunteered</a>';
                        }
                    ],
                ]
            ]
        ]); ?>
    </div>

</div><!-- site-roster -->
