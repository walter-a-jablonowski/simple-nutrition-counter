<?php

/**
 * Find food price data from rewe.de based on a product search
 * 
 * @param string $searchString The product search string
 * @return array Result array with status and price information
 */
function findRewePrice( $searchString )
{
  // Encode the search string for URL
  $encodedSearch = urlencode($searchString);
  $url = "https://shop.rewe.de/productList?search={$encodedSearch}";
  
  // Initialize cURL session
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
  
  // Execute cURL session
  $response = curl_exec($ch);
  
  // Check for cURL errors
  if( curl_errno($ch) )
  {
    curl_close($ch);
    return ['error', 'network error: ' . curl_error($ch)];
  }
  
  // Close cURL session
  curl_close($ch);
  
  // Check if we have a valid response
  if( ! $response )
    return ['error', 'empty response'];
  
  // Check for HTML structure changes
  // Look for key elements that should be present in the REWE product list page
  if( ! strpos($response, 'search-service-product') && ! strpos($response, 'productPrice') )
    return ['error', 'html structure changed'];
  
  // Check if we have product results
  if( strpos($response, 'Keine Ergebnisse gefunden') !== false || 
      strpos($response, 'No results found') !== false )
    return ['missing'];
  
  // Count product entries to ensure we have exactly one match
  $productCount = preg_match_all('/<div[^>]*class="[^"]*search-service-product[^"]*"[^>]*>/i', $response, $matches);
  
  if( $productCount === false )
    return ['error', 'regex error'];
  
  if( $productCount > 1 )
    return ['error', 'multiple articles'];
  
  if( $productCount === 0 )
    return ['missing'];
  
  // We have exactly one product, extract the price
  $dealPrice = false;
  $price = null;
  
  // First check for deal price
  if( preg_match('/<div[^>]*class="[^"]*productOfferPrice[^"]*"[^>]*>([^<]*)<\/div>/i', $response, $matches) )
  {
    $dealPrice = true;
    $priceText = trim($matches[1]);
    // Extract numeric price from format like "1,29 €"
    $priceText = str_replace(',', '.', $priceText); // Convert comma to decimal point
    $priceText = preg_replace('/[^0-9.]/', '', $priceText); // Remove non-numeric characters
    $price = floatval($priceText);
  }
  // If no deal price, check for regular price
  elseif( preg_match('/<div[^>]*class="[^"]*productPrice[^"]*"[^>]*>([^<]*)<\/div>/i', $response, $matches) )
  {
    $priceText = trim($matches[1]);
    // Extract numeric price from format like "1,29 €"
    $priceText = str_replace(',', '.', $priceText); // Convert comma to decimal point
    $priceText = preg_replace('/[^0-9.]/', '', $priceText); // Remove non-numeric characters
    $price = floatval($priceText);
  }
  else
    return ['error', 'price missing'];
  
  // Return success with price and deal status
  return ['success', $price, $dealPrice];
}
