<?php

namespace common\components;

use Yii;
use yii\base\Object;

/**
 * Class YaMetrica
 * @package common\components
 * @property @newsUrl
 */
class YaMetrica extends Object
{


    const METRICA_SCOPE = 'https://api-metrika.yandex.ru/stat/v1/data';
    const YA_COUNTER = 28715071;
    const OAUTH_TOKEN = 'AQAAAAACSqngAAUUCAu6DdKpkE3ZtHQpi1hjBGI';
    const METRICS = 'ym:pv:pageviews, ym:pv:users';
    const DIMENSIONS = 'ym:pv:date,ym:pv:URLPath,ym:pv:URLPathLevel3';
    const TODAY = 'today';
    const YESTERDAY = 'yesterday';
    const LONG_AGO = '2018-01-01';

    const END_DATE = 'today';
    const SORT = 'ym:pv:date';
    /*
     * metrika api позволяет поставить значение limit не более ста тысяч
     * отвечает за макс. число элементов в ответе
     */
    const MAX_LIMIT = 100000;

    const NOVOSTI_URL = '/novosti/';
    const CITY_URL = '/gorod/';
    const LIFE_URL = '/zhizn/';
    const PLACES_URL = '/mesta/';
    const AFISHA_URL = '/afisha/';
    const PROFESSION_URL = '/vuzy/professiya/';
    const UNIVERSITIES_URL = '/vuzy/';
    const EMPTY_URL = '';


    /**
     * @param string $rubricUrl
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function data($rubricUrl, $startDate, $endDate)
    {
        $params = [
            'id' => self::YA_COUNTER,
            'oauth_token' => self::OAUTH_TOKEN,
            'metrics'     => self::METRICS,
            'dimensions'  => self::DIMENSIONS,
            'filters' => "ym:pv:URLPath=@'" . $rubricUrl . "'",
            'date1'       => $startDate,
            'date2'       => $endDate,
            'sort'        => self::SORT,
            'limit' => self::MAX_LIMIT,
            'pretty' => 'true',
        ];
        $jsonArr = json_decode(file_get_contents(self::METRICA_SCOPE . '?' . http_build_query($params)), true);
        $result =[];
        foreach ($jsonArr['data'] as $node) {
            $slug = str_replace($rubricUrl, '', $node['dimensions'][1]['name']);
            while ($dashPos = mb_strrpos($slug, '/')) {
                $slug = str_replace(substr($slug, 0, $dashPos+1), '', $slug);
            }

            $result[$slug]=[
                'slug' => $slug,
                'views' => ($result[$slug]['views'] ?? 0) + $node['metrics'][0],
                'unique_views' => ($result[$slug]['unique_views'] ?? 0) + $node['metrics'][1]
            ];
        }

        return $result;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function news($startDate, $endDate)
    {
        $params = [
            'id' => self::YA_COUNTER,
            'oauth_token' => self::OAUTH_TOKEN,
            'metrics'     => self::METRICS,
            'dimensions'  => self::DIMENSIONS,
            'filters' => "ym:pv:URLPath=@'" . self::NOVOSTI_URL . "'",
            'date1'       => $startDate,
            'date2'       => $endDate,
            'sort'        => self::SORT,
            'limit' => self::MAX_LIMIT,
            'pretty' => 'true',
        ];
        $jsonArr = json_decode(file_get_contents(self::METRICA_SCOPE . '?' . http_build_query($params)), true);
        $result =[];
        foreach ($jsonArr['data'] as $node) {
            $slug = str_replace(self::NOVOSTI_URL, '', $node['dimensions'][1]['name']);
            $id = mb_substr($slug, mb_strrpos($slug, '-id')+3);
            $slug = mb_substr($slug, 0, mb_strrpos($slug, '-id'));

            $result[$slug]=[
                'id' => $id,
                'slug' => $slug,
                'views' => ($result[$slug]['views'] ?? 0) + $node['metrics'][0],
                'unique_views' => ($result[$slug]['unique_views'] ?? 0) + $node['metrics'][1]
            ];
        }

        return $result;
    }




}
