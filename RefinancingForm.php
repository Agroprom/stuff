<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * Refinancing form
 * @property integer $offer;
 * @property string $profit;
 */
class RefinancingForm extends Model
{
    public $sum;
    public $periodsLeft;
    public $monthlyPayment;

    private $_profit;
    private $_bankOffer;


    const INTEREST_RATE = 0.09 / 12;
    const MAX_SUM = 99999999;
    const MAX_PERIODS = 999;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sum', 'monthlyPayment', 'periodsLeft'], 'required'],
            [['sum', 'monthlyPayment'], 'integer', 'min'=>1,'max'=> self::MAX_SUM],
            [['periodsLeft'], 'integer', 'min'=>1,'max'=> self::MAX_PERIODS],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'sum' => 'Остаток по основному долгу',
            'monthlyPayment' => 'Ежемесячный платёж',
            'periodsLeft'=>'Осталось платить месяцев',
        ];
    }


    /**
     * @return integer
     */

    public function getOffer()
    {
        if (isset($this->_bankOffer)) {
            return $this->_bankOffer;
        }

        $this->_bankOffer = (int) $this->sum * self:: INTEREST_RATE / (1 - 1 / pow((1 + self:: INTEREST_RATE), $this->periodsLeft));

        return $this->_bankOffer;
    }


    /**
     * @return string
     */
    public function getProfit()
    {
        if (isset($this->_profit)) {
            return $this->_profit;
        }

        $profit = (int)(($this->monthlyPayment - $this->_bankOffer)*$this->periodsLeft);
        $this->_profit =  Yii::t('app', '{n, plural, =1{# рубля} one{# рубля} few{# рублей} many{# рублей} other{# рублей}}!', ['n' => $profit], 'ru_RU');

        return $this->_profit;
    }


}
