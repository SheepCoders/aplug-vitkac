<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected $csvDirectory = 'products';

    public function index(Request $request)
    {
        $filters = $this->getAvailableFilters($request);

        $products = collect($this->getProductsFromCSV($request, $filters));

        $perPage = 20;
        $page = request()->get('page', 1);
        $paginatedProducts = new \Illuminate\Pagination\LengthAwarePaginator(
            $products->forPage($page, $perPage),
            $products->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('products.index', [
            'products' => $paginatedProducts,
            'filters' => $filters,
        ]);
    }

    public function sendToShopify($productId)
    {
        $products = $this->getProductsFromCSV();
        $product = $products[$productId];

        $this->sendProductToShopify($product);

        return redirect()->route('products.index')->with('success', 'Product sent to Shopify!');
    }

    private function getAvailableFilters(Request $request = null)
    {
        $csvFiles = Storage::files($this->csvDirectory);

        $genderOptions = [];
        $categoryOptions = [];

        foreach ($csvFiles as $csvFile) {
            $category = $this->extractCategoryFromFilename($csvFile);
            $gender = $this->extractgenderFromFilename($csvFile);

            if (!in_array($gender, $genderOptions)) {
                $genderOptions[] = $gender;
            }

            if ($request && $request->has('gender') && $request->gender !== $gender) {
                continue;
            }

            if (!in_array($category, $categoryOptions)) {
                $categoryOptions[] = $category;
            }
        }

        return ['gender' => $genderOptions, 'category' => $categoryOptions];
    }

    private function getProductsFromCSV(Request $request = null, $filters = [])
    {
        $csvFiles = Storage::files($this->csvDirectory);

        $products = [];

        foreach ($csvFiles as $csvFile) {
            $category = $this->extractCategoryFromFilename($csvFile);
            $gender = $this->extractgenderFromFilename($csvFile);

            if ($request && $request->has('gender') && $request->gender != $gender) {
                continue;
            }

            if ($request && $request->has('category') && $request->category != $category) {
                continue;
            }

            $csvData = Storage::get($csvFile);

            $rows = array_map('str_getcsv', explode("\n", $csvData));

            $headers = array_shift($rows);

            foreach ($rows as $row) {
                if (empty($row) || count($row) !== count($headers)) {
                    continue;
                }

                $product = array_combine($headers, $row);
                $product['category'] = $category;
                $product['gender'] = $gender;
                $product['images'] = json_decode(str_replace("'", '"', $product['additional_images']), true);

                if ($request && $request->filled('search')) {
                    $search = strtolower($request->search);
                    if (strpos(strtolower($product['name']), $search) === false) {
                        continue;
                    }
                }

                if (!is_array($product['images'])) {
                    $product['images'] = [];
                }
                $products[] = $product;

            }
        }

        return $products;
    }

    public function getCategories(Request $request)
    {
        $gender = $request->input('gender');
        $filters = $this->getAvailableFilters($request);

        return response()->json($filters['category']);
    }

    private function extractCategoryFromFilename($filename)
    {
        preg_match('/_category_(\d+)\.csv/', basename($filename), $matches);
        return $matches[1] ?? '';
    }

    private function extractgenderFromFilename($filename)
    {
        preg_match('/^([a-zA-Z]+)/', basename($filename), $matches);
        return $matches[1] ?? '';
    }

    private function sendProductToShopify($product)
    {
        $client = new \GuzzleHttp\Client();

        $shopifyUrl = "https://your-shop.myshopify.com/admin/api/2021-01/products.json";
        $accessToken = 'your_shopify_api_access_token';

        $data = [
            'product' => [
                'title' => $product['name'],
                'body_html' => $product['detailed_description'],
                'vendor' => 'Vendor Name',
                'product_type' => $product['category'],
                'variants' => [
                    [
                        'price' => $product['price'],
                    ]
                ],
                'images' => array_map(function ($image) {
                    return ['src' => $image];
                }, json_decode($product['additional_images'], true)),
            ]
        ];

        $client->post($shopifyUrl, [
            'json' => $data,
            'headers' => [
                'X-Shopify-Access-Token' => $accessToken,
            ]
        ]);
    }
}
