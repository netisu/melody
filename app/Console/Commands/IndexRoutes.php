<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MeiliSearch\Client;
use MeiliSearch\Exceptions\ApiException;

class IndexRoutes extends Command
{
    protected $signature = 'search:index-routes';
    protected $description = 'Indexes predefined application routes into Meilisearch.';

    public function handle()
    {
        $meilisearchHost = config('scout.meilisearch.host');
        $meilisearchKey = config('scout.meilisearch.key');
        $indexName = 'routes';

        $this->info("Attempting to connect to Meilisearch at: " . $meilisearchHost);
        $this->info("Using Meilisearch Key (first 5 chars): " . substr($meilisearchKey, 0, 5) . '...');

        try {
            $client = new Client($meilisearchHost, $meilisearchKey);

            // Test Meilisearch connection
            $health = $client->health();
            if ($health['status'] !== 'available') {
                $this->error("Meilisearch is not healthy. Status: " . $health['status']);
                return 1;
            }
            $this->info("Meilisearch connection successful. Status: " . $health['status']);
            $this->info("Meilisearch version: " . $client->version()['pkgVersion']);


            $routes = config('search_routes');
            if (empty($routes)) {
                $this->warn("No routes found in config/search_routes.php. Please ensure the file exists and contains data.");
                return 0;
            }

            $formattedRoutes = [];
            foreach ($routes as $route) {
                try {
                    $url = eval('return ' . $route['url'] . ';');
                } catch (\Throwable $e) {
                    $this->error("Error evaluating route URL for '{$route['name']}': " . $e->getMessage());
                    $this->error("Problematic URL string: " . $route['url']);
                    continue;
                }

                if (!$url) {
                    $this->warn("Route URL for '{$route['name']}' evaluated to null or empty. Skipping.");
                    continue;
                }

                $formattedRoutes[] = [
                    'id' => \Illuminate\Support\Str::slug($route['name']),
                    'name' => $route['name'],
                    'keywords' => $route['keywords'],
                    'url' => $url,
                    'description' => $route['description'] ?? null,
                    'type' => $route['type'],
                    'icon' => $route['icon'] ?? null,
                ];
            }

            if (empty($formattedRoutes)) {
                $this->error("No valid routes to index after formatting.");
                return 1;
            }

            $this->info("Attempting to index " . count($formattedRoutes) . " routes into the '" . $indexName . "' index.");

            $index = null;
            try {
                $index = $client->getIndex($indexName);
                $this->info("Index '" . $indexName . "' already exists.");
            } catch (ApiException $e) {
                if ($e->getCode() === 404) { // Index not found
                    $this->info("Index '" . $indexName . "' not found. Creating it...");
                    $index = $client->createIndex($indexName, ['primaryKey' => 'id']);
                    $this->info("Index '" . $indexName . "' created successfully.");
                } else {
                    $this->error("Error checking or creating index '" . $indexName . "': " . $e->getMessage());
                    return 1;
                }
            }

            // Update settings
            $this->info("Updating settings for index '" . $indexName . "'...");
            $index->updateSettings([
                'searchableAttributes' => ['name', 'keywords', 'description'],
                'filterableAttributes' => ['type'],
            ]);
            $this->info("Index settings updated.");

            // Add documents
            $task = $index->addDocuments($formattedRoutes);
            $this->info("Documents added to index. Waiting for task to complete (task ID: " . $task['taskUid'] . ")...");

            // Wait for the indexing task to complete
            $client->index($indexName)->waitForTask($task['taskUid'], 500, 60000); // Poll every 500ms, timeout after 60s
            $this->info("Indexing task completed. Status: " . $client->getTask($task['taskUid'])['status']);


            $this->info('Routes indexed successfully!');
            return false;
        } catch (ApiException $e) {
            $this->error("Meilisearch API Error: " . $e->getMessage());
            $this->error("Status Code: " . $e->getCode());
            return true;
        } catch (\Exception $e) {
            $this->error("An unexpected error occurred: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . ", Line: " . $e->getLine());
            return true;
        }
    }
}
