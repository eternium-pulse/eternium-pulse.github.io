<?php

namespace Eternium\Command;

use Eternium\Event\EventInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use function Symfony\Component\String\u;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchCommand extends Command
{
    protected static $defaultName = 'fetch';

    private HttpClientInterface $client;

    /**
     * @var array<int, EventInterface>
     */
    private array $events;

    public function __construct(EventInterface ...$events)
    {
        $this->events = $events;
        $this->client = HttpClient::createForBaseUri('https://mfp.makingfun.com/api/', [
            'http_version' => '1.1',
            'max_redirects' => 0,
            'headers' => [
                'Accept' => 'application/json',
                'X-API-Key' => (string) getenv('ETERNIUM_API_KEY'),
            ],
        ]);

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
        $filter = $this->filter($input->getArgument('prefix'));
        $dir = $input->getOption('data-dir');
        $update = $input->getOption('update');

        $fetcher = function (string $prefix, string $id) use ($output, $filter, $dir, $update): void {
            $formatter = $this->getHelper('formatter');

            if (!$filter($prefix)) {
                $output->writeln(
                    $formatter->formatSection('SKIP', "{$prefix} not matched prefix", 'comment'),
                    Output::VERBOSITY_VERBOSE
                );

                return;
            }

            $file = "{$dir}/{$prefix}.csv";
            if (!$update && is_file($file)) {
                $output->writeln(
                    $formatter->formatSection('SKIP', "{$prefix} entries already dumped", 'comment'),
                    Output::VERBOSITY_VERBOSE
                );

                return;
            }

            $output->writeln(
                $formatter->formatSection('DUMP', "fetching {$prefix} entries...")
            );

            $progressBar = new ProgressBar($output);
            $count = $this->dump($file, $progressBar->iterate($this->fetch($id)));
            $progressBar->clear();

            $output->writeln(
                $formatter->formatSection('DUMP', "{$count} entries dumped")
            );
        };

        foreach ($this->events as $event) {
            $event->fetch($fetcher, '');
        }

        return self::SUCCESS;
    }

    private function filter(string $prefix): \Closure
    {
        if ('' === $prefix) {
            return static fn (): bool => true;
        }

        return static fn (string $name): bool => u($name)->startsWith($prefix);
    }

    private function dump(string $file, iterable $data): int
    {
        $count = 0;

        $mem = fopen('php://memory', 'r+');
        foreach ($data as $entry) {
            fputcsv($mem, $entry);
            ++$count;
        }
        rewind($mem);

        if (false === @file_put_contents($file, $mem, LOCK_EX)) {
            $error = error_get_last();
            error_clear_last();

            throw new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
        }

        return $count;
    }

    /**
     * @return iterable<int, array>
     */
    private function fetch(string $id): iterable
    {
        $query = [
            'page' => 0,
            'pageSize' => 1000,
            'payload' => 'name,champion_level,hero.selectedPlayerNameID,trialStats.heroDeaths',
        ];

        do {
            $count = 0;
            ++$query['page'];
            $response = $this->client->request('GET', "leaderboards/{$id}/rankings", ['query' => $query]);
            foreach ($response->toArray() as $data) {
                yield [
                    'name' => $data['payload']['name'],
                    'title' => u($data['payload']['hero']['selectedPlayerNameID'] ?? '')
                        ->replace('_', ' ')
                        ->title(true)
                        ->toString(),
                    'level' => $data['payload']['champion_level'],
                    'score' => $data['score'],
                    'deaths' => $data['payload']['trialStats']['heroDeaths'],
                ];
                ++$count;
            }
        } while ($count === $query['pageSize']);
    }
}
