<?php

namespace Eternium\Command;

use Eternium\Event\Event;
use Eternium\Event\Leaderboard;
use Eternium\Utils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->client = Utils::createHttpClient((string) getenv('ETERNIUM_API_KEY'));

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Fetches LB entries from game server');
        $this->addArgument('prefix', InputArgument::OPTIONAL, 'The LB prefix', '');
        $this->addOption('data-dir', 'd', InputOption::VALUE_REQUIRED, 'Dump data to this directory', 'data');
        $this->addOption('update', 'u', InputOption::VALUE_NONE, 'Update existing data');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $dir = $input->getOption('data-dir');
        if (!is_dir($dir)) {
            throw new InvalidOptionException('The option "--data-dir" requires an existing directory.');
        }
        $input->setOption('data-dir', realpath($dir));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $prefix = $input->getArgument('prefix');
        $path = $input->getOption('data-dir');
        $update = $input->getOption('update');

        $fetcher = function (Leaderboard $leaderboard, string ...$names) use ($prefix, $path, $update, $output): array {
            $formatter = $this->getHelper('formatter');

            $name = join('.', $names);
            if (!str_starts_with($name, $prefix)) {
                $output->writeln(
                    $formatter->formatSection('SKIP', "{$name} not matched prefix", 'comment'),
                    Output::VERBOSITY_VERBOSE
                );

                return [];
            }

            $file = $path.DIRECTORY_SEPARATOR."{$name}.csv";
            if (!$update && is_file($file)) {
                $output->writeln(
                    $formatter->formatSection('SKIP', "{$name} entries already dumped", 'comment'),
                    Output::VERBOSITY_VERBOSE
                );

                return [];
            }

            $output->writeln($formatter->formatSection('DUMP', "fetching {$name} entries..."));

            $reader = Utils::createLeaderboardReader($this->client, $leaderboard->getId());
            $writer = Utils::createCsvWriter($file);

            try {
                $progressBar = new ProgressBar($output);
                foreach ($progressBar->iterate($reader) as $entry) {
                    $writer->send($entry);
                }
            } finally {
                $progressBar->clear();
            }
            $writer->send(null);

            $output->writeln($formatter->formatSection('DUMP', "{$writer->getReturn()} entries dumped"));

            return [];
        };

        foreach ($this->events as $event) {
            $event->apply($fetcher);
        }

        return self::SUCCESS;
    }
}
