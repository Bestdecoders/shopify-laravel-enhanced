<?php 

if (!function_exists('debug_log')) {
    function debug_log($message, array $context = []) {
        if (config('app.debug')) {
            info($message, $context);
        }
    }
  }


function getProductById($shop, $Id)
{
  $graphQuery =  config('shopify-enhanced.queries.product.by_id');;
  $response = doRequestGraphQL($shop, $graphQuery, [
    'id' => $Id
  ]);
  return $response['product'];
}


function getCollections($shop, $query = [], $direction = 'next', $cursor = null, $pageSize = 100)
{
  // Convert the query array to a string (if it's an array)
  $queryString = is_array($query) ? implode(' ', $query) : $query;

  // Fetch the collections queries from the config
  $collectionsQueries = config('shopify-enhanced.queries.collections');

  // Get the required query from the collections array (default to the first)
  $baseQuery = $collectionsQueries[0]; // Modify index logic as needed

  // Determine keywords based on the direction
  $keyword = $direction === 'prev' ? 'last' : 'first';
  $secondKeyword = $direction === 'prev' ? 'before' : 'after';

  // Replace placeholders in the base query
  $graphQuery = str_replace(['{{KEYWORD}}', '{{SECOND_KEYWORD}}'], [$keyword, $secondKeyword], $baseQuery);

  // Make the GraphQL request
  $response = doRequestGraphQL($shop, $graphQuery, [
    'pageSize' => $pageSize,
    'query' => $queryString,
    'cursor' => $cursor,
  ], 'collections');

  return $response['collections'];
}