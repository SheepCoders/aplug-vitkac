<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RunScraper extends Command
{
    protected $signature = 'scrape:vitkac';
    protected $description = 'Run the scraper for scraping products from Vitkac website';

    public function handle()
    {
        $this->info('Running the Python scraper...');

        $process = new Process(['/var/www/venv/bin/python3', '/var/www/python-scripts/scrape_vitkac.py']);
        $process->setTimeout(3000);

        try {
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    $this->error($buffer);
                } else {
                    $this->info($buffer);
                }
            });

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $this->info('Scraping completed!');
        } catch (ProcessFailedException $exception) {
            $this->error('Scraping failed: ' . $exception->getMessage());
        } catch (\Symfony\Component\Process\Exception\ProcessTimedOutException $exception) {
            $this->error('Scraping process timed out: ' . $exception->getMessage());
        }
    }
}
