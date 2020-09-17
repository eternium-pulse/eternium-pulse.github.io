<?php

namespace Eternium\Command;

use Eternium\Event\Event;
use Eternium\Event\Leaderboard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class FetchCommand extends Command
{
    protected static $defaultName = 'fetch';

    /**
     * @var array<int, Event>
     */
    private array $events;

    private string $prefix = '';

    private bool $update = false;

    private bool $hideProgress = false;

    public function __construct(Event ...$events)
    {
        $this->events = $events;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Fetches leaderboards from game server');
        $this->addArgument('prefix', InputArgument::OPTIONAL, 'The leaderboards prefix', $this->prefix);
        $this->addOption('update', 'u', InputOption::VALUE_NONE, 'Update existing data');
        $this->addOption('no-progress', '', InputOption::VALUE_NONE, 'Do not output download progress');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->prefix = $input->getArgument('prefix');
        $this->update = $input->getOption('update');
        $this->hideProgress = $input->getOption('no-progress');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fetcher = $this->createFetcher($output);
        foreach ($this->events as $event) {
            $event->walk($fetcher);
        }
        $fetcher->send(null);

        return self::SUCCESS;
    }

    private function createFetcher(OutputInterface $output): \Generator
    {
        $formatter = $this->getHelper('formatter');
        $progressBar = new ProgressBar($this->hideProgress ? new NullOutput() : $output);
        $progressBar->setFormat($formatter->formatSection('DUMP', '%current% [%bar%] %elapsed%'));

        while (null !== ($event = yield)) {
            if (!($event instanceof Leaderboard)) {
                continue;
            }

            $name = $event->getPath('.');
            if (!str_starts_with($name, $this->prefix)) {
                $output->writeln(
                    $formatter->formatSection('SKIP', "{$name} not matched prefix", 'comment'),
                    Output::VERBOSITY_VERBOSE
                );

                continue;
            }

            $file = ETERNIUM_DATA_PATH."{$name}.csv";
            if (!$this->update && is_file($file)) {
                $output->writeln(
                    $formatter->formatSection('SKIP', "{$name} entries already dumped", 'comment'),
                    Output::VERBOSITY_VERBOSE
                );

                continue;
            }

            $output->writeln($formatter->formatSection('DUMP', "fetching {$name} entries..."));

            $writer = $event->write($file);

            try {
                $progressBar->display();
                foreach ($progressBar->iterate($event->fetch()) as $entry) {
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
