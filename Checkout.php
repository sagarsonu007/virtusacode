<?php

namespace App;

class Checkout implements CheckoutInterface
{
    
    protected $cart = [];
    
    protected $pricing = [
        'A' => 50,
        'B' => 30,
        'C' => 20,
        'D' => 15,
        'E' => 5
    ];
    
    protected $discounts = [
        'A' => [
            'threshold' => 3,
            'amount' => 20
        ],
        'B' => [
            'threshold' => 2,
            'amount' => 15
        ],
        'C' => [
            ['threshold' => 2,
            'amount' => 2],
            ['threshold' => 3,
            'amount' => 10],
        ],
    ];

    
    protected $stats = [
        'A' => 0,
        'B' => 0,
        'C' => 0,
        'D' => 0,
        'E' => 0,
    ];

    
    public function scan(string $sku)
    {
        if (!array_key_exists($sku, $this->pricing)) {
            return;
        }

        $this->stats[$sku] = $this->stats[$sku] + 1;

        $this->cart[] = [
            'sku' => $sku,
            'price' => $this->pricing[$sku]
        ];
    }

    
    public function total(): int
   {
        $standardPrices = array_reduce($this->cart, function ($total, array $product) {
            $total += $product['price'];
            return $total;
        }) ?? 0;

        $totalDiscount = 0;

        foreach ($this->discounts as $key => $discount) {
            if (count($discount)>1) {
               foreach($discount as $k=>$d){
                    if ($this->stats[$k] >= $d['threshold']) {
                        $numberOfSets = floor($this->stats[$k] / $d['threshold']);
                        $totalDiscount += ($d['amount'] * $numberOfSets);
                    }

               }
            }elseif ($this->stats[$key] >= $discount['threshold']) {
                $numberOfSets = floor($this->stats[$key] / $discount['threshold']);
                $totalDiscount += ($discount['amount'] * $numberOfSets);
            }
        }

        return $standardPrices - $totalDiscount;
    }
}
