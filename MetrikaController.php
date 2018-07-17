<?php
namespace console\controllers;

use common\components\YaMetrica;
use common\models\City;
use common\models\Event;
use common\models\ForStudent;
use common\models\Life;
use common\models\News;
use common\models\Place;
use common\models\Profession;
use common\models\StudentLife;
use yii\console\Controller;
use Yii;
use yii\db\Expression;

/**
 * Class MetrikaController
 * @package console\controllers
 */
class MetrikaController extends Controller
{
    /**
     * @var \common\components\YaMetrica
     */
    private $_service;
    private $_counter;

    public function init()
    {
        $this->_service = new YaMetrica();
        $this->_counter = 0;
    }

    /**
     * @param bool $showDebug
     * @return int
     */
    public function actionUpdateDayCounters($showDebug = false)
    {
        $this->actionUpdateNewsCounters(YaMetrica::TODAY, YaMetrica::TODAY, true, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::TODAY, YaMetrica::TODAY, YaMetrica::CITY_URL, City::class, true, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::TODAY, YaMetrica::TODAY, YaMetrica::PLACES_URL, Place::class, true, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::TODAY, YaMetrica::TODAY, YaMetrica::AFISHA_URL, Event::class, true, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::TODAY, YaMetrica::TODAY, YaMetrica::LIFE_URL, Life::class, true, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::TODAY, YaMetrica::TODAY, YaMetrica::PROFESSION_URL, Profession::class, true, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::TODAY, YaMetrica::TODAY, YaMetrica::UNIVERSITIES_URL, StudentLife::class, true, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::TODAY, YaMetrica::TODAY, YaMetrica::UNIVERSITIES_URL, ForStudent::class, true, $showDebug);

        return self::EXIT_CODE_NORMAL;
    }

    /**
     * @param bool $showDebug
     * @return int
     */
    public function actionUpdateCounters($showDebug = false)
    {
        $this->actionUpdateNewsCounters(YaMetrica::YESTERDAY, YaMetrica::YESTERDAY, false, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::YESTERDAY, YaMetrica::YESTERDAY, YaMetrica::CITY_URL, City::class, false, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::YESTERDAY, YaMetrica::YESTERDAY, YaMetrica::PLACES_URL, Place::class, false, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::YESTERDAY, YaMetrica::YESTERDAY, YaMetrica::AFISHA_URL, Event::class, false, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::YESTERDAY, YaMetrica::YESTERDAY, YaMetrica::LIFE_URL, Life::class, false, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::YESTERDAY, YaMetrica::YESTERDAY, YaMetrica::PROFESSION_URL, Profession::class, false, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::YESTERDAY, YaMetrica::YESTERDAY, YaMetrica::UNIVERSITIES_URL, StudentLife::class, false, $showDebug);
        $this->actionUpdateRubricCounters(YaMetrica::YESTERDAY, YaMetrica::YESTERDAY, YaMetrica::UNIVERSITIES_URL, ForStudent::class, false, $showDebug);

        return self::EXIT_CODE_NORMAL;
    }

    /**
     * @param bool $showDebug
     */
    public function actionTotalUpdate($showDebug = false)
    {
        ini_set('memory_limit', '256M');
        $this->actionNewsTotalUpdate($showDebug);
        $this->actionRubricTotalUpdate(YaMetrica::CITY_URL, City::class, $showDebug);
        $this->actionRubricTotalUpdate(YaMetrica::PLACES_URL, Place::class, $showDebug);
        $this->actionRubricTotalUpdate(YaMetrica::LIFE_URL, Life::class, $showDebug);
        $this->actionRubricTotalUpdate(YaMetrica::PLACES_URL, Place::class, $showDebug);
        $this->actionRubricTotalUpdate(YaMetrica::PROFESSION_URL, Profession::class, $showDebug);
        $this->actionRubricTotalUpdate(YaMetrica::UNIVERSITIES_URL, StudentLife::class, $showDebug);
        $this->actionRubricTotalUpdate(YaMetrica::UNIVERSITIES_URL, ForStudent::class, $showDebug);


        return self::EXIT_CODE_NORMAL;
    }


