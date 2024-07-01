<?php

namespace Eternium\Command;

use Eternium\Config;
use Eternium\Event\Leaderboard;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('fetch')]
class FetchCommand extends Command
{
    private string $pattern = '';

    private bool $update = false;

    /**
     * @var callable(string): bool
     */
    private $accept;

    private bool $hideProgress = false;

    public function __construct(private readonly Config $config)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Fetches leaderboards from game server');
        $this->addArgument('pattern', InputArgument::OPTIONAL, 'The pattern of leaderboard paths', $this->pattern);
        $this->addOption('from', '', InputOption::VALUE_NONE, 'Start fetching when pattern matched');
        $this->addOption('update', 'u', InputOption::VALUE_NONE, 'Update existing data');
        $this->addOption('no-progress', '', InputOption::VALUE_NONE, 'Do not output download progress');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->accept = [self::class, 'acceptAll'];
        $pattern = self::normalizePattern($input->getArgument('pattern'));
        if ($input->getOption('from')) {
            $this->accept = function (string $path) use ($pattern): bool {
                if ($accept = self::acceptPattern($path, $pattern)) {
                    $this->accept = [self::class, 'acceptAll'];
                }

                return $accept;
            };
        } else {
            $this->accept = static fn (string $path): bool => self::acceptPattern($path, $pattern);
        }

        $this->update = $input->getOption('update');
        $this->hideProgress = $input->getOption('no-progress');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fetcher = $this->createFetcher($output);
        foreach ($this->config->events as $event) {
            $event->walk($fetcher);
        }
        $fetcher->send(null);

        return self::SUCCESS;
    }

    protected static function normalizePattern(string $pattern): string
    {
        return \strtolower(\strtr($pattern, '\\', '/'));
    }

    protected static function acceptAll(): bool
    {
        return true;
    }

    protected static function acceptPattern(string $path, string $pattern): bool
    {
        return \str_starts_with($path, $pattern);
    }

    private function createFetcher(OutputInterface $output): \Generator
    {
        $formatter = $this->getHelper('formatter');
        $progressBar = new ProgressBar($this->hideProgress ? new NullOutput() : $output);
        $progressBar->setFormat($formatter->formatSection('DUMP', '%message% %current% %elapsed%'));

        while (null !== ($event = yield)) {
            if (!$event instanceof Leaderboard) {
                continue;
            }

            $path = \join('/', $event->getPath());
            $file = new \SplFileInfo("{$this->config->dataPath}/{$path}.csv");

            $href = 'file:///'.\ltrim(\strtr($file, DIRECTORY_SEPARATOR, '/'), '/');

            if (!($this->accept)($path)) {
                $output->writeln(
                    $formatter->formatSection('SKIP', "{$path} not matched against pattern", 'comment'),
                    Output::VERBOSITY_VERY_VERBOSE
                );

                continue;
            }

            if (!$this->update && $file->isFile()) {
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
                foreach ($progressBar->iterate($event->fetch($this->config->eternium)) as $entry) {
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
