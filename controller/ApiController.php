<?php

class ApiController {
    
    public function getApiConfig() {
        // Set content type to JSON
        header('Content-Type: application/json');
        
        // Check if API key is set
        $apiKey = getenv('API_KEY');
        if (!$apiKey) {
            http_response_code(500);
            echo json_encode(['error' => 'API configuration not found']);
            exit;
        }
        
        // Return only the necessary API data
        $apiConfig = [
            'apiKey' => $apiKey,
            'imageBaseUrl' => getenv('IMAGE_BASE_URL'),
            'movieBaseUrl' => getenv('MOVIE_BASE_URL'),
            'tvBaseUrl' => getenv('TV_BASE_URL'),
            'searchBaseUrl' => getenv('SEARCH_BASE_URL'),
            'trendingMovieUrl' => getenv('TRENDING_MOVIE_URL'),
            'trendingTvUrl' => getenv('TRENDING_TV_URL')
        ];
        
        echo json_encode($apiConfig);
    }
} 