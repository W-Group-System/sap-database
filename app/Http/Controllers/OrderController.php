<?php

namespace App\Http\Controllers;
use App\ORDR;
use App\ORDR_PBI;
use App\ORDR_CCC;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function salesOrderCCC() 
    {
        $salesOrdersPBI = ORDR_CCC::with(['items' => function($query) {
            $query->select('DocEntry', 'Dscription', 'ItemCode', 'Quantity');
        }])
        ->select('DocEntry', 'DocDate', 'DocNum', 'CardCode', 'CardName', 'U_Label', 'U_Packaging')
        ->where('DocStatus', 'O')
        ->get();
        
        return response()->json($salesOrdersPBI);
    }

    public function salesOrderWHIV2(Request $request) 
    {
        $response = [
            "message"=>"Failed to retrieved information.",
            "total"=>0,
            "page"=>1,
            "data"=>null
        ];
        $isSuccess = false;
        try {
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $buyersCode = $request->buyersCode ?? "";
            
            $salesOrdersWHI = ORDR::with(['items' => function($query) {
                $query->select('DocEntry', 'Dscription', 'ItemCode', 'Quantity');
            }])
            ->select('DocEntry', 'DocDate', 'DocNum', 'CardCode as Customer', 'NumAtCard as BuyersCode', 'CardName', 'U_Label', 'U_Packaging')
            ->where('DocStatus', 'O');

            if (!empty($buyersCode)) {
                $salesOrdersWHI = $salesOrdersWHI->where('NumAtCard',$buyersCode); 
            }

            if ($request->filled('startDate') && $request->filled('endDate')) {
                $start = Carbon::parse($request->startDate)->startOfDay();
                $end   = Carbon::parse($request->endDate)->endOfDay();

                $salesOrdersWHI->whereBetween('DocDate', [$start, $end]);
            }

            $totalCount = (clone $salesOrdersWHI)->count();

            $salesOrdersWHI = $salesOrdersWHI->orderBy("DocEntry","desc")
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();
            
            $response["message"] = "Successfully retrieved information.";
            $response["page"] = $page;
            $response["total"] = $totalCount;
            $response["data"] = $salesOrdersWHI;
            $isSuccess = true;
        } catch (\Throwable $th) {
            Log::error("ERROR IN salesOrderWHIV2: ".$th);
        }

        if ($isSuccess) {
            return response()->json($response, 200);
        }else{
            return response()->json($response, 400);
        }
    }

    public function salesOrderWHIDistinct(Request $request) 
    {
        $response = [
            "message"=>"Failed to retrieved information.",
            "total"=>0,
            "page"=>1,
            "data"=>null
        ];
        $isSuccess = false;
        try {
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $startDate = $request->startDate ?? "";
            $endDate = $request->endDate ?? "";
            
            $salesOrdersWHI = ORDR::select(DB::raw('MIN(DocDate) as DocDate'),'NumAtCard as BuyersCode', 'CardName',DB::raw('COUNT(NumAtCard) as Count'))
            ->where('DocStatus', 'O')
            ->groupBy('NumAtCard')
            ->groupBy('CardName');

            if ($request->filled('startDate') && $request->filled('endDate')) {
                $start = Carbon::parse($request->startDate)->startOfDay();
                $end   = Carbon::parse($request->endDate)->endOfDay();

                $salesOrdersWHI->whereBetween('DocDate', [$start, $end]);
            }

            $totalCount = (clone $salesOrdersWHI)->get()->count();

            $salesOrdersWHI = $salesOrdersWHI->orderBy("NumAtCard","desc")
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();
            
            $response["message"] = "Successfully retrieved information.";
            $response["page"] = $page;
            $response["total"] = $totalCount;
            $response["data"] = $salesOrdersWHI;
            $isSuccess = true;
        } catch (\Throwable $th) {
            Log::error("ERROR IN salesOrderWHIDistinct: ".$th);
        }

        if ($isSuccess) {
            return response()->json($response, 200);
        }else{
            return response()->json($response, 400);
        }
    }


    public function salesOrderPBIV2(Request $request) 
    {
        $response = [
            "message"=>"Failed to retrieved information.",
            "total"=>0,
            "page"=>1,
            "data"=>null
        ];
        $isSuccess = false;
        try {
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $buyersCode = $request->buyersCode ?? "";
            
            $salesOrdersWHI = ORDR_PBI::with(['items' => function($query) {
                $query->select('DocEntry', 'Dscription', 'ItemCode', 'Quantity');
            }])
            ->select('DocEntry', 'DocDate', 'DocNum', 'CardCode as Customer', 'NumAtCard as BuyersCode', 'CardName', 'U_Label', 'U_Packaging')
            ->where('DocStatus', 'O');

            if (!empty($buyersCode)) {
                $salesOrdersWHI = $salesOrdersWHI->where('NumAtCard',$buyersCode); 
            }

            if ($request->filled('startDate') && $request->filled('endDate')) {
                $start = Carbon::parse($request->startDate)->startOfDay();
                $end   = Carbon::parse($request->endDate)->endOfDay();

                $salesOrdersWHI->whereBetween('DocDate', [$start, $end]);
            }

            $totalCount = (clone $salesOrdersWHI)->count();

            $salesOrdersWHI = $salesOrdersWHI->orderBy("DocEntry","desc")
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();
            
            $response["message"] = "Successfully retrieved information.";
            $response["page"] = $page;
            $response["total"] = $totalCount;
            $response["data"] = $salesOrdersWHI;
            $isSuccess = true;
        } catch (\Throwable $th) {
            Log::error("ERROR IN salesOrderWHIV2: ".$th);
        }

        if ($isSuccess) {
            return response()->json($response, 200);
        }else{
            return response()->json($response, 400);
        }
    }

    public function salesOrderPBIDistinct(Request $request) 
    {
        $response = [
            "message"=>"Failed to retrieved information.",
            "total"=>0,
            "page"=>1,
            "data"=>null
        ];
        $isSuccess = false;
        try {
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $startDate = $request->startDate ?? "";
            $endDate = $request->endDate ?? "";
            
            $salesOrdersWHI = ORDR_PBI::select(DB::raw('MIN(DocDate) as DocDate'),'NumAtCard as BuyersCode', 'CardName',DB::raw('COUNT(NumAtCard) as Count'))
            ->where('DocStatus', 'O')
            ->groupBy('NumAtCard')
            ->groupBy('CardName');

            if ($request->filled('startDate') && $request->filled('endDate')) {
                $start = Carbon::parse($request->startDate)->startOfDay();
                $end   = Carbon::parse($request->endDate)->endOfDay();

                $salesOrdersWHI->whereBetween('DocDate', [$start, $end]);
            }

            $totalCount = (clone $salesOrdersWHI)->get()->count();

            $salesOrdersWHI = $salesOrdersWHI->orderBy("NumAtCard","desc")
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();
            
            $response["message"] = "Successfully retrieved information.";
            $response["page"] = $page;
            $response["total"] = $totalCount;
            $response["data"] = $salesOrdersWHI;
            $isSuccess = true;
        } catch (\Throwable $th) {
            Log::error("ERROR IN salesOrderWHIDistinct: ".$th);
        }

        if ($isSuccess) {
            return response()->json($response, 200);
        }else{
            return response()->json($response, 400);
        }
    }

    public function salesOrderCCCV2(Request $request) 
    {
        $response = [
            "message"=>"Failed to retrieved information.",
            "total"=>0,
            "page"=>1,
            "data"=>null
        ];
        $isSuccess = false;
        try {
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $buyersCode = $request->buyersCode ?? "";
            
            $salesOrdersWHI = ORDR_PBI::with(['items' => function($query) {
                $query->select('DocEntry', 'Dscription', 'ItemCode', 'Quantity');
            }])
            ->select('DocEntry', 'DocDate', 'DocNum', 'CardCode as Customer', 'NumAtCard as BuyersCode', 'CardName', 'U_Label', 'U_Packaging')
            ->where('DocStatus', 'O');

            if (!empty($buyersCode)) {
                $salesOrdersWHI = $salesOrdersWHI->where('NumAtCard',$buyersCode); 
            }

            if ($request->filled('startDate') && $request->filled('endDate')) {
                $start = Carbon::parse($request->startDate)->startOfDay();
                $end   = Carbon::parse($request->endDate)->endOfDay();

                $salesOrdersWHI->whereBetween('DocDate', [$start, $end]);
            }

            $totalCount = (clone $salesOrdersWHI)->count();

            $salesOrdersWHI = $salesOrdersWHI->orderBy("DocEntry","desc")
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();
            
            $response["message"] = "Successfully retrieved information.";
            $response["page"] = $page;
            $response["total"] = $totalCount;
            $response["data"] = $salesOrdersWHI;
            $isSuccess = true;
        } catch (\Throwable $th) {
            Log::error("ERROR IN salesOrderWHIV2: ".$th);
        }

        if ($isSuccess) {
            return response()->json($response, 200);
        }else{
            return response()->json($response, 400);
        }
    }

    public function salesOrderCCCDistinct(Request $request) 
    {
        $response = [
            "message"=>"Failed to retrieved information.",
            "total"=>0,
            "page"=>1,
            "data"=>null
        ];
        $isSuccess = false;
        try {
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $startDate = $request->startDate ?? "";
            $endDate = $request->endDate ?? "";
            
            $salesOrdersWHI = ORDR_PBI::select(DB::raw('MIN(DocDate) as DocDate'),'NumAtCard as BuyersCode', 'CardName',DB::raw('COUNT(NumAtCard) as Count'))
            ->where('DocStatus', 'O')
            ->groupBy('NumAtCard')
            ->groupBy('CardName');

            if ($request->filled('startDate') && $request->filled('endDate')) {
                $start = Carbon::parse($request->startDate)->startOfDay();
                $end   = Carbon::parse($request->endDate)->endOfDay();

                $salesOrdersWHI->whereBetween('DocDate', [$start, $end]);
            }

            $totalCount = (clone $salesOrdersWHI)->get()->count();

            $salesOrdersWHI = $salesOrdersWHI->orderBy("NumAtCard","desc")
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();
            
            $response["message"] = "Successfully retrieved information.";
            $response["page"] = $page;
            $response["total"] = $totalCount;
            $response["data"] = $salesOrdersWHI;
            $isSuccess = true;
        } catch (\Throwable $th) {
            Log::error("ERROR IN salesOrderWHIDistinct: ".$th);
        }

        if ($isSuccess) {
            return response()->json($response, 200);
        }else{
            return response()->json($response, 400);
        }
    }
}
