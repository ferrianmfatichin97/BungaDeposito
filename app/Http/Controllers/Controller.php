<?php

namespace App\Http\Controllers;
use App\Models\Remote_data_deposito_master;
use Illuminate\Http\Request;

abstract class Controller
{
    public function index()
    {
        $data = Remote_data_deposito_master::getDepositoData();

        return response()->json(['data' => $data]);
    }
}
