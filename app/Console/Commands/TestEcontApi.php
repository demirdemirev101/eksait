<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestEcontApi extends Command
{
    protected $signature = 'test:econt-api';
    protected $description = 'Test the configured Econt API connection';

    public function handle(): int
    {
        $baseUrl = rtrim((string) config('services.econt.base_url'), '/');
        $username = (string) config('services.econt.username');
        $password = (string) config('services.econt.password');
        $verifySsl = (bool) config('services.econt.verify_ssl', true);

        if ($baseUrl === '' || $username === '' || $password === '') {
            $this->error('Missing Econt configuration. Check ECONT_BASE_URL, ECONT_USERNAME, and ECONT_PASSWORD.');

            return self::FAILURE;
        }

        $this->info('Testing configured Econt API connection...');
        $this->info("URL: {$baseUrl}");
        $this->info('Credentials: configured');
        $this->newLine();

        $client = Http::withOptions([
                'verify' => $verifySsl,
                'debug' => false,
            ])
            ->timeout(30)
            ->withBasicAuth($username, $password)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);

        $this->info('Test 1: getCities...');
        $response = $client->post($baseUrl . '/Nomenclatures/NomenclaturesService.getCities.json', [
            'countryCode' => 'BGR',
            'name' => 'Sofia',
        ]);

        $this->info('Status Code: ' . $response->status());

        if (! $response->successful()) {
            $this->error('FAILED');
            $this->error('Response: ' . $response->body());

            return self::FAILURE;
        }

        $this->info('SUCCESS');
        $this->info(json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->info('Test 2: getOffices for Sofia...');

        $response = $client->post($baseUrl . '/Nomenclatures/NomenclaturesService.getOffices.json', [
            'countryCode' => 'BGR',
            'cityId' => 68134,
        ]);

        $this->info('Status: ' . $response->status());

        if (! $response->successful()) {
            $this->error('FAILED');
            $this->error('Response: ' . $response->body());

            return self::FAILURE;
        }

        $offices = $response->json('offices', []);
        $this->info('Found ' . count($offices) . ' offices');

        foreach (array_slice($offices, 0, 3) as $office) {
            $this->line('  - ' . ($office['name'] ?? 'N/A') . ' (' . ($office['code'] ?? 'N/A') . ')');
        }

        return self::SUCCESS;
    }
}
