<?php

namespace App\Services;

use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    protected string $endpoint;

    protected string $token;

    protected string $collectionId;

    public function __construct()
    {
        $store = config('services.shopify.store_url');

        $this->endpoint =
            "https://{$store}/admin/api/2025-10/graphql.json";

        $this->token =
            config('services.shopify.access_token');

        $this->collectionId =
            config('services.shopify.collection_id');
    }

    protected function graphql(
        string $query,
        array $variables = []
    ): array {

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->post($this->endpoint, [
            'query' => $query,
            'variables' => $variables,
        ]);

        if (!$response->successful()) {

            throw new Exception(
                'Shopify API request failed.'
            );
        }

        $json = $response->json();

        if (isset($json['errors'])) {

            throw new Exception(
                json_encode($json['errors'])
            );
        }

        return $json;
    }

    public function findProductBySku(
        string $sku
    ): ?string {

        if (empty($sku)) {
            return null;
        }

        $query  =   <<<'GRAPHQL'
                    query ProductSearch($query: String!) {
                    products(first: 1, query: $query) {
                        nodes {
                        id
                        title
                        }
                    }
                    }
                    GRAPHQL;

        $result = $this->graphql(
            $query,
            [
                'query' => "sku:$sku"
            ]
        );

        return
            $result['data']['products']['nodes'][0]['id']
            ?? null;
    }

    public function createProduct(
        Product $product
    ): array {

        $mutation = <<<'GRAPHQL'
            mutation ProductCreate($input: ProductInput!) {

            productCreate(input: $input) {

                product {
                id
                title
                }

                userErrors {
                field
                message
                }
            }
            }
            GRAPHQL;

        $variables = [

            'input' => [

                'title' =>
                    $product->title,

                'descriptionHtml' =>
                    $product->body_html,

                'vendor' =>
                    $product->vendor,

                'productType' =>
                    $product->product_type,

                'tags' =>
                    $product->tags
                        ? explode(',', $product->tags)
                        : [],
            ]
        ];

        $response = $this->graphql(
            $mutation,
            $variables
        );

        $errors =
            $response['data']
            ['productCreate']
            ['userErrors'];

        if (!empty($errors)) {

            throw new Exception(
                json_encode($errors)
            );
        }

        return [
            'product_id' =>
                $response['data']
                ['productCreate']
                ['product']['id'],

            'response' => $response,

            'request' => $variables,

            'action' => 'create',
        ];
    }

    public function updateProduct(
        string $shopifyProductId,
        Product $product
    ): array {

        $mutation   =   <<<'GRAPHQL'
                        mutation ProductUpdate($input: ProductInput!) {

                        productUpdate(input: $input) {

                            product {
                            id
                            title
                            }

                            userErrors {
                            field
                            message
                            }
                        }
                        }
                    GRAPHQL;

        $variables = [

            'input' => [

                'id' =>
                    $shopifyProductId,

                'title' =>
                    $product->title,

                'descriptionHtml' =>
                    $product->body_html,

                'vendor' =>
                    $product->vendor,

                'productType' =>
                    $product->product_type,
            ]
        ];

        $response = $this->graphql(
            $mutation,
            $variables
        );

        $errors =
            $response['data']
            ['productUpdate']
            ['userErrors'];

        if (!empty($errors)) {

            throw new Exception(
                json_encode($errors)
            );
        }

        return [

            'product_id' =>
                $shopifyProductId,

            'response' =>
                $response,

            'request' =>
                $variables,

            'action' =>
                'update',
        ];
    }

    public function addToCollection(
        string $productId
    ): void {

        $mutation   =   <<<'GRAPHQL'
                        mutation AddToCollection(
                        $id: ID!,
                        $productIds: [ID!]!
                        ) {

                        collectionAddProducts(
                            id: $id,
                            productIds: $productIds
                        ) {

                            userErrors {
                            field
                            message
                            }
                        }
                        }
                        GRAPHQL;

        $this->graphql(
            $mutation,
            [
                'id' =>
                    'gid://shopify/Collection/' .
                    $this->collectionId,

                'productIds' => [
                    $productId
                ]
            ]
        );
    }

    public function createOrUpdateProduct(
        Product $product
    ): array {

        $existingId = null;

        if ($product->sku) {

            $existingId =
                $this->findProductBySku(
                    $product->sku
                );
        }

        if ($existingId) {

            $result =
                $this->updateProduct(
                    $existingId,
                    $product
                );

        } else {

            $result =
                $this->createProduct(
                    $product
                );
        }

        $this->addToCollection(
            $result['product_id']
        );

        Log::info(
            'Shopify Product Imported',
            [
                'product_id' =>
                    $result['product_id']
            ]
        );

        return [

            'product_id' =>
                $result['product_id'],

            'variant_id' =>
                null,

            'action' =>
                $result['action'],

            'request' =>
                $result['request'],

            'response' =>
                $result['response'],
        ];
    }
}