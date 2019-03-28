<?php
/**
 * @package waPlugins
 * @subpackage Payment
 * @name Modulbank
 * @description Modulbank payment module
 * @payment-type online
 *
 * @property-read string $login
 * @property-read string $api_login
 * @property-read string $password
 * @property-read string $sign_password
 * @property-read string $customer_phone
 *
 */
include_once __DIR__.'/modulbanklib/ModulbankHelper.php';
include_once __DIR__.'/modulbanklib/ModulbankReceipt.php';

class modulbankPayment extends waPayment implements waIPayment
{
    private $order_id;

    private static $url = 'https://pay.modulbank.ru/pay';


    public function allowedCurrency()
    {
        return array('RUB');
    }

    public function payment($payment_form_data, $order_data, $auto_submit = false)
    {
        $order = waOrder::factory($order_data);
        $allowed_currency = $this->allowedCurrency();
        if (!in_array($order->currency, $allowed_currency)) {
            throw new waException(sprintf('Unsupported currency %s', $order->currency));
        }

        $amount = number_format($order->total, 2, '.', '');



        $sysinfo = [
            'language' => 'PHP ' . phpversion(),
            'plugin'   => $this->getVersion(),
            'cms'      => $this->getCmsVersion(),
        ];
        $c = new waContact($order_data['customer_contact_id']);

        $email = $c->get('email', 'default');
        $name = $c->get('firstname', 'default').' '.$c->get('lastname', 'default');
        $form_fields = array(
            'merchant'        => $this->merchant,
            'amount'          => $amount,
            'order_id'        => $order_data['id_str'],
            'testing'         => $this->mode == 'test' ? 1 : 0,
            'description'     => 'Оплата заказа ' . $order_data['id_str'],
            'success_url'     => $this->success_url,
            'fail_url'        => $this->fail_url,
            'cancel_url'      => $this->cancel_url,
            'callback_url'    => $this->getRelayUrl().'?transaction_result=result&app_id='.$this->app_id.'&client_id='.$this->app_id.'_'.$this->merchant_id.'_'.$order_data['order_id'],
            'client_name'     => $name,
            'client_email'    => $email,
            'receipt_contact' => $email,
            'receipt_items'   => $this->getReceiptData($order),
            'unix_timestamp'  => time(),
            'sysinfo'         => json_encode($sysinfo),
            'salt'            => ModulbankHelper::getSalt(),
        );

        $key = $this->mode == 'test' ?
            $this->test_secret_key :
            $this->secret_key;

        $signature                 = ModulbankHelper::calcSignature($key, $form_fields);
        $form_fields['signature'] = $signature;

        $view = wa()->getView();
        $form_url = self::$url;
        $this->logger($order->items, 'orderItems');
        $this->logger($form_fields, 'paymentForm');
        $view->assign(compact('form_fields', 'form_url', 'auto_submit'));

        return $view->fetch($this->path.'/templates/payment.html');
    }

    private function getReceiptData(waOrder $order)
    {
        $receipt = new ModulbankReceipt($this->sno, $this->payment_method, $order->total);

            foreach ($order->items as $item) {
                $item['amount'] = $item['price'] - ifset($item['discount'], 0.0);
                $taxId = $this->getTaxId($item);
                $receipt->addItem($item['name'], $item['amount'], $taxId, $this->payment_object, $item['quantity']);
            }

            #shipping
            if (strlen($order->shipping_name) || $order->shipping) {

                $item = array(
                    'tax_rate' => $order->shipping_tax_rate,
                );
                if ($order->shipping_tax_included !== null) {
                    $item['tax_included'] = $order->shipping_tax_included;
                }
                $taxId = $this->getTaxId($item);
                $receipt->addItem($order->shipping_name, $order->shipping, $item['amount'], $order->shipping_tax_rate, $this->payment_object_delivery);
            }
            return $receipt->getJson();

    }

    private function getVersion() {
        return $this->properties['version'];
    }

    private function getCmsVersion() {
        $prop = wa()->getAppInfo('shop');
        return $prop['version'];
    }

    public function refund($transaction_raw_data)
    {
        $result = ['result' => 1, 'description' => 'Ошибка выполнения запроса возврата'];
        $key = $this->mode == 'test' ?
            $this->test_secret_key :
            $this->secret_key;
        $merchant = $this->merchant;
        $amount = number_format($transaction_raw_data['transaction']['amount'], 2, '','.');
        $transaction_id = $transaction_raw_data['transaction']['native_id'];
        $this->logger(['merchant' => $merchant, 'amount' => $amount, 'transaction' => $transaction_id], 'refund');
        $response = ModulbankHelper::refund($merchant, $amount, $transaction_id, $key);
        $this->logger($response, 'refundResponse');
        $response = json_decode($response);
        if ($response && $response->status === 'ok') {
            if (in_array($response->refund->state, array('PENDING', 'PROCESSING', 'WAITING_FOR_RESULT', 'COMPLETE'))){
                $result = ['result' => 0, 'description' => $response->message ];
            } else {
                $result = ['result' => 1, 'description' => $response->message ];
            }
        }
        if ($response && $response->status === 'error') {
            $result = ['result' => 1, 'description' => $response->message ];
        }
        return $result;
    }


