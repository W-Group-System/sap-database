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
            $soNumber = $request->soNumber ?? "";
            $apiKey = $request->header('apikey')??"";
            
            if (empty($apiKey)) {
                $response["message"] = "Invalid API key.";
                return response()->json($response, 400);
            }

            if ($apiKey != config('app.api_key')) {
                $response["message"] = "Invalid API key.";
                return response()->json($response, 400);
            }
            
            $salesOrdersWHI = ORDR::with(['items' => function($query) {
                $query->select('DocEntry', 'Dscription', 'ItemCode', 'Quantity');
            },
            'bdeName'  => function($bde) {
                $bde->select('SlpName','SlpCode');
            },
            'contactName'  => function($cnt) {
                $cnt->select('CntctCode','Name');
            }])
            ->select(
                'DocEntry', 
                'DocDate', 
                'DocNum', 
                'CardCode as Customer', 
                'NumAtCard as BuyersCode', 
                'CardName', 
                'U_Label', 
                'U_Packaging',
                'CntctCode',
                'SlpCode',
                'U_BuyersPO',
                DB::raw("CASE WHEN COALESCE(U_Inco,'') <> '' THEN U_Inco WHEN COALESCE(U_Delivery,'') <> '' THEN U_Delivery ELSE '' END AS IncoTerms"),
                DB::raw("CASE WHEN U_PortDestination IS NOT NULL THEN U_PortDestination WHEN COALESCE(U_CountryDen,'') <> '' THEN U_CountryDen ELSE '' END AS PortOfDestination"),
                'U_CountryDen',
                'U_Onpallet',
                'U_Modeship',
                'U_PortLoad as LoadingPort'
            )
            ->where('DocStatus', 'O');

            if (!empty($buyersCode)) {
                $salesOrdersWHI = $salesOrdersWHI->where('NumAtCard',$buyersCode); 
            }
            if (!empty($soNumber)) {
                $salesOrdersWHI = $salesOrdersWHI->where('DocNum',$soNumber); 
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
            $endDate = $request->endDate ?? $startDate;
            $dateCreated = $request->dateCreated ?? "";
            $buyersCode = $request->buyersCode ?? "";
            $buyersName = $request->buyersName ?? "";
            $apiKey = $request->header('apikey')??"";

            if (empty($apiKey)) {
                $response["message"] = "Invalid API key.";
                return response()->json($response, 400);
            }

            if ($apiKey != config('app.api_key')) {
                $response["message"] = "Invalid API key.";
                return response()->json($response, 400);
            }
            
            $salesOrdersWHI = ORDR::select(DB::raw('MIN(DocDate) as DocDate'),'NumAtCard as BuyersCode', 'CardName',DB::raw('COUNT(NumAtCard) as Count'))
            ->where('DocStatus', 'O');

            if ($request->filled('startDate')) {
                $start = Carbon::parse($request->startDate)->startOfDay();
                $end   = Carbon::parse($request->endDate)->endOfDay();

                $salesOrdersWHI->whereBetween('DocDate', [$start, $end]);
            }

            if (!empty($buyersCode)) {
                $salesOrdersWHI->where('NumAtCard', 'like', '%' . $buyersCode . '%');
            }
            if (!empty($buyersName)) {
                $salesOrdersWHI->where('CardName', 'like', '%' . $buyersName . '%');
            }
            if (!empty($dateCreated)) {
                $parsedDateCreated = Carbon::parse($dateCreated);
                $salesOrdersWHI->where('DocDate', $parsedDateCreated);
            }

            $salesOrdersWHI = $salesOrdersWHI->groupBy('NumAtCard')
            ->groupBy('CardName');
            $totalCount = (clone $salesOrdersWHI)->get()->count();

            $salesOrdersWHI = $salesOrdersWHI->orderByRaw('MIN(DocDate) DESC')
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
            $soNumber = $request->soNumber ?? "";
            $apiKey = $request->header('apikey')??"";

            if (empty($apiKey)) {
                $response["message"] = "Invalid API key.";
                return response()->json($response, 400);
            }

            if ($apiKey != config('app.api_key')) {
                $response["message"] = "Invalid API key.";
                return response()->json($response, 400);
            }

            $salesOrdersWHI = ORDR_PBI::with(['items' => function($query) {
                $query->select('DocEntry', 'Dscription', 'ItemCode', 'Quantity');
            },
            'bdeName'  => function($bde) {
                $bde->select('SlpName','SlpCode');
            },
            'contactName'  => function($bde) {
                $bde->select('CntctCode','Name');
            }])
            ->select(
                'DocEntry', 
                'DocDate', 
                'DocNum', 
                'CardCode as Customer', 
                'NumAtCard as BuyersCode', 
                'CardName', 
                'U_Label', 
                'U_Packaging',
                'CntctCode',
                'SlpCode',
                'U_BuyersPO',
                DB::raw("CASE WHEN COALESCE(U_Inco,'') <> '' THEN U_Inco WHEN COALESCE(U_Delivery,'') <> '' THEN U_Delivery ELSE '' END AS IncoTerms"),
                'U_Destinationport AS PortOfDestination',
                'U_CountryDen',
                'U_Onpallet',
                'U_Modeship',
                'U_Loadingport as LoadingPort'
            )
            ->where('DocStatus', 'O');

            if (!empty($buyersCode)) {
                $salesOrdersWHI = $salesOrdersWHI->where('NumAtCard',$buyersCode); 
            }
            if (!empty($soNumber)) {
                $salesOrdersWHI = $salesOrdersWHI->where('DocEntry',$soNumber); 
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
            $endDate = $request->endDate ?? $startDate;
            $dateCreated = $request->dateCreated ?? "";
            $buyersCode = $request->buyersCode ?? "";
            $buyersName = $request->buyersName ?? "";
            $apiKey = $request->header('apikey')??"";

            if (empty($apiKey)) {
                $response["message"] = "Invalid API key.";
                return response()->json($response, 400);
            }

            if ($apiKey != config('app.api_key')) {
                $response["message"] = "Invalid API key.";
                return response()->json($response, 400);
            }
            
            $salesOrdersWHI = ORDR_PBI::select(DB::raw('MIN(DocDate) as DocDate'),'NumAtCard as BuyersCode', 'CardName',DB::raw('COUNT(NumAtCard) as Count'))
            ->where('DocStatus', 'O');

            if ($request->filled('startDate')) {
                $start = Carbon::parse($request->startDate)->startOfDay();
                $end   = Carbon::parse($request->endDate)->endOfDay();

                $salesOrdersWHI->whereBetween('DocDate', [$start, $end]);
            }

            if (!empty($buyersCode)) {
                $salesOrdersWHI->where('NumAtCard', 'like', '%' . $buyersCode . '%');
            }
            if (!empty($buyersName)) {
                $salesOrdersWHI->where('CardName', 'like', '%' . $buyersName . '%');
            }
            if (!empty($dateCreated)) {
                $parsedDateCreated = Carbon::parse($dateCreated);
                $salesOrdersWHI->where('DocDate', $parsedDateCreated);
            }

            $salesOrdersWHI = $salesOrdersWHI->groupBy('NumAtCard')
            ->groupBy('CardName');

            $totalCount = (clone $salesOrdersWHI)->get()->count();

            $salesOrdersWHI = $salesOrdersWHI->orderByRaw('MIN(DocDate) DESC')
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
            $soNumber = $request->soNumber ?? "";
            $apiKey = $request->header('apikey')??"";
            
            if (empty($apiKey)) {
                $response["message"] = "Invalid API key.";
                return response()->json($response, 400);
            }

            if ($apiKey != config('app.api_key')) {
                $response["message"] = "Invalid API key.";
                return response()->json($response, 400);
            }

            $salesOrdersWHI = ORDR_CCC::with(['items' => function($query) {
                $query->select('DocEntry', 'Dscription', 'ItemCode', 'Quantity');
            },
            'bdeName'  => function($bde) {
                $bde->select('SlpName','SlpCode');
            },
            'contactName'  => function($cnt) {
                $cnt->select('CntctCode','Name');
            }])
            ->select(
                'DocEntry', 
                'DocDate', 
                'DocNum', 
                'CardCode as Customer', 
                'NumAtCard as BuyersCode', 
                'CardName', 
                'U_Label', 
                'U_Packaging',
                'CntctCode',
                'SlpCode',
                'U_BuyersPO',
                DB::raw("CASE WHEN COALESCE(U_Inco,'') <> '' THEN U_Inco WHEN COALESCE(U_Delivery,'') <> '' THEN U_Delivery ELSE '' END AS IncoTerms"),
                'U_Destinationport AS PortOfDestination',
                'U_CountryDen',
                'U_Onpallet',
                'U_Modeship',
                'U_Loadingport as LoadingPort'
            )
            ->where('DocStatus', 'O');

            if (!empty($buyersCode)) {
                $salesOrdersWHI = $salesOrdersWHI->where('NumAtCard',$buyersCode); 
            }
            if (!empty($soNumber)) {
                $salesOrdersWHI = $salesOrdersWHI->where('DocEntry',$soNumber); 
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
            $endDate = $request->endDate ?? $startDate;
            $dateCreated = $request->dateCreated ?? "";
            $buyersCode = $request->buyersCode ?? "";
            $buyersName = $request->buyersName ?? "";
            $apiKey = $request->header('apikey')??"";
            
            if (empty($apiKey)) {
                $response["message"] = "Invalid API key.";
                return response()->json($response, 400);
            }

            if ($apiKey != config('app.api_key')) {
                $response["message"] = "Invalid API key.";
                return response()->json($response, 400);
            }
            
            $salesOrdersWHI = ORDR_CCC::select(DB::raw('MIN(DocDate) as DocDate'),'NumAtCard as BuyersCode', 'CardName',DB::raw('COUNT(NumAtCard) as Count'))
            ->where('DocStatus', 'O');

            if ($request->filled('startDate')) {
                $start = Carbon::parse($request->startDate)->startOfDay();
                $end   = Carbon::parse($request->endDate)->endOfDay();

                $salesOrdersWHI->whereBetween('DocDate', [$start, $end]);
            }

            if (!empty($buyersCode)) {
                $salesOrdersWHI->where('NumAtCard', 'like', '%' . $buyersCode . '%');
            }
            if (!empty($buyersName)) {
                $salesOrdersWHI->where('CardName', 'like', '%' . $buyersName . '%');
            }
            if (!empty($dateCreated)) {
                $parsedDateCreated = Carbon::parse($dateCreated);
                $salesOrdersWHI->where('DocDate', $parsedDateCreated);
            }
            
            $salesOrdersWHI = $salesOrdersWHI->groupBy('NumAtCard')
            ->groupBy('CardName');

            $totalCount = (clone $salesOrdersWHI)->get()->count();

            $salesOrdersWHI = $salesOrdersWHI->orderByRaw('MIN(DocDate) DESC')
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
