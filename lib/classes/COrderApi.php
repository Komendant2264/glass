<?php
class COrderApi extends COrder
{
   public function GetOrderBasket($id, $api = false, $cancel_items = true)
   {
       $id = intval($id);
       if($id == 0 || $this->db == null) return false;
       $result = array();
       $id = $this->db->quote($id);
       $query = "select 
                        `cart`.`orderId`,
                        `cart`.`productId`,
                        `cart`.`fk_good`,       
                        `cart`.`price`,       
                        `cart`.`quantity`,       
                        `cart`.`productData`,       
                        `cart`.`userId`,       
                        `cart`.`delivery`,
                        `cart`.`avail`,
                        `cart`.`distr`,
                        `cart`.`codes`,
                        `cart`.`buy_price`,
                        `cart`.`date`,
                        `cart`.`type_reserve`,
                        `cart`.`assoc_reserve`,
                        `cart`.`canceled`,
                        `cart`.`payment`,
                        `p`.`extra`
                    from ".$this->tbl_basket." `cart`
                         left join `price_2016_07_16_18_03_30` `p` on `cart`.`productId` = `p`.`id`
                    where `cart`.`orderId` = ".$id." ".
           ($api ? 'AND (type_reserve=1 OR (type_reserve=2 AND assoc_reserve=0) )':'')." ".
           (!$cancel_items ? 'AND canceled = 0' : '')."
		order by `cart`.`assoc_reserve` DESC, `cart`.`id`";
       $basket = $this->db->query($query)->fetchAll();
       return $basket;
   }
}