    protected function callbackInit($request)
    {
        if(wa()->getUser()->isAdmin() && $request['transaction_result'] === 'download_modulbank_logs') {
            ModulbankHelper::sendPackedLogs($this->getLogsPath());
        }
        $pattern = "@^([a-z]+)_(\\d+)_(.+)$@";
        if (!empty($request['client_id']) && preg_match($pattern, $request['client_id'], $match)) {
            $this->app_id = $match[1];
            $this->merchant_id = $match[2];
            $this->order_id = $match[3];
        }
        return parent::callbackInit($request);
    }

    /**
     *
     * @param $data - get from gateway
     * @return array
     */
    protected function callbackHandler($request)
    {
        $this->logger($request, 'callback');
        $transaction_data = $this->formalizeData($request);
        $transaction_result = ifempty($request['transaction_result'], 'success');
        $url = null;
        $app_payment_method = null;
        switch ($transaction_result) {
            case 'result':
                if($this->checkSign()){
                    if($request['state'] === 'COMPLETE'){
                        $app_payment_method = self::CALLBACK_PAYMENT;
                        $transaction_data['state'] = self::STATE_CAPTURED;
                        $transaction_data['type'] = self::OPERATION_AUTH_CAPTURE;
                    }
                } else {
                    throw new waException('sign error');
                }
                break;
            case 'success':
                $url = $this->getAdapter()->getBackUrl(waAppPayment::URL_SUCCESS, $transaction_data);
                break;
            case 'failure':
                if ($this->order_id && $this->app_id) {
                    $app_payment_method = self::CALLBACK_CANCEL;
                    $transaction_data['state'] = self::STATE_CANCELED;
                }
                $url = $this->getAdapter()->getBackUrl(waAppPayment::URL_FAIL, $transaction_data);
                break;
            default:
                $url = $this->getAdapter()->getBackUrl(waAppPayment::URL_FAIL, $transaction_data);
                break;
        }
        if ($app_payment_method) {
            $transaction_data = $this->saveTransaction($transaction_data, $request);
            $this->execAppCallback($app_payment_method, $transaction_data);
        }
        if ($transaction_result == 'result') {
            echo 'Success';
            return array(
                'template' => false,
            );
        } else {
            return array(
                'redirect' => $url,
            );
        }
    }

    private function getTransactionStatus($request)
    {
        return false;
    }


    /**
     * @param array|checkBillResponse $result
     * @return array
     */
    protected function formalizeData($result)
    {
        $transaction_data = parent::formalizeData(null);
        $transaction_data['native_id'] = $result['transaction_id'];
        $transaction_data['order_id'] = $this->order_id;
        $transaction_data['amount'] = $result['amount'];
        $transaction_data['currency_id'] = $result['currency'];
        $transaction_data['view_data'] = 'Pan: '.$result['pan_mask'];

        return $transaction_data;
    }




    private function getTaxId($item)
    {
        if (!isset($item['tax_rate'])) {
            $tax = '1'; //без НДС;
        } else {
            $tax_included = (!isset($item['tax_included']) || !empty($item['tax_included']));
            $rate = ifset($item['tax_rate']);
            if (in_array($rate, array(null, false, ''), true)) {
                $rate = 'none';
            }

            if (!$tax_included && $rate > 0) {
                throw new waPaymentException('Фискализация товаров с налогом не включенном в стоимость не поддерживается. Обратитесь к администратору магазина');
            }

            switch ($rate) {
                case 0:
                    $tax = 'vat0';//НДС по ставке 0%;
                    break;
                case 10:
                    if ($tax_included) {
                        $tax = 'vat10';//НДС чека по ставке 10%;
                    } else {
                        $tax = 'vat110';// НДС чека по расчетной ставке 10/110;
                    }
                    break;
                case 18:
                case 20:
                    if ($tax_included) {
                        $tax = 'vat20';//НДС чека по ставке 18%;
                    } else {
                        $tax = 'vat120';// НДС чека по расчетной ставке 18/118.
                    }
                    break;
                default:
                    $tax = 'none';//без НДС;
                    break;
            }
        }
        return $tax;
    }


    private function checkSign()
    {
        $post = waRequest::post();
        $key       = $this->mode == 'test' ? $this->test_secret_key : $this->secret_key;
        $signature = ModulbankHelper::calcSignature($key, $post);
        return strcasecmp($signature, $post['signature']) == 0;
    }

    private function getLogsPath()
    {
        $path = waConfig::get('wa_path_log');
        if (!$path) {
            $path = wa()->getConfig()->getRootPath().DIRECTORY_SEPARATOR.'wa-log';
        }
        $path = realpath($path.'/payment');
        return $path;
    }

    private function logger($data, $category)
    {
        if ($this->logging) {
            $path = $this->getLogsPath();
            $filename   = $path . '/modulbank.log';
            ModulbankHelper::log($filename, $data, $category, $this->log_size_limit);

        }
    }

    public function frontendCheckout($params)
    {
        $result = array(
            'contactinfo'  => 'HTML code for contact info step',
            'shipping'     => 'HTML code shipping step',
            'payment'      => 'HTML code for payment step',
            'confirmation' => 'HTML code for confirmation step',
            'success'      => 'HTML code for success step',
        );

        return $result[$params['step']];
    }


}
