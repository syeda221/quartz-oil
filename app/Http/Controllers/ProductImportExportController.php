<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Subcategory;
use App\Models\WarehouseStock;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ProductImportExportController extends Controller
{
    // ──────────────────────────────────────────────────────────
    //  TEMPLATE  –  blank CSV with correct headers and clean examples
    // ──────────────────────────────────────────────────────────
    public function template()
    {
        $headers = $this->csvHeaders();

        $callback = function () use ($headers) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM so Microsoft Excel opens special characters correctly
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            // Row 1 & 2: Carton based product with 2 variants (Carton & Loose Pcs)
            // Leave 'Item Code' blank for NEW products (system will auto-generate ITEM-XXXX)
            fputcsv($handle, [
                '', // Leave empty for new product
                'Ceramic Wall Tile 30x60', 'Tiles', 'Wall Tiles', 'Master',
                'by_cartons', '8', // 8 pieces per carton
                '0', '0', '1',
                'Ceramic Wall Tile 30x60 (Carton)', '30x60', 'Glossy White', 'Carton', '8', '0',
                '3200', '3000', '2400', '5', '', '1',
                '20', '4', '164', // 20 Cartons, 4 Loose Pieces = (20*8)+4 = 164 Pcs
            ]);

            fputcsv($handle, [
                '', // Same empty Item Code + same Product Name groups this variant under the same product
                'Ceramic Wall Tile 30x60', 'Tiles', 'Wall Tiles', 'Master',
                'by_cartons', '8',
                '0', '0', '1',
                'Ceramic Wall Tile 30x60 (Piece)', '30x60', 'Glossy White', 'Pcs', '1', '0',
                '400', '375', '300', '10', '', '0',
                '0', '0', '0',
            ]);

            // Row 3: Piece based product (Single item / clothing / general merchandise)
            fputcsv($handle, [
                '', // Leave empty for new product
                'Cotton Polo T-Shirt', 'Apparel', 'Shirts', 'Polo',
                'by_pieces', '1',
                '0', '0', '1',
                'Cotton Polo T-Shirt Blue M', 'M', 'Blue', 'Pcs', '1', '0',
                '1500', '1400', '1000', '5', '', '1',
                '0', '50', '50', // 50 Pieces
            ]);

            // Row 4: Weight based product (by_kg)
            fputcsv($handle, [
                '', // Leave empty for new product
                'MS Hex Bolt 3/8 x 2', 'Hardware', 'Bolts', 'General',
                'by_kg', '1',
                '0', '0', '1',
                'MS Hex Bolt 3/8 x 2 (Kg)', '-', '-', 'Kg', '1', '1000',
                '650', '600', '450', '5', '', '1',
                '0', '0', '25', // 25 Kg
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products_import_template.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    //  EXPORT  –  all products as CSV (Splitting Variants + Live Stock)
    // ──────────────────────────────────────────────────────────
    public function export(Request $request)
    {
        $query = Product::with([
            'category_relation',
            'sub_category_relation',
            'brand'
        ])
        ->withSum('warehouseStocks as live_total_pieces', 'total_pieces')
        ->orderBy('id');
        
        // Basic filtering
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $headers = $this->csvHeaders();

        $callback = function () use ($query, $headers) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            // Chunk query to prevent memory exhaustion on large datasets (17k+ products)
            $query->chunk(500, function ($products) use ($handle) {
                foreach ($products as $p) {
                    $sizeMode  = $p->size_mode ?? 'by_pieces';
                    $pcsPerBox = $p->pieces_per_box > 0 ? (int)$p->pieces_per_box : 1;
                    
                    // Live stock from warehouse_stocks table
                    $liveTotalPieces = (float)($p->live_total_pieces ?? 0);
                    
                    // Calculate cartons and loose pieces
                    $cartonStock = 0;
                    $loosePiecesStock = 0;
                    if ($sizeMode === 'by_cartons' && $pcsPerBox > 1) {
                        $cartonStock      = floor(max(0, $liveTotalPieces) / $pcsPerBox);
                        $loosePiecesStock = fmod(max(0, $liveTotalPieces), $pcsPerBox);
                    } else {
                        $cartonStock      = 0;
                        $loosePiecesStock = $liveTotalPieces;
                    }

                    $variants = [];
                    if (!empty($p->color)) {
                        $parsed = is_string($p->color) ? json_decode($p->color, true) : $p->color;
                        if (is_array($parsed) && count($parsed) > 0 && isset($parsed[0]['name'])) {
                            $variants = $parsed;
                        }
                    }
                    
                    // Fallback to single variant row if no variants defined
                    if (empty($variants)) {
                        $vUnit = $sizeMode === 'by_kg' ? 'Kg' : ($sizeMode === 'by_gm' ? 'Gm' : ($sizeMode === 'by_cartons' ? 'Carton' : 'Pcs'));
                        fputcsv($handle, [
                            $p->item_code,
                            $p->item_name,
                            $p->category_relation->name ?? '',
                            $p->sub_category_relation->name ?? '',
                            $p->brand->name ?? '',
                            $sizeMode,
                            $pcsPerBox,
                            $p->sale_discount_percent ?? 0,
                            $p->purchase_discount_percent ?? 0,
                            $p->is_active ? 1 : 0,
                            // Variant columns
                            $p->item_name,  // Variant Name
                            '-',            // Variant Size
                            '-',            // Variant Color
                            $vUnit,         // Variant Unit
                            $sizeMode === 'by_cartons' ? $pcsPerBox : '1', // Conv Factor
                            $vUnit === 'Kg' ? '1000' : '0', // Piece Wt (g)
                            round($p->sale_price_per_piece ?? 0, 2),
                            round($p->wholesale_price ?? 0, 2),
                            round($p->purchase_price_per_piece ?? 0, 2),
                            $p->alert_quantity ?? 0, // Variant Alert
                            $p->barcode_path,        // Variant Barcode
                            '1',                     // Variant Is Base
                            $cartonStock,            // Carton Stock
                            $loosePiecesStock,       // Loose Pieces Stock
                            $liveTotalPieces,        // Total Pieces Stock
                        ]);
                    } else {
                        foreach ($variants as $idx => $v) {
                            $vUnit = $v['unit'] ?? ($sizeMode === 'by_kg' ? 'Kg' : ($sizeMode === 'by_gm' ? 'Gm' : ($sizeMode === 'by_cartons' ? 'Carton' : 'Pcs')));
                            $vFactor = isset($v['conv_factor']) ? $v['conv_factor'] : ($sizeMode === 'by_cartons' && strtolower($vUnit) === 'carton' ? $pcsPerBox : 1);
                            $vWeight = isset($v['weight_per_piece']) ? $v['weight_per_piece'] : (($vFactor > 0 && $vFactor < 10 && $sizeMode === 'by_kg') ? $vFactor * 1000 : 0);
                            $vIsBase = isset($v['is_base_variant']) ? (int)$v['is_base_variant'] : ($idx === 0 ? 1 : 0);
                            $vAlert  = $v['alert'] ?? ($p->alert_quantity ?? 0);

                            // For the base variant row, show the live calculated stock; for child variants show their relative/configured stock
                            $rowCartonStock = $vIsBase ? $cartonStock : 0;
                            $rowLooseStock  = $vIsBase ? $loosePiecesStock : 0;
                            $rowTotalStock  = $vIsBase ? $liveTotalPieces : ($v['stock'] ?? 0);

                            fputcsv($handle, [
                                $p->item_code,
                                $p->item_name,
                                $p->category_relation->name ?? '',
                                $p->sub_category_relation->name ?? '',
                                $p->brand->name ?? '',
                                $sizeMode,
                                $pcsPerBox,
                                $p->sale_discount_percent ?? 0,
                                $p->purchase_discount_percent ?? 0,
                                $p->is_active ? 1 : 0,
                                // Variant columns
                                $v['name'] ?? $p->item_name,
                                $v['size'] ?? '-',
                                $v['color'] ?? '-',
                                $vUnit,
                                $vFactor,
                                $vWeight,
                                round($v['sale_price'] ?? $p->sale_price_per_piece ?? 0, 2),
                                round($v['wholesale_price'] ?? $p->wholesale_price ?? 0, 2),
                                round($v['purch_price'] ?? $p->purchase_price_per_piece ?? 0, 2),
                                $vAlert,
                                $v['barcode'] ?? $p->barcode_path,
                                $vIsBase,
                                $rowCartonStock,
                                $rowLooseStock,
                                $rowTotalStock,
                            ]);
                        }
                    }
                }
            });

            fclose($handle);
        };

        $filename = 'products_export_' . now()->format('Y-m-d_H-i') . '.csv';

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    //  IMPORT STEP 1: VALIDATE & PREVIEW
    // ──────────────────────────────────────────────────────────
    public function importValidate(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|max:10240',
            'import_mode' => 'required|in:create,update_only',
        ]);

        $mode = $request->input('import_mode');
        $autoCreate = $request->has('auto_create');
        
        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        // Check and strip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $headerRow = fgetcsv($handle);
        if (!$headerRow) {
            return redirect()->back()->with('error', 'CSV file is empty or invalid.');
        }

        $headerMap = [];
        foreach ($headerRow as $i => $col) {
            $headerMap[strtolower(trim($col))] = $i;
        }
        
        // Flexible required columns matching
        $requiredCols = [
            'product_name'    => ['product name', 'product_name', 'item_name', 'item_name (*)'],
            'variant_name'    => ['variant name', 'variant_name', 'item_name (*)'],
            'sale_price'      => ['variant sale price', 'sale_price', 'variant_sale_price_per_piece', 'sale_price_per_piece'],
            'wholesale_price' => ['variant wholesale', 'variant wholesale price', 'wholesale_price'],
            'purch_price'     => ['variant purch price', 'variant purchase price', 'purchase_price', 'variant_purchase_price_per_piece', 'purchase_price_per_piece'],
        ];

        foreach ($requiredCols as $key => $possibleNames) {
            $found = false;
            foreach ($possibleNames as $pName) {
                if (isset($headerMap[$pName])) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return redirect()->back()->with('error', "Required column matching '{$possibleNames[0]}' not found in CSV. Please use the downloaded template.");
            }
        }

        $get = function (array $row, $possibleNames, $default = '') use ($headerMap) {
            if (!is_array($possibleNames)) $possibleNames = [$possibleNames];
            foreach ($possibleNames as $pName) {
                $key = strtolower(trim($pName));
                if (isset($headerMap[$key]) && isset($row[$headerMap[$key]])) {
                    return trim($row[$headerMap[$key]]);
                }
            }
            return $default;
        };

        $categories = Category::pluck('name', 'id')->map(function($name) { return strtolower($name); })->toArray();
        $subCategories = Subcategory::pluck('name', 'id')->map(function($name) { return strtolower($name); })->toArray();
        $brands = Brand::pluck('name', 'id')->map(function($name) { return strtolower($name); })->toArray();
        
        $productsByRef = [];
        $errors = [];
        $rowNum = 1;
        $barcodesSeen = [];
        
        $masterDataToCreate = [
            'categories' => [],
            'subcategories' => [],
            'brands' => []
        ];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (empty(array_filter($row))) continue;

            $prodRef = $get($row, ['item code', 'item_code', 'product reference', 'product_reference', 'id']);
            $prodName = $get($row, $requiredCols['product_name']);
            
            if (empty($prodName)) {
                $errors[] = ["row" => $rowNum, "msg" => "Product Name is required."];
                continue;
            }

            // CRITICAL FIX FOR MULTI-VARIANT PRODUCTS:
            // If Item Code is provided, use it as grouping key (e.g. ITEM-0001).
            // If Item Code is blank (new product), group by sanitized Product Name so multiple rows for the same product stay together!
            if (!empty($prodRef)) {
                $groupKey = 'CODE_' . strtoupper(trim($prodRef));
            } else {
                $groupKey = 'NAME_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', trim($prodName)));
            }

            $catName = $get($row, ['category']);
            $subCatName = $get($row, ['sub_category', 'sub category']);
            $brandName = $get($row, ['brand']);
            $vBarcode = $get($row, ['variant barcode', 'barcode']);
            
            if (!empty($vBarcode)) {
                if (isset($barcodesSeen[$vBarcode])) {
                    $errors[] = ["row" => $rowNum, "msg" => "Duplicate barcode '$vBarcode' found in CSV (Row ".$barcodesSeen[$vBarcode].")."];
                }
                $barcodesSeen[$vBarcode] = $rowNum;
            }
            
            // Check master data
            if (!empty($catName) && !in_array(strtolower($catName), $categories)) {
                if ($autoCreate) {
                    if (!in_array($catName, $masterDataToCreate['categories'])) {
                        $masterDataToCreate['categories'][] = $catName;
                    }
                } else {
                    $errors[] = ["row" => $rowNum, "msg" => "Category '$catName' does not exist."];
                }
            }
            
            if (!empty($brandName) && !in_array(strtolower($brandName), $brands)) {
                if ($autoCreate) {
                    if (!in_array($brandName, $masterDataToCreate['brands'])) {
                        $masterDataToCreate['brands'][] = $brandName;
                    }
                } else {
                    $errors[] = ["row" => $rowNum, "msg" => "Brand '$brandName' does not exist."];
                }
            }

            $pcsPerCarton = max(1, (int)$get($row, ['pcs per box', 'pieces per box', 'pcs_per_carton', 'pcs per carton'], 1));

            if (!isset($productsByRef[$groupKey])) {
                $rawSizeMode = strtolower(trim($get($row, ['size mode', 'size_mode', 'mode'], 'by_pieces')));
                $sizeMode = 'by_pieces';
                if (in_array($rawSizeMode, ['by_cartons', 'carton', 'cartons', 'box', 'boxes'])) {
                    $sizeMode = 'by_cartons';
                } elseif (in_array($rawSizeMode, ['by_kg', 'kg', 'kgs', 'kilogram', 'kilograms'])) {
                    $sizeMode = 'by_kg';
                } elseif (in_array($rawSizeMode, ['by_gm', 'gm', 'g', 'gram', 'grams'])) {
                    $sizeMode = 'by_gm';
                } elseif (in_array($rawSizeMode, ['by_feet', 'ft', 'feet'])) {
                    $sizeMode = 'by_feet';
                } elseif (in_array($rawSizeMode, ['by_meter', 'meter', 'mtr'])) {
                    $sizeMode = 'by_meter';
                }

                $productsByRef[$groupKey] = [
                    'group_key'      => $groupKey,
                    'raw_ref'        => $prodRef,
                    'name'           => $prodName,
                    'category'       => $catName,
                    'sub_category'   => $subCatName,
                    'brand'          => $brandName,
                    'size_mode'      => $sizeMode,
                    'pcs_per_carton' => $pcsPerCarton,
                    'sale_discount'  => max(0, (float)$get($row, ['sale discount %', 'sale_discount_%'], 0)),
                    'purch_discount' => max(0, (float)$get($row, ['purchase discount %', 'purchase_discount_%'], 0)),
                    'is_active'      => (int)$get($row, ['is_active', 'is active'], 1),
                    'variants'       => []
                ];
            } else {
                // Keep the max pieces per carton if variant defines it
                if ($pcsPerCarton > $productsByRef[$groupKey]['pcs_per_carton']) {
                    $productsByRef[$groupKey]['pcs_per_carton'] = $pcsPerCarton;
                }
            }

            // Parse Carton & Pieces Stock (Supports both new columns and legacy 'Variant Stock')
            $cartonStockRaw = $get($row, ['carton stock', 'carton_stock', 'boxes stock', 'boxes_stock', 'cartons', 'boxes'], '');
            $looseStockRaw  = $get($row, ['loose pieces stock', 'loose_pieces_stock', 'loose pieces', 'loose_pieces', 'loose'], '');
            $totalStockRaw  = $get($row, ['total pieces stock', 'total_pieces_stock', 'total pieces', 'total_stock', 'variant stock', 'variant_stock', 'stock'], '');

            $cartonStock = $cartonStockRaw !== '' ? (float)$cartonStockRaw : null;
            $looseStock  = $looseStockRaw !== '' ? (float)$looseStockRaw : null;
            $totalStock  = $totalStockRaw !== '' ? (float)$totalStockRaw : null;

            $currentPpb = $productsByRef[$groupKey]['pcs_per_carton'];
            $calculatedPieces = 0;

            if ($cartonStock !== null || $looseStock !== null) {
                $cVal = (float)($cartonStock ?? 0);
                $lVal = (float)($looseStock ?? 0);
                // If carton stock has decimals like 5.2 (5 cartons + 2 loose pieces)
                if (strpos((string)$cartonStockRaw, '.') !== false) {
                    $parts = explode('.', (string)$cartonStockRaw);
                    $cVal = (int)($parts[0] ?? 0);
                    $lVal += (int)($parts[1] ?? 0);
                }
                $calculatedPieces = ($cVal * $currentPpb) + $lVal;
            } elseif ($totalStock !== null) {
                $calculatedPieces = (float)$totalStock;
            }

            $vUnit = $get($row, ['variant unit', 'variant_unit', 'unit'], ($productsByRef[$groupKey]['size_mode'] === 'by_cartons' ? 'Carton' : ($productsByRef[$groupKey]['size_mode'] === 'by_kg' ? 'Kg' : 'Pcs')));
            $vPieceWt = (float) $get($row, ['variant piece wt (g)', 'variant piece weight (g)', 'variant_weight_per_piece', 'weight_per_piece', 'piece_weight'], 0);
            $rawConvFactor = $get($row, ['variant conv factor', 'variant_conv_factor', 'conv_factor', 'factor'], '');
            $vConvFactor = $rawConvFactor !== '' ? (float)$rawConvFactor : 0;
            $vIsBase = (int) $get($row, ['variant is base', 'variant_is_base', 'is_base'], 0);
            $vAlert  = (float) $get($row, ['variant alert', 'variant_alert_qty', 'alert'], 0);

            // Carton conversion factor logic
            if ($productsByRef[$groupKey]['size_mode'] === 'by_cartons') {
                if (strtolower($vUnit) === 'carton' || strtolower($vUnit) === 'box') {
                    if ($vConvFactor <= 0) $vConvFactor = $currentPpb;
                } else {
                    if ($vConvFactor <= 0) $vConvFactor = 1;
                }
            } elseif ($vPieceWt > 0 && ($rawConvFactor === '' || $vConvFactor <= 0 || $vConvFactor == 1)) {
                $vConvFactor = $vPieceWt / 1000.0;
            } elseif ($vPieceWt <= 0 && $vConvFactor > 0 && $vConvFactor < 1) {
                $vPieceWt = $vConvFactor * 1000.0;
            } elseif ($vConvFactor <= 0) {
                $vConvFactor = 1;
            }

            // Variant Data
            $productsByRef[$groupKey]['variants'][] = [
                'row'              => $rowNum,
                'name'             => $get($row, $requiredCols['variant_name'], $prodName),
                'size'             => $get($row, ['variant size', 'variant_size'], '-'),
                'color'            => $get($row, ['variant color', 'variant_color'], '-'),
                'unit'             => $vUnit,
                'conv_factor'      => $vConvFactor,
                'weight_per_piece' => $vPieceWt,
                'barcode'          => $vBarcode,
                'carton_stock'     => $cartonStock ?? ($currentPpb > 1 ? floor($calculatedPieces / $currentPpb) : 0),
                'loose_stock'      => $looseStock ?? ($currentPpb > 1 ? fmod($calculatedPieces, $currentPpb) : $calculatedPieces),
                'stock'            => max(0, $calculatedPieces),
                'sale_price'       => max(0, (float)$get($row, $requiredCols['sale_price'], 0)),
                'wholesale_price'  => max(0, (float)$get($row, $requiredCols['wholesale_price'], 0)),
                'purch_price'      => max(0, (float)$get($row, $requiredCols['purch_price'], 0)),
                'is_base_variant'  => $vIsBase,
                'alert'            => $vAlert,
            ];
        }

        fclose($handle);

        // Prepare Preview Summary without N+1 queries
        $existingCodes = Product::pluck('id', 'item_code')->toArray();
        $productsToCreate = 0;
        $productsToUpdate = 0;
        $variantsToCreate = 0;
        $variantsToUpdate = 0;
        $ignored = 0;
        
        $validPayload = [];

        foreach ($productsByRef as $groupKey => $pData) {
            $rawRef = $pData['raw_ref'];
            $isUpdate = (!empty($rawRef) && isset($existingCodes[$rawRef]));
            
            if ($isUpdate) {
                $existingProduct = Product::find($existingCodes[$rawRef]);
                $existingVariants = [];
                if (!empty($existingProduct->color)) {
                    $parsed = is_string($existingProduct->color) ? json_decode($existingProduct->color, true) : $existingProduct->color;
                    if (is_array($parsed)) $existingVariants = $parsed;
                }
                
                $newVCount = 0;
                $updVCount = 0;
                
                foreach ($pData['variants'] as $newV) {
                    $found = false;
                    foreach ($existingVariants as $eV) {
                        if (isset($eV['name']) && strcasecmp($eV['name'], $newV['name']) === 0) {
                            if (isset($eV['size']) && strcasecmp($eV['size'], $newV['size']) === 0) {
                                if (isset($eV['color']) && strcasecmp($eV['color'], $newV['color']) === 0) {
                                    $found = true;
                                    break;
                                }
                            }
                        }
                    }
                    if ($found) {
                        $updVCount++;
                    } else {
                        $newVCount++;
                    }
                }
                
                $productsToUpdate++;
                $variantsToUpdate += $updVCount;
                $variantsToCreate += $newVCount;
                $pData['is_update'] = true;
                $pData['matched_code'] = $rawRef;
                
            } else {
                if ($mode === 'update_only') {
                    $ignored += count($pData['variants']);
                    continue; // Skip new products in update_only mode
                }
                $productsToCreate++;
                $variantsToCreate += count($pData['variants']);
                $pData['is_update'] = false;
                $pData['matched_code'] = null;
            }
            
            $validPayload[$groupKey] = $pData;
        }

        // Store payload in session for confirmation
        Session::put('import_payload', [
            'mode'          => $mode,
            'auto_create'   => $autoCreate,
            'products'      => $validPayload,
            'master_data'   => $masterDataToCreate,
            'errors'        => $errors,
            'preview_stats' => [
                'products_create' => $productsToCreate,
                'products_update' => $productsToUpdate,
                'variants_create' => $variantsToCreate,
                'variants_update' => $variantsToUpdate,
                'ignored'         => $ignored,
                'master_create'   => count($masterDataToCreate['categories']) + count($masterDataToCreate['brands'])
            ]
        ]);

        return redirect()->route('products.import.preview');
    }
    
    // ──────────────────────────────────────────────────────────
    //  IMPORT STEP 2: SHOW PREVIEW
    // ──────────────────────────────────────────────────────────
    public function importPreview()
    {
        if (!Session::has('import_payload')) {
            return redirect()->route('product')->with('error', 'Import session expired or invalid.');
        }
        
        $payload = Session::get('import_payload');
        return view('admin_panel.product.import_preview', compact('payload'));
    }
    
    // ──────────────────────────────────────────────────────────
    //  IMPORT STEP 3: CONFIRM & IMPORT
    // ──────────────────────────────────────────────────────────
    public function importConfirm()
    {
        if (!Session::has('import_payload')) {
            return redirect()->route('product')->with('error', 'Import session expired.');
        }

        $payload = Session::get('import_payload');
        $productsToProcess = $payload['products'];
        $autoCreate = $payload['auto_create'];
        
        $createdProducts = 0;
        $updatedProducts = 0;
        $createdVariants = 0;
        $updatedVariants = 0;

        DB::beginTransaction();
        try {
            // 1. Auto Create Master Data
            $catMap = Category::pluck('id', 'name')->mapWithKeys(function ($item, $key) { return [strtolower($key) => $item]; })->toArray();
            $brandMap = Brand::pluck('id', 'name')->mapWithKeys(function ($item, $key) { return [strtolower($key) => $item]; })->toArray();
            $subCatMap = Subcategory::pluck('id', 'name')->mapWithKeys(function ($item, $key) { return [strtolower($key) => $item]; })->toArray();
            
            if ($autoCreate) {
                foreach ($payload['master_data']['categories'] as $catName) {
                    $key = strtolower($catName);
                    if (!isset($catMap[$key])) {
                        $c = Category::create(['name' => $catName]);
                        $catMap[$key] = $c->id;
                    }
                }
                foreach ($payload['master_data']['brands'] as $brandName) {
                    $key = strtolower($brandName);
                    if (!isset($brandMap[$key])) {
                        $b = Brand::create(['name' => $brandName]);
                        $brandMap[$key] = $b->id;
                    }
                }
            }

            // 2. Process Products
            foreach ($productsToProcess as $groupKey => $pData) {
                $rawRef = $pData['raw_ref'];
                $product = !empty($rawRef) ? Product::where('item_code', $rawRef)->first() : null;
                
                $cId = isset($catMap[strtolower($pData['category'])]) ? $catMap[strtolower($pData['category'])] : null;
                $bId = isset($brandMap[strtolower($pData['brand'])]) ? $brandMap[strtolower($pData['brand'])] : null;
                $sId = isset($subCatMap[strtolower($pData['sub_category'])]) ? $subCatMap[strtolower($pData['sub_category'])] : null;
                $ppb = max(1, (int)$pData['pcs_per_carton']);

                $finalVariants = [];

                if ($product) {
                    // Update Product
                    $existingVariants = [];
                    if (!empty($product->color)) {
                        $parsed = is_string($product->color) ? json_decode($product->color, true) : $product->color;
                        if (is_array($parsed)) $existingVariants = $parsed;
                    }
                    
                    if ($payload['mode'] === 'update_only') {
                        $finalVariants = []; // Wipe and replace with incoming CSV variants
                    } else {
                        $finalVariants = $existingVariants; // Preserve and merge
                    }
                    
                    foreach ($pData['variants'] as $newV) {
                        $foundIdx = -1;
                        $oldBarcode = '';
                        
                        foreach ($finalVariants as $idx => $eV) {
                            if (strcasecmp($eV['name'] ?? '', $newV['name']) === 0 && 
                                strcasecmp($eV['size'] ?? '', $newV['size']) === 0 && 
                                strcasecmp($eV['color'] ?? '', $newV['color']) === 0) {
                                $foundIdx = $idx;
                                $oldBarcode = $eV['barcode'] ?? '';
                                break;
                            }
                        }
                        
                        $vArr = [
                            'name'             => $newV['name'],
                            'size'             => $newV['size'],
                            'color'            => $newV['color'],
                            'unit'             => $newV['unit'] ?? ($pData['size_mode'] === 'by_cartons' ? 'Carton' : 'Pcs'),
                            'conv_factor'      => (float)($newV['conv_factor'] ?? ($pData['size_mode'] === 'by_cartons' ? $ppb : 1)),
                            'weight_per_piece' => (float)($newV['weight_per_piece'] ?? 0),
                            'stock'            => $newV['stock'],
                            'sale_price'       => $newV['sale_price'],
                            'wholesale_price'  => $newV['wholesale_price'] ?? 0,
                            'purch_price'      => $newV['purch_price'],
                            'barcode'          => $newV['barcode'] ?: $oldBarcode,
                            'is_base_variant'  => (int)($newV['is_base_variant'] ?? 0),
                            'alert'            => (float)($newV['alert'] ?? 0),
                        ];
                        
                        if ($foundIdx >= 0) {
                            $finalVariants[$foundIdx] = $vArr;
                            $updatedVariants++;
                        } else {
                            $finalVariants[] = $vArr;
                            $createdVariants++;
                        }
                    }
                    
                    // Calculate Total Stock in Pieces
                    $stockTotal = 0;
                    if ($pData['size_mode'] === 'by_cartons') {
                        // For carton mode: take the base variant stock (which importValidate already calculated in total pieces)
                        $baseV = collect($finalVariants)->firstWhere('is_base_variant', 1) ?? $finalVariants[0];
                        $stockTotal = (float)($baseV['stock'] ?? 0);
                    } elseif ($pData['size_mode'] === 'by_kg' || $pData['size_mode'] === 'by_gm') {
                        // For weight mode: base variant defines physical stock
                        $baseV = collect($finalVariants)->firstWhere('is_base_variant', 1) ?? $finalVariants[0];
                        $stockTotal = (float)($baseV['stock'] ?? 0);
                    } else {
                        // Piece based mode: sum pieces across variants
                        foreach ($finalVariants as $v) {
                            $stockTotal += (float)($v['stock'] ?? 0);
                        }
                    }
                    
                    // Pricing calculations for cartons vs pieces
                    $salePricePc = $finalVariants[0]['sale_price'] ?? 0;
                    $salePriceBox = $salePricePc;
                    $purchPricePc = $finalVariants[0]['purch_price'] ?? 0;
                    $purchPriceBox = $purchPricePc;

                    if ($pData['size_mode'] === 'by_cartons' && $ppb > 1) {
                        $salePriceBox = $salePricePc;
                        $salePricePc  = round($salePriceBox / $ppb, 4);
                        $purchPriceBox = $purchPricePc;
                        $purchPricePc  = round($purchPriceBox / $ppb, 4);
                    }

                    $boxesQty = $ppb > 1 ? floor($stockTotal / $ppb) : $stockTotal;
                    $loosePieces = $ppb > 1 ? fmod($stockTotal, $ppb) : 0;

                    $product->update([
                        'item_name'                 => $pData['name'],
                        'category_id'               => $cId,
                        'sub_category_id'           => $sId,
                        'brand_id'                  => $bId,
                        'size_mode'                 => $pData['size_mode'],
                        'pieces_per_box'            => $ppb,
                        'sale_discount_percent'     => $pData['sale_discount'],
                        'purchase_discount_percent' => $pData['purch_discount'],
                        'sale_price_per_piece'      => $salePricePc,
                        'sale_price_per_box'        => $salePriceBox,
                        'wholesale_price'           => $finalVariants[0]['wholesale_price'] ?? 0,
                        'purchase_price_per_piece'  => $purchPricePc,
                        'purchase_price_per_box'    => $purchPriceBox,
                        'boxes_quantity'            => $boxesQty,
                        'loose_pieces'              => $loosePieces,
                        'piece_quantity'            => $stockTotal,
                        'total_stock_qty'           => $stockTotal,
                        'color'                     => json_encode($finalVariants),
                        'is_active'                 => $pData['is_active'],
                    ]);
                    
                    // Consolidate duplicate WarehouseStock rows for Warehouse 1
                    $wsList = WarehouseStock::where('warehouse_id', 1)->where('product_id', $product->id)->get();
                    if ($wsList->count() > 1) {
                        $ws = $wsList->first();
                        WarehouseStock::where('warehouse_id', 1)
                            ->where('product_id', $product->id)
                            ->where('id', '!=', $ws->id)
                            ->delete();
                    } elseif ($wsList->count() === 1) {
                        $ws = $wsList->first();
                    } else {
                        $ws = new WarehouseStock([
                            'warehouse_id' => 1,
                            'product_id'   => $product->id,
                        ]);
                    }

                    $diff = $stockTotal - (float)($ws->total_pieces ?? 0);
                    $ws->total_pieces = $stockTotal;
                    $ws->quantity     = $boxesQty;
                    $ws->remarks      = 'Updated via CSV Import';
                    $ws->save();
                    
                    if ($diff != 0) {
                        StockMovement::create([
                            'product_id' => $product->id,
                            'type'       => 'adjustment',
                            'qty'        => $diff,
                            'ref_type'   => 'IMPORT',
                            'note'       => 'Stock adjusted via CSV import',
                        ]);
                    }
                    
                    $updatedProducts++;
                    
                } else {
                    // Create New Product
                    foreach ($pData['variants'] as $newV) {
                        $finalVariants[] = [
                            'name'             => $newV['name'],
                            'size'             => $newV['size'],
                            'color'            => $newV['color'],
                            'unit'             => $newV['unit'] ?? ($pData['size_mode'] === 'by_cartons' ? 'Carton' : 'Pcs'),
                            'conv_factor'      => (float)($newV['conv_factor'] ?? ($pData['size_mode'] === 'by_cartons' ? $ppb : 1)),
                            'weight_per_piece' => (float)($newV['weight_per_piece'] ?? 0),
                            'stock'            => $newV['stock'],
                            'sale_price'       => $newV['sale_price'],
                            'wholesale_price'  => $newV['wholesale_price'] ?? 0,
                            'purch_price'      => $newV['purch_price'],
                            'barcode'          => $newV['barcode'] ?: (string)rand(100000000000, 999999999999),
                            'is_base_variant'  => (int)($newV['is_base_variant'] ?? 0),
                            'alert'            => (float)($newV['alert'] ?? 0),
                        ];
                        $createdVariants++;
                    }
                    
                    // Stock calculations
                    $stockTotal = 0;
                    if ($pData['size_mode'] === 'by_cartons') {
                        $baseV = collect($finalVariants)->firstWhere('is_base_variant', 1) ?? $finalVariants[0];
                        $stockTotal = (float)($baseV['stock'] ?? 0);
                    } elseif ($pData['size_mode'] === 'by_kg' || $pData['size_mode'] === 'by_gm') {
                        $baseV = collect($finalVariants)->firstWhere('is_base_variant', 1) ?? $finalVariants[0];
                        $stockTotal = (float)($baseV['stock'] ?? 0);
                    } else {
                        foreach ($finalVariants as $v) {
                            $stockTotal += (float)($v['stock'] ?? 0);
                        }
                    }
                    
                    // Generate fresh ITEM-XXXX code
                    $lastProduct = Product::orderBy('id', 'desc')->first();
                    $actualRef = $lastProduct ? ('ITEM-'.str_pad($lastProduct->id + 1, 4, '0', STR_PAD_LEFT)) : 'ITEM-0001';
                    
                    $salePricePc = $finalVariants[0]['sale_price'] ?? 0;
                    $salePriceBox = $salePricePc;
                    $purchPricePc = $finalVariants[0]['purch_price'] ?? 0;
                    $purchPriceBox = $purchPricePc;

                    if ($pData['size_mode'] === 'by_cartons' && $ppb > 1) {
                        $salePriceBox = $salePricePc;
                        $salePricePc  = round($salePriceBox / $ppb, 4);
                        $purchPriceBox = $purchPricePc;
                        $purchPricePc  = round($purchPriceBox / $ppb, 4);
                    }

                    $boxesQty = $ppb > 1 ? floor($stockTotal / $ppb) : $stockTotal;
                    $loosePieces = $ppb > 1 ? fmod($stockTotal, $ppb) : 0;

                    $product = Product::create([
                        'creater_id'                => Auth::id(),
                        'item_code'                 => $actualRef,
                        'item_name'                 => $pData['name'],
                        'category_id'               => $cId,
                        'sub_category_id'           => $sId,
                        'brand_id'                  => $bId,
                        'size_mode'                 => $pData['size_mode'],
                        'pieces_per_box'            => $ppb,
                        'sale_discount_percent'     => $pData['sale_discount'],
                        'purchase_discount_percent' => $pData['purch_discount'],
                        'sale_price_per_piece'      => $salePricePc,
                        'sale_price_per_box'        => $salePriceBox,
                        'wholesale_price'           => $finalVariants[0]['wholesale_price'] ?? 0,
                        'purchase_price_per_piece'  => $purchPricePc,
                        'purchase_price_per_box'    => $purchPriceBox,
                        'boxes_quantity'            => $boxesQty,
                        'loose_pieces'              => $loosePieces,
                        'piece_quantity'            => $stockTotal,
                        'total_stock_qty'           => $stockTotal,
                        'color'                     => json_encode($finalVariants),
                        'is_active'                 => $pData['is_active'],
                        'is_part'                   => 0,
                        'is_assembled'              => 0,
                        'barcode_path'              => rand(100000000000, 999999999999),
                        'total_m2'                  => 0,
                        'price_per_m2'              => 0,
                        'purchase_price_per_m2'     => 0,
                        'pieces_per_m2'             => 0,
                        'height'                    => 0,
                        'width'                     => 0,
                    ]);
                    
                    WarehouseStock::create([
                        'warehouse_id' => 1,
                        'product_id'   => $product->id,
                        'quantity'     => $boxesQty,
                        'total_pieces' => $stockTotal,
                        'remarks'      => 'Initial Stock via Import',
                    ]);
                    
                    if ($stockTotal > 0) {
                        StockMovement::create([
                            'product_id' => $product->id,
                            'type'       => 'adjustment',
                            'qty'        => $stockTotal,
                            'ref_type'   => 'IMPORT',
                            'note'       => 'Initial Stock via CSV import',
                        ]);
                    }
                    
                    $createdProducts++;
                }
            }

            DB::commit();
            Session::forget('import_payload');
            
            return redirect()->route('product')->with('success', "Import completed successfully! {$createdProducts} products created, {$updatedProducts} products updated ({$createdVariants} variants created, {$updatedVariants} variants updated).");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('product')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────
    //  Helper – canonical CSV column headers
    // ──────────────────────────────────────────────────────────
    private function csvHeaders(): array
    {
        return [
            'Item Code',            // Leave BLANK for new products. Enter exact code only to UPDATE.
            'Product Name',         // Required: Item name
            'Category',             // Category name (auto-created if enabled)
            'Sub Category',         // Subcategory name
            'Brand',                // Brand name (auto-created if enabled)
            'Size Mode',            // by_cartons / by_pieces / by_kg / by_gm / by_feet / by_meter
            'Pieces Per Box',       // Number of pieces per carton/box (e.g. 12)
            'Sale Discount %',      // Discount percent (e.g. 0)
            'Purchase Discount %',  // Purchase discount percent (e.g. 0)
            'Is Active',            // 1 = Active, 0 = Inactive
            'Variant Name',         // Required: Variant display name
            'Variant Size',         // Size e.g. M, L, 30x60, or '-'
            'Variant Color',        // Color e.g. Blue, Red, or '-'
            'Variant Unit',         // Carton / Pcs / Kg / Gm / Ft / Meter
            'Variant Conv Factor',  // Conversion factor (Pieces per carton or weight multiplier)
            'Variant Piece Wt (g)', // Weight per piece in grams (optional)
            'Variant Sale Price',   // Required: Sale price (per carton if unit is carton, or per piece)
            'Variant Wholesale',    // Wholesale price
            'Variant Purch Price',  // Required: Purchase price
            'Variant Alert',        // Low stock alert quantity
            'Variant Barcode',      // Barcode (optional, auto-generated if blank)
            'Variant Is Base',      // 1 for base variant row, 0 for child variant
            'Carton Stock',         // Stock in cartons/boxes (e.g. 10)
            'Loose Pieces Stock',   // Loose pieces stock (e.g. 2)
            'Total Pieces Stock',   // Total pieces stock (or leave blank if Carton + Loose provided)
        ];
    }
}
