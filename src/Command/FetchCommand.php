<?php

namespace Eternium\Command;

use Eternium\Event\EventInterface;
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
     * @var array<int, EventInterface>
     */
    private array $events;

    private string $pattern = '*';

    private bool $update = false;

    private bool $hideProgress = false;

    public function __construct(EventInterface ...$events)
    {
        $this->events = $events;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Fetches leaderboards from game server');
        $this->addArgument('pattern', InputArgument::OPTIONAL, 'The pattern of leaderboard paths', $this->pattern);
        $this->addOption('update', 'u', InputOption::VALUE_NONE, 'Update existing data');
        $this->addOption('no-progress', '', InputOption::VALUE_NONE, 'Do not output download progress');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->pattern = strtr($input->getArgument('pattern'), '\\', '/');
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
        $progressBar->setFormat($formatter->formatSection('DUMP', '%message% %current% %elapsed%'));

        while (null !== ($event = yield)) {
            if (!($event instanceof Leaderboard)) {
                continue;
            }

            $path = $event->getPath('/');
            $file = ETERNIUM_DATA_PATH.strtr($path, '/', DIRECTORY_SEPARATOR).'.csv';
            $href = 'file:///'.ltrim(strtr($file, DIRECTORY_SEPARATOR, '/'), '/');

            if (!fnmatch($this->pattern, $path, FNM_NOESCAPE)) {
                $output->writeln(
                    $formatter->formatSection('SKIP', "{$path} not matched against pattern", 'comment'),
                    Output::VERBOSITY_VERY_VERBOSE
                );

                continue;
            }

            if (!$this->update && is_file($file)) {
                $output->writeln(
                    $formatter->formatSection('SKIP', "<href={$href}>{$path}.csv</> already exists", 'comment'),
                    Output::VERBOSITY_VERBOSE
                );

                continue;
            }

            $writer = $event->write($file);

            try {
                $progressBar->setMessage("fetching {$path}");
                $progressBar->display();
                foreach ($progressBar->iterate($event->fetch()) as $entry) {
                    $writer->send($entry);
                }
            } finally {
                $progressBar->clear();
            }
            $writer->send(null);

            $output->writeln($formatter->formatSection('DUMP', "<href={$href}>{$path}.csv</> dumped ({$writer->getReturn()} entries)"));
        }
    }
}
