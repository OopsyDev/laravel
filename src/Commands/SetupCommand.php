<?php

declare(strict_types=1);

namespace Oopsy\Laravel\Commands;

use Illuminate\Console\Command;
use Oopsy\Laravel\Dsn;
use Oopsy\Laravel\OopsyClient;

class SetupCommand extends Command
{
    protected $signature = 'oopsy:setup {dsn? : The Oopsy DSN string}
                            {--dsn= : The Oopsy DSN string (alternative to argument)}';

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

        // Resolve DSN: argument > option > existing env > prompt
        $dsn = $this->argument('dsn')
            ?? $this->option('dsn')
            ?? $this->ask('Enter your Oopsy DSN (found in your project settings)');

        if (! $dsn) {
            $this->components->error('No DSN provided. Run this command again with your DSN.');

            return self::FAILURE;
        }

        // Write DSN to .env
        $this->writeDsnToEnv($dsn);
        $this->components->info('DSN written to .env');

        // Clear cached config so the new DSN takes effect
        $this->callSilently('config:clear');

        // Update runtime config + rebuild the client singleton
        config(['oopsy.dsn' => $dsn]);
        $this->laravel->instance(OopsyClient::class, new OopsyClient(Dsn::parse($dsn)));

        // Send test event
        $this->newLine();
        if ($this->confirm('Send a test exception to verify the integration?', true)) {
            $this->call('oopsy:test');
        }

        return self::SUCCESS;
    }

    private function writeDsnToEnv(string $dsn): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            file_put_contents($envPath, "OOPSY_DSN={$dsn}\n");

            return;
        }

        $contents = file_get_contents($envPath);

        if (preg_match('/^OOPSY_DSN=.*/m', $contents)) {
            $contents = preg_replace('/^OOPSY_DSN=.*/m', "OOPSY_DSN={$dsn}", $contents);
        } else {
            $contents = rtrim($contents, "\n") . "\n\nOOPSY_DSN={$dsn}\n";
        }

        file_put_contents($envPath, $contents);
    }
}