    /**
     * @param string $rubricUrl
     * @param \yii\db\ActiveRecord $tableClass
     * @param $startDate
     * @param $endDate
     * @param bool $showDebug
     * @param bool $pageviewsDay
     */
    public function actionUpdateRubricCounters($startDate, $endDate, $rubricUrl, $tableClass, $pageviewsDay = false, $showDebug = false)
    {
        if ($pageviewsDay) {
            echo "update $rubricUrl pageviews_day ...";
        } else {
            echo "update $rubricUrl pageviews ...";
        }
        $time = microtime(true);
        $counters =  $this->_service->data($rubricUrl, $startDate, $endDate);
        
        foreach ($counters as $counter) {
            $this->_counter++;
            if ($showDebug) {
                foreach ($counter as $key => $value) {
                    echo $this->_counter . ' '  . $key . ' ' . $value . "\n";
                }
            }
            if ($pageviewsDay) {
                $tableClass::updateAll(
                    ['pageviews_day' => $counter['views']],
                    [
                        'slug' =>(string)$counter['slug'],
                    ]
                );
            } else {
                $tableClass::updateAll(
                    [
                        'pageviews' => new Expression('pageviews + ' . $counter['views']),
                        'pageviews_day' => 0
                    ],
                    [
                        'slug' =>(string)$counter['slug'],
                    ]
                );
            }
        }



        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param bool $pageviewsDay
     * @param bool $showDebug
     */
    public function actionUpdateNewsCounters($startDate, $endDate, $pageviewsDay = false, $showDebug = false)
    {
        if ($pageviewsDay) {
            echo 'update news pageviews_day ...';
        } else {
            echo 'update news pageviews ...';
        }

        $time = microtime(true);
        $counters =  $this->_service->news($startDate, $endDate);

        foreach ($counters as $counter) {
            $this->_counter++;
            if ($showDebug) {
                foreach ($counter as $key => $value) {
                    echo $this->_counter . ' '  . $key . ' ' . $value . "\n";
                }
            }

            if ($pageviewsDay) {
                News::updateAll(
                    ['pageviews_day' => $counter['views']],
                    [
                        'slug' =>(string)$counter['slug'],
                        'id' => (int)$counter['id'],
                    ]
                );
            } else {
                News::updateAll(
                    [
                        'pageviews' => new Expression('pageviews + ' . $counter['views']),
                        'pageviews_day' => 0
                    ],
                    [
                        'slug' =>(string)$counter['slug'],
                        'id' => (int)$counter['id'],
                    ]
                );
            }
        }

        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }



    /**
     * @param bool $showDebug
     */
    public function actionNewsTotalUpdate($showDebug = false)
    {
        echo 'total update of all news for all time...';
        $time = microtime(true);
        $counters =  $this->_service->news(YaMetrica::LONG_AGO, YaMetrica::TODAY);

        foreach ($counters as $counter) {
            $this->_counter++;
            if ($showDebug) {
                foreach ($counter as $key => $value) {
                    echo $this->_counter . ' '  . $key . ' ' . $value . "\n";
                }
            }

                News::updateAll(
                    [
                        'pageviews' =>  $counter['views'],
                        'pageviews_day' => 0
                    ],
                    [
                        'slug' =>(string)$counter['slug'],
                        'id' => (int)$counter['id'],
                    ]
                );
            }
        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";

    }

    /**
     * @param $rubricUrl
     * @param $tableClass
     * @param bool $showDebug
     */
    public function actionRubricTotalUpdate($rubricUrl, $tableClass, $showDebug = false)
    {
        echo "total update $rubricUrl pageviews for all time...";
        $time = microtime(true);
        $counters =  $this->_service->data($rubricUrl, YaMetrica::LONG_AGO, YaMetrica::TODAY);

        foreach ($counters as $counter) {
            $this->_counter++;
            if ($showDebug) {
                foreach ($counter as $key => $value) {
                    echo $this->_counter . ' '  . $key . ' ' . $value . "\n";
                }
            }

            $tableClass::updateAll(
                [
                    'pageviews' =>  $counter['views'],
                    'pageviews_day' => 0
                ],
                [
                    'slug' =>(string)$counter['slug'],
                ]
            );
        }
        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";

    }

}
