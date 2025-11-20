<?php
namespace GlassApi;
use COrder;
class getOrders extends GlassApi
{
    public $filters = [];
    public $options  = [];
    public $sort = [];
    public function executeAction() {
        $COrder = new COrder();
        $statuses = $this->prepareStatuses($COrder->GetStatuses());
        $orders = $COrder->newGetOrders($this->filters, $this->options, $this->sort);

        $arrOrders = [];
        foreach ($orders as $order)
        {
            $arrOrder = [
                "orderId" => $order['orderId'],
                "prepaid" => $order['prepaid'],
                "orderStatusPartnerId" => $order['orderStatusPartnerId'],
                "historyPartner" => $order['historyPartner'],
                "commentPartner" => $order['commentPartner'],
                "delivery_type" => $order['delivery_type'],
                "user_phone" => $order['user_phone'],
                "guest_name" => $order['guest_name'],
                "date" => $order['date'],
                "delivery_time" => $order['delivery_time'],
                "filialID" => $order['filialID'],
                "id_executor" => $order['id_executor'],
                "executionDate" => $order['executionDate'],
                "updated_date" => $order['updated_date'],
                "red_user" => $order['red_user'],
                "status" => $order['status'],
                "user_id" => $order['user_id'],
                "user_name"=> $order['user_name'],
                "basket" => []
            ];
            $statusID = $order['orderStatusId'];

            // Статус заказа
            if ((int)$order['orderStatusId'] > 0 && in_array($statusID,$statuses))
            {
                $arrOrder["orderStatus"] = [
                    "id" => $statusID,
                    "name" => $statuses[$statusID]['orderStatusName']
                ];
            }
            else
            {
                $arrOrder["orderStatus"] = [];
            }

            // Корзина
            $basket = $COrder->GetOrderBasket($order['orderId']);
            if (is_array($basket) && count($basket) > 0) {
                foreach($basket as $item)
                {
                    if(!$item['canceled'])//Если резервы не отменены
                    {
                        $item['productData'] = str_replace(array("\r\n", "\r", "\n"), ' ', $item['productData']);
                        $data_u = preg_replace_callback(
                            '!s:(\d+):"(.*?)";!',
                            function($m) {
                                return 's:'.strlen($m[2]).':"'.$m[2].'";';
                            },
                            $item['productData']);
                        try {
                            $data = unserialize($data_u);

                            if(!empty($data['name']) && !empty($data['brand']) && !empty($item['codes']) && !empty($item['buy_price'])) {
                                $position = $item['codes'].", ".$data['name'].", ".$data['brand'].", ".$item['buy_price'];
                                $arrOrder['basket'][] = [
                                    'productId' => $item['productId'],
                                    'position' => $position,
                                    'codes' => $item['codes'],
                                    'name' => $data['name'],
                                    'brand' => $data['brand'],
                                    'buy_price' => $item['buy_price']
                                ];
                            }
                        } catch (Exception $e) {
                            echo 'PHP перехватил исключение: ',  $e->getMessage(), "\n";
                            echo json_encode($item);
                        }
                    }
                }
            }
            $arrOrders[]=$arrOrder;
        }
        return array(
            "orders" => $arrOrders
        );
    }

    private function prepareStatuses(array $GetStatuses)
    {
        $arrStatuses = [];
        foreach ($GetStatuses as $status) {
            $arrStatuses[$status['orderStatusId']] = $status;
        }
        return $arrStatuses;
    }
}