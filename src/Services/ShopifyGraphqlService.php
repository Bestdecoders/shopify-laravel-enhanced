<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ShopifyGraphqlService
{
    public function execute($shop, $query, $payload = [])
    {
        try {

            $shopModel = $this->getShop($shop);

            if (!$shopModel) {
                throw new Exception("No user available.");
            }

            $response = $shopModel->api()->graph($query, $payload);

            $this->validateResponse($response, $query);

            return $response['body']['data'];
        } catch (Exception $e) {
            Log::error("GraphQL execution error: " . $e->getMessage());
            throw $e;
        }
    }

    protected function getShop($shop)
    {


        if (Auth::check()) {
            return Auth::user();
        } elseif ($shop) {
            $userModel = config('shopify-enhanced.user_model');
            if (!class_exists($userModel)) {
                throw new \Exception("The user model class '{$userModel}' does not exist.");
            }
            return $userModel::where('name', $shop)->first();
        }
    }


    protected function validateResponse($response, $query)
    {
        if (!isset($response['body']['data'])) {
            $this->logErrors($response, 'Response Error: ', $query);
        }

        $data = $response['body']['data'];
        $key = key($data);

        if (!empty($data[$key]['userErrors'])) {
            $this->logErrors($data[$key]['userErrors'], 'User Error: ', $query);
        }

        if (!empty($data[$key]['mediaUserErrors'])) {
            $this->logErrors($data[$key]['mediaUserErrors'], 'Media User Error: ', $query);
        }
    }

    protected function logErrors($errors, $prefix, $query)
    {
        Log::error($prefix . json_encode($errors));
        Log::info('Error Source: ' . $query);

        throw new Exception(json_encode($errors));
    }
}
