<?php

namespace Eternium\Command;

use Eternium\Event\Event;
use Eternium\Event\Leaderboard;
use Eternium\Utils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchCommand extends Command
{
    protected static $defaultName = 'fetch';

    private HttpClientInterface $client;

    /**
     * @var array<int, Event>
     */
    private array $events;

    public function __construct(Event ...$events)
    {
        $this->events = $events;
        $this->client = Utils::createHttpClient();

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Fetches leaderboards from game server');
        $this->addArgument('prefix', InputArgument::OPTIONAL, 'The leaderboards prefix', '');
        $this->addOption('update', 'u', InputOption::VALUE_NONE, 'Update existing data');
        $this->addOption('no-progress', '', InputOption::VALUE_NONE, 'Do not output download progress');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $prefix = $input->getArgument('prefix');
        $update = $input->getOption('update');
        $progressOutput = $input->getOption('no-progress') ? new NullOutput() : $output;

        $fetcher = $this->createFetcher($prefix, $update, $output, $progressOutput);
        foreach ($this->events as $event) {
            $event->walk($fetcher);
        }
        $fetcher->send(null);

        return self::SUCCESS;
    }

    private function createFetcher(string $prefix, bool $update, OutputInterface $output, OutputInterface $progressOutput): \Generator
    {
        $formatter = $this->getHelper('formatter');

        while (is_array($chain = yield)) {
            $event = $chain[0];
            if (!($event instanceof Leaderboard)) {
                continue;
            }

            $name = join('.', array_reverse($chain));
            if (!str_starts_with($name, $prefix)) {
                $output->writeln(
                    $formatter->formatSection('SKIP', "{$name} not matched prefix", 'comment'),
                    Output::VERBOSITY_VERBOSE
                );

                continue;
            }

            $file = ETERNIUM_DATA_PATH."{$name}.csv";
            if (!$update && is_file($file)) {
                $output->writeln(
                    $formatter->formatSection('SKIP', "{$name} entries already dumped", 'comment'),
                    Output::VERBOSITY_VERBOSE
                );

                continue;
            }

            $output->writeln($formatter->formatSection('DUMP', "fetching {$name} entries..."));

            $reader = Utils::createLeaderboardReader($this->client, $event->getId());
            $writer = Utils::createCsvWriter($file);

            try {
                $progressBar = new ProgressBar($progressOutput);
                $progressBar->setFormat($formatter->formatSection('DUMP', '%current% [%bar%] %elapsed%'));
                foreach ($progressBar->iterate($reader) as $entry) {
                    $writer->send($entry);
                }
            } finally {
                $progressBar->clear();
            }
            $writer->send(null);

            $output->writeln($formatter->formatSection('DUMP', "{$writer->getReturn()} entries dumped"));
        }
    }
}
