<?php
return array(
    'merchant'          => array(
        'value'        => '',
        'title'        => 'Мерчант',
        'placeholder'  => '',
        'description'  => '',
        'control_type' => waHtmlControl::INPUT,
    ),

    'secret_key'      => array(
        'value'        => '',
        'title'        => 'Секретный ключ',
        'description'  => '',
        'placeholder'  => '',
        'control_type' => waHtmlControl::INPUT,

    ),
    'test_secret_key'      => array(
        'value'        => '',
        'title'        => 'Тестовый секретный ключ',
        'description'  => '',
        'placeholder'  => '',
        'control_type' => waHtmlControl::INPUT,

    ),
    'success_url'      => array(
        'value'        => $this->getRelayUrl() . '?transaction_result=success',
        'title'        => 'Адрес для перехода после успешной оплаты',
        'description'  => '',
        'placeholder'  => '',
        'control_type' => waHtmlControl::INPUT,

    ),
    'fail_url'      => array(
        'value'        => $this->getRelayUrl() . '?transaction_result=failure',
        'title'        => 'Адрес для перехода после ошибки при оплате',
        'description'  => '',
        'placeholder'  => '',
        'control_type' => waHtmlControl::INPUT,

    ),
    'cancel_url'      => array(
        'value'        => $this->getRelayUrl() . '?transaction_result=success',
        'title'        => 'Адрес для перехода в случае нажатия кнопки «Вернуться в магазин»',
        'description'  => '',
        'placeholder'  => '',
        'control_type' => waHtmlControl::INPUT,

    ),

    'mode'   => array(
        'value'         => 'test',
        'placeholder'   =>  '',
        'title'         => 'Режим',
        'description'   => '',
        'control_type'  => waHtmlControl::SELECT,
        'options'       => array(
            'test'   => 'Тестовый',
            'prod'   => 'Рабочий'
            )
        ),
    'preauth'   => array(
        'value'         => '0',
        'placeholder'   =>  '',
        'title'         => 'Предавторизация',
        'description'   => '',
        'control_type'  => waHtmlControl::SELECT,
        'options'       => array(
            '0'   => 'Нет',
            '1'   => 'Да'
            )
        ),
    'pm_checkbox'  => array(
        'value'        => false,
        'title'        => 'Отображать определённые способы оплаты',
        'description'  => 'Для отображения отдельных методов оплаты установите галочку и выберите интересующие из списка',
        'control_type' => waHtmlControl::CHECKBOX,
    ),
    'show_payment_methods'         => array(
        'value'            => array(),
        'title'            => 'Отображаемые варианты оплаты',
        'description'      => '',
        'control_type'     => waHtmlControl::GROUPBOX,
        'options' => array(
            'card'      => 'Банковской картой',
            'sbp'       => 'Система быстрых платежей',
            'googlepay' => 'GooglePay',
            'applepay'  => 'ApplePay'
        ),
    ),
    'sno'   => array(
        'value'         => 'usn',
        'placeholder'   =>  '',
        'title'         => 'Система налогооблажения',
        'description'   => '',
        'control_type'  => waHtmlControl::SELECT,
        'options'       => array(
            'osn' => 'общая СН',
            'usn_income' => 'упрощенная СН (доходы)',
            'usn_income_outcome' => 'упрощенная СН (доходы минус расходы)',
            'envd' => 'единый налог на вмененный доход',
            'esn' => 'единый сельскохозяйственный налог',
            'patent' => 'патентная СН',
            )
        ),
    'payment_method'   => array(
        'value'         => 'full_prepayment',
        'placeholder'   =>  '',
        'title'         => 'Признак способа расчёта',
        'description'   => '',
        'control_type'  => waHtmlControl::SELECT,
        'options'       => array(
            'full_prepayment' => 'полная предоплата',
            'prepayment' => 'частичная предоплата',
            'advance' => 'аванс',
            'full_payment' => 'полный расчет',
            'partial_payment' => 'частичный расчет и кредит',
            'credit' => 'кредит',
            'credit_payment' => 'выплата по кредиту',
            )
        ),
    'payment_object'   => array(
        'value'         => 'commodity',
        'placeholder'   =>  '',
        'title'         => 'Признак предмета расчёта',
        'description'   => '',
        'control_type'  => waHtmlControl::SELECT,
        'options'       => array(
            'commodity' => 'товар',
            'excise' => 'подакцизный товар',
            'job' => 'работа',
            'service' => 'услуга',
            'gambling_bet' => 'ставка в азартной игре',
            'gambling_prize' => 'выигрыш в азартной игре',
            'lottery' => 'лотерейный билет',
            'lottery_prize' => 'выигрыш в лотерею',
            'intellectual_activity' => 'результаты интеллектуальной деятельности',
            'payment' => 'платеж',
            'agent_commission' => 'агентское вознаграждение',
            'composite' => 'несколько вариантов',
            'another' => 'другое',
            )
        ),
    'payment_object_delivery'   => array(
        'value'         => 'service',
        'placeholder'   =>  '',
        'title'         => 'Признак предмета расчёта на доставку',
        'description'   => '',
        'control_type'  => waHtmlControl::SELECT,
        'options'       => array(
            'commodity' => 'товар',
            'excise' => 'подакцизный товар',
            'job' => 'работа',
            'service' => 'услуга',
            'gambling_bet' => 'ставка в азартной игре',
            'gambling_prize' => 'выигрыш в азартной игре',
            'lottery' => 'лотерейный билет',
            'lottery_prize' => 'выигрыш в лотерею',
            'intellectual_activity' => 'результаты интеллектуальной деятельности',
            'payment' => 'платеж',
            'agent_commission' => 'агентское вознаграждение',
            'composite' => 'несколько вариантов',
            'another' => 'другое',
            )
        ),
    'logging'   => array(
        'value'         => '1',
        'placeholder'   =>  '',
        'title'         => 'Включить логирование',
        'description'   => '',
        'control_type'  => waHtmlControl::SELECT,
        'options'       => array(
            '0'   => 'Нет',
            '1'   => 'Да'
            )
        ),
    'log_size_limit'   => array(
        'value'         => '10',
        'placeholder'   =>  '',
        'title'         => 'Максимальный размер логов(Mb)',
        'description'   => '<a href="'.$this->getRelayUrl() . '?transaction_result=download_modulbank_logs'.'">Скачать логи</a>',
        'control_type'  => waHtmlControl::INPUT,
        ),
);
