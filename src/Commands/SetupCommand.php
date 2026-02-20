<?php

declare(strict_types=1);

namespace Oopsy\Laravel\Commands;

use Illuminate\Console\Command;
use Oopsy\Laravel\OopsyClient;

class SetupCommand extends Command
{
    protected $signature = 'oopsy:setup {key? : The Oopsy project key}
                            {--key= : The Oopsy project key (alternative to argument)}
                            {--url= : The Oopsy instance URL (defaults to https://oopsy.dev)}';

    protected $description = 'Configure the Oopsy SDK for this Laravel application';

    public function handle(): int
    {
        $this->info('Setting up Oopsy error monitoring...');
        $this->newLine();

        // Publish config if not already present
        if (! file_exists(config_path('oopsy.php'))) {
            $this->components->task('Publishing config', function () {
                $this->callSilently('vendor:publish', ['--tag' => 'oopsy-config']);
            });
        } else {
            $this->components->info('Config file already exists.');
        }

        // Resolve key: argument > option > prompt
        $key = $this->argument('key')
            ?? $this->option('key')
            ?? $this->ask('Enter your Oopsy project key (found in your project settings)');

        if (! $key) {
            $this->components->error('No key provided. Run this command again with your project key.');

            return self::FAILURE;
        }

        $url = $this->option('url') ?? 'https://oopsy.dev';

        // Write to .env
        $this->writeEnvVar('OOPSY_KEY', $key);
        $this->components->info('OOPSY_KEY written to .env');

        if ($url !== 'https://oopsy.dev') {
            $this->writeEnvVar('OOPSY_URL', $url);
            $this->components->info('OOPSY_URL written to .env');
        }

        // Clear cached config so the new values take effect
        $this->callSilently('config:clear');

        // Update runtime config + rebuild the client singleton
        config(['oopsy.key' => $key, 'oopsy.url' => $url]);
        $this->laravel->instance(OopsyClient::class, new OopsyClient($key, $url));

        // Send test event
        $this->newLine();
        if ($this->confirm('Send a test exception to verify the integration?', true)) {
            $this->call('oopsy:test');
        }

        return self::SUCCESS;
    }

    private function writeEnvVar(string $name, string $value): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            file_put_contents($envPath, "{$name}={$value}\n");

            return;
        }

        $contents = file_get_contents($envPath);

        if (preg_match("/^{$name}=.*/m", $contents)) {
            $contents = preg_replace("/^{$name}=.*/m", "{$name}={$value}", $contents);
        } else {
            $contents = rtrim($contents, "\n") . "\n\n{$name}={$value}\n";
        }

        file_put_contents($envPath, $contents);
    }
}
