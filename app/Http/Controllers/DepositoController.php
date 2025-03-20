<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Remote_data_deposito_master;

class DepositoController extends Controller
{
    public function index()
    {
        $data = Remote_data_deposito_master::getDepositoData();
        return response()->json(['data' => $data], 200, [], JSON_PRETTY_PRINT);

        //return response()->json($data);
    }
}
