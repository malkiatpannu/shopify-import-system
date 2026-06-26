<?php

namespace App\Jobs;

use App\Models\ErrorLog;
use App\Models\ImportRecord;
use App\Models\Product;
use App\Models\Upload;
use App\Services\ShopifyService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessCsvImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public int $uploadId
    ) {
    }

    public function handle(
        ShopifyService $shopifyService
    ): void {
        Log::info('Csv import processing started', [
            'upload_id'     =>  $this->uploadId
        ]);
        $upload             =   Upload::findOrFail($this->uploadId);
        $upload->update([
            'status'        =>  'processing',
            'started_at'    =>  now()
        ]);
        try {
            $filePath       =   storage_path('app/private/' . $upload->file_path);
            if (!file_exists($filePath)) {
                throw new Exception('Uploaded CSV file not found.');
            }
            $rows           =   array_map('str_getcsv', file($filePath));

            if (empty($rows)) {
                throw new Exception('CSV file is empty.');
            }

            $header         =   array_shift($rows);
            // Remove UTF-8 BOM if present
            $header[0]      =   preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

            $requiredColumns=   [
                'Title',
                'Variant SKU',
                'Variant Price',
            ];

            foreach ($requiredColumns as $column) {
                if (!in_array($column, $header)) {
                    throw new Exception(
                        "Missing required CSV column: {$column}"
                    );
                }
            }
            $upload->update([
                'total_records' =>  count($rows)
            ]);
            foreach ($rows as $index => $rowData) {
                $rowNumber  =   $index + 2;
                $product    =   null;
                try {
                    if (count($header) !== count($rowData)) {

                        ErrorLog::create([
                            'upload_id' =>  $upload->id,
                            'source'    =>  'CSV Validation',
                            'message'   =>  "Row {$rowNumber}: Invalid column count.",
                        ]);

                        $upload->increment('failed_records');
                        $upload->increment('processed_records');
                        continue;
                    }

                    $row    =   array_combine($header, $rowData);
                    DB::beginTransaction();
                    if (empty(trim($row['Title'] ?? ''))) {
                        throw new Exception("Row {$rowNumber}: Product title is required.");
                    }
                    if (
                        !is_numeric($row['Variant Price']) ||
                        $row['Variant Price'] < 0
                    ) {
                        throw new Exception("Row {$rowNumber}: Invalid product price.");
                    }

                    $product    =   Product::updateOrCreate(
                        [
                            'sku'           =>  $row['Variant SKU'] ?? null,
                        ],
                        [
                            'upload_id'     =>  $upload->id,
                            'handle'        =>  $row['Handle'] ?? null,
                            'title'         =>  $row['Title'] ?? '',
                            'body_html'     =>  $row['Body HTML'] ?? null,
                            'vendor'        =>  $row['Vendor'] ?? null,
                            'product_type'  =>  $row['Product Type'] ?? null,
                            'tags'          =>  $row['Tags'] ?? null,
                            'published'     =>
                                strtolower(
                                    $row['Published'] ?? 'false'
                                ) === 'true',
                            'sku'           =>
                                $row['Variant SKU'] ?? null,
                            'price'         =>
                                $row['Variant Price'] ?? 0,
                            'compare_at_price'  =>
                                $row['Variant Compare At Price'] ?? null,
                            'requires_shipping' =>
                                strtolower(
                                    $row['Variant Requires Shipping'] ?? 'true'
                                ) === 'true',
                            'taxable'       =>
                                strtolower(
                                    $row['Variant Taxable'] ?? 'true'
                                ) === 'true',
                            'inventory_tracker' =>
                                $row['Variant Inventory Tracker'] ?? null,
                            'inventory_qty'     =>
                                $row['Variant Inventory Qty'] ?? 0,
                            'inventory_policy'  =>
                                $row['Variant Inventory Policy'] ?? null,
                            'fulfillment_service'   =>
                                $row['Variant Fulfillment Service'] ?? null,
                            'weight'        =>
                                $row['Variant Weight'] ?? 0,
                            'weight_unit'   =>
                                $row['Variant Weight Unit'] ?? null,
                            'image_src'     =>
                                $row['Image Src'] ?? null,
                            'image_position'=>
                                $row['Image Position'] ?? null,
                            'image_alt_text'=>
                                $row['Image Alt Text'] ?? null,
                            'status'        =>  'processing'
                        ]
                    );

                    $shopifyResponse    =   $shopifyService
                            ->createOrUpdateProduct($product);

                    $product->update([
                        'shopify_product_id'    =>  $shopifyResponse['product_id'],
                        'shopify_variant_id'    =>  $shopifyResponse['variant_id'],
                        'status'                =>  'success',
                        'error_message'         =>  null,
                    ]);

                    ImportRecord::create([
                        'upload_id'         =>  $upload->id,
                        'product_id'        =>  $product->id,
                        'action'            =>  $shopifyResponse['action'],
                        'status'            =>  'success',
                        'request_payload'   =>  json_encode(
                                $shopifyResponse['request']
                            ),
                        'response_payload'  =>  json_encode(
                                $shopifyResponse['response']
                            ),
                        'message'           =>  'Product imported successfully.'
                    ]);

                    DB::commit();
                    $upload->increment('successful_records');
                    Log::info('Product imported successfully.', [
                        'upload_id'     =>  $upload->id,
                        'product_id'    =>  $product->id,
                        'sku'           =>  $product->sku,
                    ]);
                } catch (Throwable $e) {
                    DB::rollBack();
                    if ($product) {
                        $product->update([
                            'status'        => 'failed',
                            'error_message' =>  $e->getMessage(),
                        ]);
                    }

                    ErrorLog::create([
                        'upload_id'     =>  $upload->id,
                        'product_id'    =>  $product->id ?? null,
                        'source'        =>  'CSV Processing',
                        'message'       =>  $e->getMessage()
                    ]);

                    ImportRecord::create([
                        'upload_id'     =>  $upload->id,
                        'product_id'    =>  $product->id ?? null,
                        'action'        =>  'create',
                        'status'        =>  'failed',
                        'message'       =>  $e->getMessage()
                    ]);

                    $upload->increment('failed_records');

                    Log::error('Product import failed.', [
                        'upload_id'     =>  $upload->id,
                        'row'           =>  $rowNumber,
                        'sku'           =>  $row['Variant SKU'] ?? null,
                        'error'         =>  $e->getMessage(),
                    ]);
                }finally {
                    $upload->increment('processed_records');
                }
            }

            $upload->update([
                'status'        =>  'completed',
                'completed_at'  =>  now()
            ]);
            Log::info('CSV import completed.', [
                'upload_id'     =>  $upload->id,
                'total'         =>  $upload->total_records,
                'successful'    =>  $upload->successful_records,
                'failed'        =>  $upload->failed_records,
            ]);
        } catch (Throwable $e) {
            $upload->update([
                'status'        =>  'failed',
                'completed_at'  =>  now(),
            ]);

            ErrorLog::create([
                'upload_id'     =>  $upload->id,
                'source'        =>  'CSV Processing',
                'message'       =>  $e->getMessage()
            ]);
            Log::error('CSV processing failed.', [
                'upload_id'     =>  $upload->id,
                'error'         =>  $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        $upload                 =   Upload::find($this->uploadId);
        if (!$upload) {
            return;
        }

        $upload->update([
            'status'            =>  'failed',
            'completed_at'      =>  now(),
        ]);

        ErrorLog::create([
            'upload_id'         =>  $upload->id,
            'source'            =>  'Queue',
            'message'           =>  $exception->getMessage(),
        ]);

        Log::error('CSV import job failed after all retry attempts.', [
            'upload_id'         =>  $upload->id,
            'error'             =>  $exception->getMessage(),
        ]);
    }
}