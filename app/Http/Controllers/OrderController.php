<?php

namespace App\Http\Controllers;
use App\ORDR;
use App\ORDR_PBI;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function salesOrderWHI() 
    {
        $salesOrdersWHI = ORDR::with(['items' => function($query) {
            $query->select('DocEntry', 'Dscription', 'ItemCode', 'Quantity');
        }])
        ->select('DocEntry', 'DocDate', 'DocNum', 'CardCode', 'CardName', 'U_Label', 'U_Packaging')
        ->where('DocStatus', 'O')
        ->get();

        return response()->json($salesOrdersWHI);
    }

    public function salesOrderPBI() 
    {
        $salesOrdersPBI = ORDR_PBI::with(['items' => function($query) {
            $query->select('DocEntry', 'Dscription', 'ItemCode', 'Quantity');
        }])
        ->select('DocEntry', 'DocDate', 'DocNum', 'CardCode', 'CardName', 'U_Label', 'U_Packaging')
        ->where('DocStatus', 'O')
        ->get();
        
        return response()->json($salesOrdersPBI);
    }
}
