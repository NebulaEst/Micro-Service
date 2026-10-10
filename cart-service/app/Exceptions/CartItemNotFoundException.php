<?php

namespace App\Exceptions;

class CartItemNotFoundException extends \RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Item keranjang dengan id {$id} tidak ditemukan");
    }
}
